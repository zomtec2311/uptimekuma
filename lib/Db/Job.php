<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\Db;

use OCP\AppFramework\Db\Entity;

class Job extends Entity {
    protected $name; protected $instanceId; protected $statusSlug; protected $title; protected $content; protected $style; protected $enabled; protected $createdAt; protected $updatedAt;
    public function __construct() {
        $this->addType('name','string'); $this->addType('instanceId','integer'); $this->addType('statusSlug','string'); $this->addType('title','string'); $this->addType('content','string'); $this->addType('style','string'); $this->addType('enabled','integer'); $this->addType('createdAt','integer'); $this->addType('updatedAt','integer');
    }
    public function getName(): string { return (string)$this->name; } public function setName(string $v): void { $this->name=$v; $this->markFieldUpdated('name'); }
    public function getInstanceId(): int { return (int)$this->instanceId; } public function setInstanceId(int $v): void { $this->instanceId=$v; $this->markFieldUpdated('instanceId'); }
    public function getStatusSlug(): string { return (string)$this->statusSlug; } public function setStatusSlug(string $v): void { $this->statusSlug=$v; $this->markFieldUpdated('statusSlug'); }
    public function getTitle(): string { return (string)$this->title; } public function setTitle(string $v): void { $this->title=$v; $this->markFieldUpdated('title'); }
    public function getContent(): string { return (string)$this->content; } public function setContent(string $v): void { $this->content=$v; $this->markFieldUpdated('content'); }
    public function getStyle(): string { return (string)$this->style; } public function setStyle(string $v): void { $this->style=$v; $this->markFieldUpdated('style'); }
    public function getEnabled(): bool { return (bool)$this->enabled; } public function setEnabled(bool $v): void { $this->enabled=$v?1:0; $this->markFieldUpdated('enabled'); }
    public function getCreatedAt(): int { return (int)$this->createdAt; } public function setCreatedAt(int $v): void { $this->createdAt=$v; $this->markFieldUpdated('createdAt'); }
    public function getUpdatedAt(): int { return (int)$this->updatedAt; } public function setUpdatedAt(int $v): void { $this->updatedAt=$v; $this->markFieldUpdated('updatedAt'); }
}
