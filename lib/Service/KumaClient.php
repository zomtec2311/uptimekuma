<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\Service;

use OCP\Security\ICrypto;
use RuntimeException;

class KumaClient {
    private KumaWebSocket $client;
    private int $eventId = 0;

    public function __construct(private ICrypto $crypto) {
        $this->client = new KumaWebSocket(15);
    }

    public function test(string $url, string $username, string $encryptedPassword): void {
        $this->connect($url);
        try {
            $this->login($username, $this->crypto->decrypt($encryptedPassword));
        } finally {
            $this->client->close();
        }
    }

    public function start(string $url, string $username, string $encryptedPassword, string $slug, string $title, string $content, string $style): int {
        $this->connect($url);
        try {
            $this->login($username, $this->crypto->decrypt($encryptedPassword));
            $result = $this->emit('postIncident', [$slug, ['title' => $title, 'content' => $content, 'style' => $style]]);
            $id = $this->extractId($result);
            if ($id === null) {
                throw new RuntimeException('Kuma hat keine Incident-ID zurückgegeben.');
            }
            return $id;
        } finally {
            $this->client->close();
        }
    }

    public function resolve(string $url, string $username, string $encryptedPassword, string $slug, int $incidentId): void {
        $this->connect($url);
        try {
            $this->login($username, $this->crypto->decrypt($encryptedPassword));
            $this->emit('resolveIncident', [$slug, $incidentId]);
        } finally {
            $this->client->close();
        }
    }

    public function update(string $url, string $username, string $encryptedPassword, string $slug, int $incidentId, string $title, string $content, string $style): void {
        $this->connect($url);
        try {
            $this->login($username, $this->crypto->decrypt($encryptedPassword));
            $this->emit('postIncident', [$slug, ['id' => $incidentId, 'title' => $title, 'content' => $content, 'style' => $style]]);
        } finally {
            $this->client->close();
        }
    }

    /**
     * Returns the current incident history from Uptime Kuma 2.5.x.
     * This uses Kuma's authenticated Socket.IO API so the result is not
     * affected by the public status-page cache.
     */
    public function getIncidentHistory(string $url, string $username, string $encryptedPassword, string $slug): array {
        $this->connect($url);
        try {
            $this->login($username, $this->crypto->decrypt($encryptedPassword));
            $result = $this->emit('getIncidentHistory', [$slug, null]);
            if (($result['ok'] ?? false) !== true) {
                throw new RuntimeException((string)($result['msg'] ?? 'Kuma konnte die Incident-Historie nicht liefern.'));
            }
            return is_array($result['incidents'] ?? null) ? $result['incidents'] : [];
        } finally {
            $this->client->close();
        }
    }

    private function login(string $username, string $password): void {
        $result = $this->emit('login', ['username' => $username, 'password' => $password, 'token' => null]);
        if (isset($result['error']) || (isset($result['ok']) && $result['ok'] === false)) {
            throw new RuntimeException('Kuma-Login fehlgeschlagen.');
        }
    }

    private function connect(string $baseUrl): void {
        $parts = parse_url(rtrim($baseUrl, '/'));
        if (!$parts || empty($parts['host'])) {
            throw new RuntimeException('Ungültige Kuma-URL.');
        }

        $scheme = ($parts['scheme'] ?? 'https') === 'https' ? 'wss' : 'ws';
        $host = $parts['host'];
        $port = $parts['port'] ?? ($scheme === 'wss' ? 443 : 80);
        $wsUrl = sprintf('%s://%s:%d/socket.io/?EIO=4&transport=websocket', $scheme, $host, $port);

        $this->client->connect($wsUrl);
        $hello = $this->client->receive();
        if (substr($hello, 0, 1) !== '0') {
            throw new RuntimeException('Unerwartete Engine.IO-Antwort.');
        }

        $this->client->send('40');

        // Uptime Kuma 2.x registers its login handler asynchronously while
        // sending the initial "info" event. Sending "login" immediately
        // after the Socket.IO CONNECT packet can therefore race the server's
        // handler registration. Wait for "info" before authenticating.
        $connected = false;
        $infoReceived = false;
        $deadline = microtime(true) + 15.0;

        while (microtime(true) < $deadline) {
            $response = $this->client->receive();

            if ($response === '2') {
                $this->client->send('3');
                continue;
            }

            if (substr($response, 0, 2) === '40') {
                $connected = true;
                continue;
            }

            if (str_starts_with($response, '42')) {
                $json = substr($response, 2);
                $event = json_decode($json, true);
                if (is_array($event) && ($event[0] ?? null) === 'info') {
                    $infoReceived = true;
                    break;
                }
            }
        }

        if (!$connected) {
            throw new RuntimeException('Socket.IO-Verbindung konnte nicht aufgebaut werden.');
        }
        if (!$infoReceived) {
            throw new RuntimeException('Kuma hat keine initiale Info-Antwort gesendet.');
        }
    }

    private function emit(string $event, array $data): array {
        $this->eventId++;
        $arguments = array_is_list($data) ? array_merge([$event], $data) : [$event, $data];
        $payload = '42' . $this->eventId . json_encode(
            $arguments,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
        $this->client->send($payload);

        while (true) {
            $response = $this->client->receive();
            if ($response === '2') {
                $this->client->send('3');
                continue;
            }
            if (str_starts_with($response, '43' . $this->eventId)) {
                $payload = substr($response, 2 + strlen((string)$this->eventId));
                $decoded = json_decode($payload, true);
                if (!is_array($decoded)) {
                    throw new RuntimeException('Ungültige Kuma-Callback-Antwort: ' . substr($response, 0, 500));
                }
                return $decoded[0] ?? [];
            }
        }
    }

    private function extractId(mixed $value): ?int {
        if (is_int($value) || is_float($value) || (is_string($value) && ctype_digit($value))) {
            return (int)$value;
        }
        if (is_array($value)) {
            foreach (['id', 'incidentID', 'incidentId'] as $key) {
                if (isset($value[$key]) && is_numeric($value[$key])) {
                    return (int)$value[$key];
                }
            }
            foreach ($value as $item) {
                $id = $this->extractId($item);
                if ($id !== null) {
                    return $id;
                }
            }
        }
        return null;
    }
}
