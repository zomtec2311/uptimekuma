<?php
/**
 *
 * UptimeKuma APP (Nextcloud)
 *
 * @author Wolfgang Tödt <wtoedt@gmail.com>
 *
 * @copyright Copyright (c) 2026 Wolfgang Tödt
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace OCA\UptimeKuma\Service;

use OCA\UptimeKuma\Db\Instance;
use OCA\UptimeKuma\Db\InstanceMapper;
use OCP\Security\ICrypto;
use OCP\IL10N;
use RuntimeException;

class InstanceService {
     private $l;

    public function __construct(
        private InstanceMapper $mapper,
        private ICrypto $crypto,
        private KumaClient $kumaClient,
        IL10N $l,
    ) {
        $this->l = $l;

    }

    public function create(string $name, string $url, string $username, string $password): Instance {
        $name = trim($name);
        $url = rtrim(trim($url), '/');
        $username = trim($username);

        if ($name === '' || $url === '' || $username === '' || $password === '') {
            throw new RuntimeException($this->l->t('All fields are required.'));
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException($this->l->t('Invalid URL.'));
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
