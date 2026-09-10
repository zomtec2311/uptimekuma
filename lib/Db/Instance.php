<?php
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

    public function getName(): string { return (string)$this->name; }
    public function setName(string $value): void { $this->name = $value; $this->markFieldUpdated('name'); }
    public function getUrl(): string { return (string)$this->url; }
    public function setUrl(string $value): void { $this->url = rtrim($value, '/'); $this->markFieldUpdated('url'); }
    public function getUsername(): string { return (string)$this->username; }
    public function setUsername(string $value): void { $this->username = $value; $this->markFieldUpdated('username'); }
    public function getPasswordEncrypted(): string { return (string)$this->passwordEncrypted; }
    public function setPasswordEncrypted(string $value): void { $this->passwordEncrypted = $value; $this->markFieldUpdated('passwordEncrypted'); }
    public function getCreatedAt(): int { return (int)$this->createdAt; }
    public function setCreatedAt(int $value): void { $this->createdAt = $value; $this->markFieldUpdated('createdAt'); }
    public function getUpdatedAt(): int { return (int)$this->updatedAt; }
    public function setUpdatedAt(int $value): void { $this->updatedAt = $value; $this->markFieldUpdated('updatedAt'); }
}
