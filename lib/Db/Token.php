<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\Db;

use OCP\AppFramework\Db\Entity;

class Token extends Entity {
    protected $jobId; protected $tokenHash; protected $description; protected $enabled; protected $lastUsedAt; protected $createdAt; protected $expiresAt;
    public function __construct() { foreach(['jobId'=>'integer','tokenHash'=>'string','description'=>'string','enabled'=>'integer','lastUsedAt'=>'integer','createdAt'=>'integer','expiresAt'=>'integer'] as $k=>$t) $this->addType($k,$t); }
    public function getJobId(): int { return (int)$this->jobId; } public function setJobId(int $v): void { $this->jobId=$v; $this->markFieldUpdated('jobId'); }
    public function getTokenHash(): string { return (string)$this->tokenHash; } public function setTokenHash(string $v): void { $this->tokenHash=$v; $this->markFieldUpdated('tokenHash'); }
    public function getDescription(): string { return (string)$this->description; } public function setDescription(string $v): void { $this->description=$v; $this->markFieldUpdated('description'); }
    public function getEnabled(): bool { return (bool)$this->enabled; } public function setEnabled(bool $v): void { $this->enabled=$v?1:0; $this->markFieldUpdated('enabled'); }
    public function getLastUsedAt(): ?int { return $this->lastUsedAt===null?null:(int)$this->lastUsedAt; } public function setLastUsedAt(?int $v): void { $this->lastUsedAt=$v; $this->markFieldUpdated('lastUsedAt'); }
    public function getCreatedAt(): int { return (int)$this->createdAt; } public function setCreatedAt(int $v): void { $this->createdAt=$v; $this->markFieldUpdated('createdAt'); }
    public function getExpiresAt(): ?int { return $this->expiresAt===null?null:(int)$this->expiresAt; } public function setExpiresAt(?int $v): void { $this->expiresAt=$v; $this->markFieldUpdated('expiresAt'); }
}
