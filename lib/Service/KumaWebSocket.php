<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\Service;

use OCP\IL10N;
use RuntimeException;

class KumaWebSocket {
    /** @var resource|null */
    private $stream = null;

    public function __construct(
        private IL10N $l,
        private int $timeout = 15
    ) {
    }

    public function connect(string $url): void {
        $parts = parse_url($url);
        if (!$parts || empty($parts['host'])) {
            throw new RuntimeException($this->l->t('Invalid WebSocket URL.'));
        }

        $scheme = strtolower((string)($parts['scheme'] ?? ''));
        if (!in_array($scheme, ['ws', 'wss'], true)) {
            throw new RuntimeException($this->l->t('Only ws:// and wss:// are supported.'));
        }

        $host = (string)$parts['host'];
        $port = (int)($parts['port'] ?? ($scheme === 'wss' ? 443 : 80));
        $path = (string)($parts['path'] ?? '/');
        if ($path === '') {
            $path = '/';
        }
        if (!empty($parts['query'])) {
            $path .= '?' . $parts['query'];
        }

        $transport = $scheme === 'wss' ? 'ssl' : 'tcp';
        $contextOptions = [];
        if ($scheme === 'wss') {
            $contextOptions['ssl'] = [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'peer_name' => $host,
                'SNI_enabled' => true,
                'SNI_server_name' => $host,
            ];
        }

        $context = stream_context_create($contextOptions);
        $errno = 0;
        $error = '';
        $stream = @stream_socket_client(
            sprintf('%s://%s:%d', $transport, $host, $port),
            $errno,
            $error,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!is_resource($stream)) {
            throw new RuntimeException($this->l->t('WebSocket connection failed:').' ' . ($error !== '' ? $error : $this->l->t('Error').' ' . $errno));
        }

        $this->stream = $stream;
        stream_set_timeout($stream, $this->timeout);

        try {
            $key = base64_encode(random_bytes(16));
            $hostHeader = $host;
            if (($scheme === 'wss' && $port !== 443) || ($scheme === 'ws' && $port !== 80)) {
                $hostHeader .= ':' . $port;
            }

            $request = "GET {$path} HTTP/1.1\r\n" .
                "Host: {$hostHeader}\r\n" .
                "Upgrade: websocket\r\n" .
                "Connection: Upgrade\r\n" .
                "Sec-WebSocket-Key: {$key}\r\n" .
                "Sec-WebSocket-Version: 13\r\n\r\n";

            $this->writeAll($request);
            $response = $this->readHttpHeaders();

            if (!preg_match('/^HTTP\/1\.1\s+101\s+/i', $response)) {
                throw new RuntimeException($this->l->t('WebSocket handshake rejected.'));
            }

            $headers = $this->parseHeaders($response);
            $accept = $headers['sec-websocket-accept'] ?? '';
            $expected = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
            if (!hash_equals($expected, trim($accept))) {
                throw new RuntimeException($this->l->t('Invalid WebSocket handshake response.'));
            }
        } catch (\Throwable $e) {
            $this->close();
            throw $e;
        }
    }

    public function send(string $payload): void {
        $this->sendFrame(0x1, $payload);
    }

    public function receive(): string {
        while (true) {
            [$opcode, $payload] = $this->readFrame();

            if ($opcode === 0x1 || $opcode === 0x2) {
                return $payload;
            }
            if ($opcode === 0x9) {
                $this->sendFrame(0xA, $payload);
                continue;
            }
            if ($opcode === 0xA) {
                continue;
            }
            if ($opcode === 0x8) {
                $this->close();
                throw new RuntimeException($this->l->t('Kuma has closed the WebSocket connection.'));
            }
        }
    }

    public function close(): void {
        if (is_resource($this->stream)) {
            try {
                $this->sendFrame(0x8, '');
            } catch (\Throwable) {
                // Connection may already be closed.
            }
            fclose($this->stream);
        }
        $this->stream = null;
    }

    private function sendFrame(int $opcode, string $payload): void {
        if (!is_resource($this->stream)) {
            throw new RuntimeException($this->l->t('WebSocket is not connected.'));
        }

        $length = strlen($payload);
        $first = 0x80 | ($opcode & 0x0F);
        $mask = random_bytes(4);
        $maskedPayload = '';
        for ($i = 0; $i < $length; $i++) {
            $maskedPayload .= $payload[$i] ^ $mask[$i % 4];
        }

        if ($length < 126) {
            $header = chr($first) . chr(0x80 | $length);
        } elseif ($length <= 0xFFFF) {
            $header = chr($first) . chr(0x80 | 126) . pack('n', $length);
        } else {
            $header = chr($first) . chr(0x80 | 127) . pack('J', $length);
        }

        $this->writeAll($header . $mask . $maskedPayload);
    }

    /** @return array{0:int,1:string} */
    private function readFrame(): array {
        $header = $this->readBytes(2);
        $first = ord($header[0]);
        $second = ord($header[1]);
        $opcode = $first & 0x0F;
        $length = $second & 0x7F;

        if (($first & 0x80) === 0) {
            throw new RuntimeException($this->l->t('Fragmented WebSocket messages are not supported.'));
        }

        if ($length === 126) {
            $length = unpack('n', $this->readBytes(2))[1];
        } elseif ($length === 127) {
            $parts = unpack('N2', $this->readBytes(8));
            if (($parts[1] ?? 0) !== 0) {
                throw new RuntimeException($this->l->t('Too large WebSocket message.'));
            }
            $length = $parts[2] ?? 0;
        }

        $masked = ($second & 0x80) !== 0;
        $mask = $masked ? $this->readBytes(4) : '';
        $payload = $length > 0 ? $this->readBytes($length) : '';

        if ($masked) {
            for ($i = 0; $i < $length; $i++) {
                $payload[$i] = $payload[$i] ^ $mask[$i % 4];
            }
        }

        return [$opcode, $payload];
    }

    private function readHttpHeaders(): string {
        $response = '';
        while (!str_contains($response, "\r\n\r\n")) {
            $chunk = fread($this->stream, 1024);
            if ($chunk === false || $chunk === '') {
                throw new RuntimeException($this->l->t('No valid WebSocket handshake response received.'));
            }
            $response .= $chunk;
            if (strlen($response) > 65536) {
                throw new RuntimeException($this->l->t('Too large WebSocket handshake response'));
            }
        }
        return $response;
    }

    /** @return array<string,string> */
    private function parseHeaders(string $response): array {
        $lines = preg_split("/\r\n/", $response) ?: [];
        $headers = [];
        foreach ($lines as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }
            [$name, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($name))] = trim($value);
        }
        return $headers;
    }

    private function readBytes(int $length): string {
        $result = '';
        while (strlen($result) < $length) {
            $chunk = fread($this->stream, $length - strlen($result));
            if ($chunk === false || $chunk === '') {
                $meta = stream_get_meta_data($this->stream);
                if (!empty($meta['timed_out'])) {
                    throw new RuntimeException($this->l->t('WebSocket Timeout'));
                }
                throw new RuntimeException($this->l->t('WebSocket connection unexpectedly terminated.'));
            }
            $result .= $chunk;
        }
        return $result;
    }

    private function writeAll(string $data): void {
        $offset = 0;
        $length = strlen($data);
        while ($offset < $length) {
            $written = fwrite($this->stream, substr($data, $offset));
            if ($written === false || $written === 0) {
                throw new RuntimeException($this->l->t('Writing on WebSocket failed.'));
            }
            $offset += $written;
        }
    }
}
