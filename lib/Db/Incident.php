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

class Incident extends Entity {

    protected $jobId;
    protected $kumaIncidentId;
    protected $title;
    protected $content;
    protected $style;
    protected $state;
    protected $errorMessage;
    protected $createdAt;
    protected $resolvedAt;

    public function __construct() {
        foreach(['jobId'=>'integer','kumaIncidentId'=>'integer','title'=>'string','content'=>'string','style'=>'string','state'=>'string','errorMessage'=>'string','createdAt'=>'integer','resolvedAt'=>'integer'] as $k=>$t) {
            $this->addType($k,$t);
        }
    }

    public function getJobId(): int {
        return (int)$this->jobId;
    }

    public function setJobId(int $v): void {
        $this->jobId = $v;
        $this->markFieldUpdated('jobId');
    }

    public function getKumaIncidentId(): int {
        return (int)$this->kumaIncidentId;
    }

    public function setKumaIncidentId(int $v): void {
        $this->kumaIncidentId = $v;
        $this->markFieldUpdated('kumaIncidentId');
    }

    public function getTitle(): string {
        return (string)$this->title;
    }

    public function setTitle(string $v): void {
        $this->title = $v;
        $this->markFieldUpdated('title');
    }

    public function getContent(): string {
        return (string)$this->content;
    }

    public function setContent(string $v): void {
        $this->content = $v;
        $this->markFieldUpdated('content');
    }

    public function getStyle(): string {
        return (string)$this->style;
    }

    public function setStyle(string $v): void {
        $this->style = $v;
        $this->markFieldUpdated('style');
    }

    public function getState(): string {
        return (string)$this->state;
    }

    public function setState(string $v): void {
        $this->state = $v;
        $this->markFieldUpdated('state');
    }

    public function getErrorMessage(): string {
        return (string)$this->errorMessage;
    }

    public function setErrorMessage(string $v): void {
        $this->errorMessage = $v;
        $this->markFieldUpdated('errorMessage');
    }

    public function getCreatedAt(): int {
        return (int)$this->createdAt;
    }

    public function setCreatedAt(int $v): void {
        $this->createdAt = $v;
        $this->markFieldUpdated('createdAt');
    }

    public function getResolvedAt(): ?int {
        return $this->resolvedAt === null ? null : (int)$this->resolvedAt;
    }
    public function setResolvedAt(?int $v): void {
        $this->resolvedAt = $v;
        $this->markFieldUpdated('resolvedAt');
    }
}
