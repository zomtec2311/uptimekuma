<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\Service;

use OCA\UptimeKuma\Db\Instance;
use OCA\UptimeKuma\Db\InstanceMapper;
use OCP\Security\ICrypto;
use RuntimeException;

class InstanceService {
    public function __construct(
        private InstanceMapper $mapper,
        private ICrypto $crypto,
        private KumaClient $kumaClient,
    ) {}

    public function create(string $name, string $url, string $username, string $password): Instance {
        $name = trim($name);
        $url = rtrim(trim($url), '/');
        $username = trim($username);

        if ($name === '' || $url === '' || $username === '' || $password === '') {
            throw new RuntimeException('Alle Felder sind erforderlich.');
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('Ungültige URL.');
        }

        $now = time();
        $entity = new Instance();
        $entity->setName($name);
        $entity->setUrl($url);
        $entity->setUsername($username);
        $entity->setPasswordEncrypted($this->crypto->encrypt($password));
        $entity->setCreatedAt($now);
        $entity->setUpdatedAt($now);

        return $this->mapper->insert($entity);
    }

    public function update(int $id, string $name, string $url, string $username, ?string $password): Instance {
        $entity = $this->mapper->find($id);
        $entity->setName(trim($name));
        $entity->setUrl(rtrim(trim($url), '/'));
        $entity->setUsername(trim($username));

        if ($password !== null && $password !== '') {
            $entity->setPasswordEncrypted($this->crypto->encrypt($password));
        }

        $entity->setUpdatedAt(time());
        return $this->mapper->update($entity);
    }

    public function test(Instance $instance): void {
        $this->kumaClient->test(
            $instance->getUrl(),
            $instance->getUsername(),
            $instance->getPasswordEncrypted()
        );
    }
}
