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

namespace OCA\UptimeKuma\Db;

use OCP\AppFramework\Db\Entity;

class Instance extends Entity {
    protected $name;
    protected $url;
    protected $username;
    protected $passwordEncrypted;
    protected $createdAt;
    protected $updatedAt;

    public function __construct() {
        $this->addType('name', 'string');
        $this->addType('url', 'string');
        $this->addType('username', 'string');
        $this->addType('passwordEncrypted', 'string');
        $this->addType('createdAt', 'integer');
        $this->addType('updatedAt', 'integer');
    }

    public function getName(): string {
        return (string)$this->name;
    }

    public function setName(string $value): void {
        $this->name = $value;
        $this->markFieldUpdated('name');
    }

    public function getUrl(): string {
        return (string)$this->url;
    }

    public function setUrl(string $value): void {
        $this->url = rtrim($value, '/');
        $this->markFieldUpdated('url');
    }

    public function getUsername(): string {
        return (string)$this->username;
    }

    public function setUsername(string $value): void {
        $this->username = $value;
        $this->markFieldUpdated('username');
    }

    public function getPasswordEncrypted(): string {
        return (string)$this->passwordEncrypted;
    }

    public function setPasswordEncrypted(string $value): void {
        $this->passwordEncrypted = $value;
        $this->markFieldUpdated('passwordEncrypted');
    }

    public function getCreatedAt(): int {
        return (int)$this->createdAt;
    }

    public function setCreatedAt(int $value): void {
        $this->createdAt = $value;
        $this->markFieldUpdated('createdAt');
    }

    public function getUpdatedAt(): int {
        return (int)$this->updatedAt;
    }

    public function setUpdatedAt(int $value): void {
        $this->updatedAt = $value;
        $this->markFieldUpdated('updatedAt');
    }
}
