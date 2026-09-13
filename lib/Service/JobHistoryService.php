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

use OCA\UptimeKuma\Db\{JobHistory, JobHistoryMapper, JobMapper, TokenMapper};
use OCP\IRequest;
use OCP\IUserSession;

class JobHistoryService {
    public function __construct(
        private JobHistoryMapper $history,
        private JobMapper $jobs,
        private TokenMapper $tokens,
        private IRequest $request,
        private IUserSession $userSession
    ) {}

    public function log(
        ?int $jobId,
        string $action,
        string $source,
        bool $success,
        string $errorMessage = '',
        ?int $tokenId = null
    ): void {
        $entry = new JobHistory();
        $entry->setJobId($jobId);
        $entry->setAction($this->limit($action, 32));
        $entry->setSource($this->limit($source, 128));
        $user = $this->userSession->getUser();
        $entry->setUserId($user ? $this->limit($user->getUID(), 255) : null);
        $entry->setIpAddress($this->limit((string)$this->request->getRemoteAddress(), 64));
        $entry->setUserAgent($this->limit((string)$this->request->getHeader('User-Agent'), 1024));
        $entry->setTokenId($tokenId);
        if ($tokenId !== null) {
            try {
                $entry->setTokenDescription($this->tokens->find($tokenId)->getDescription());
            } catch (\Throwable $e) {
                $entry->setTokenDescription(null);
            }
        } else {
            $entry->setTokenDescription(null);
        }
        $entry->setSuccess($success);
        $entry->setErrorMessage($this->limit($errorMessage, 4000));
        $entry->setCreatedAt(time());
        $this->history->insert($entry);
    }

    public function source(string $fallback): string {
        $header = trim((string)$this->request->getHeader('X-UptimeKuma-Source'));
        if ($header !== '') {
            return $fallback . ': ' . $this->limit($header, 110);
        }
        return $fallback;
    }

    public function list(
        ?int $jobId,
        string $action,
        string $source,
        ?bool $success,
        string $search,
        string $sort,
        string $direction,
        int $limit,
        int $offset
    ): array {
        $limit = max(1, min($limit, 100));
        $offset = max(0, $offset);
        $rows = $this->history->findFiltered($jobId, $action, $source, $success, trim($search), $sort, $direction, $limit, $offset);
        $hasMore = count($rows) > $limit;
        if ($hasMore) {
            array_pop($rows);
        }

        $jobs = [];
        foreach ($this->jobs->findAll() as $job) {
            $jobs[$job->getId()] = $job->getName();
        }

        $items = array_map(function (JobHistory $entry) use ($jobs): array {
            $id = $entry->getJobId();
            return [
                'id' => $entry->getId(),
                'jobId' => $id,
                'jobName' => $id !== null ? ($jobs[$id] ?? ('Job #' . $id)) : '',
                'action' => $entry->getAction(),
                'source' => $entry->getSource(),
                'userId' => $entry->getUserId(),
                'ipAddress' => $entry->getIpAddress(),
                'userAgent' => $entry->getUserAgent(),
                'tokenId' => $entry->getTokenId(),
                'tokenDescription' => $entry->getTokenDescription(),
                'success' => $entry->getSuccess(),
                'errorMessage' => $entry->getErrorMessage(),
                'createdAt' => $entry->getCreatedAt(),
            ];
        }, $rows);

        return [
            'items' => $items,
            'hasMore' => $hasMore,
            'offset' => $offset,
            'limit' => $limit,
        ];
    }

    private function limit(string $value, int $length): string {
        return function_exists('mb_substr') ? mb_substr($value, 0, $length) : substr($value, 0, $length);
    }
}
