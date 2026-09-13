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

class JobHistory extends Entity {
    protected $jobId;
    protected $action;
    protected $source;
    protected $userId;
    protected $ipAddress;
    protected $userAgent;
    protected $tokenId;
    protected $tokenDescription;
    protected $success;
    protected $errorMessage;
    protected $createdAt;

    public function __construct() {
        foreach ([
            'jobId' => 'integer',
            'action' => 'string',
            'source' => 'string',
            'userId' => 'string',
            'ipAddress' => 'string',
            'userAgent' => 'string',
            'tokenId' => 'integer',
            'tokenDescription' => 'string',
            'success' => 'integer',
            'errorMessage' => 'string',
            'createdAt' => 'integer',
        ] as $key => $type) {
            $this->addType($key, $type);
        }
    }

    public function getJobId(): ?int { return $this->jobId === null ? null : (int)$this->jobId; }
    public function setJobId(?int $value): void { $this->jobId = $value; $this->markFieldUpdated('jobId'); }
    public function getAction(): string { return (string)$this->action; }
    public function setAction(string $value): void { $this->action = $value; $this->markFieldUpdated('action'); }
    public function getSource(): string { return (string)$this->source; }
    public function setSource(string $value): void { $this->source = $value; $this->markFieldUpdated('source'); }
    public function getUserId(): ?string { return $this->userId === null ? null : (string)$this->userId; }
    public function setUserId(?string $value): void { $this->userId = $value; $this->markFieldUpdated('userId'); }
    public function getIpAddress(): string { return (string)$this->ipAddress; }
    public function setIpAddress(string $value): void { $this->ipAddress = $value; $this->markFieldUpdated('ipAddress'); }
    public function getUserAgent(): string { return (string)$this->userAgent; }
    public function setUserAgent(string $value): void { $this->userAgent = $value; $this->markFieldUpdated('userAgent'); }
    public function getTokenId(): ?int { return $this->tokenId === null ? null : (int)$this->tokenId; }
    public function setTokenId(?int $value): void { $this->tokenId = $value; $this->markFieldUpdated('tokenId'); }
    public function getTokenDescription(): ?string { return $this->tokenDescription === null ? null : (string)$this->tokenDescription; }
    public function setTokenDescription(?string $value): void { $this->tokenDescription = $value; $this->markFieldUpdated('tokenDescription'); }
    public function getSuccess(): bool { return (bool)$this->success; }
    public function setSuccess(bool $value): void { $this->success = $value ? 1 : 0; $this->markFieldUpdated('success'); }
    public function getErrorMessage(): string { return (string)$this->errorMessage; }
    public function setErrorMessage(string $value): void { $this->errorMessage = $value; $this->markFieldUpdated('errorMessage'); }
    public function getCreatedAt(): int { return (int)$this->createdAt; }
    public function setCreatedAt(int $value): void { $this->createdAt = $value; $this->markFieldUpdated('createdAt'); }
}
