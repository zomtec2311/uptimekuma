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

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class JobHistoryMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'uptimekuma_job_history', JobHistory::class);
    }

    public function findFiltered(
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
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->tableName);

        if ($jobId !== null && $jobId > 0) {
            $qb->andWhere($qb->expr()->eq('job_id', $qb->createNamedParameter($jobId, IQueryBuilder::PARAM_INT)));
        }
        if ($action !== '') {
            $qb->andWhere($qb->expr()->eq('action', $qb->createNamedParameter($action, IQueryBuilder::PARAM_STR)));
        }
        if ($source !== '') {
            if ($source === 'external-api') {
                $qb->andWhere($qb->expr()->like('source', $qb->createNamedParameter('external-api%', IQueryBuilder::PARAM_STR)));
            } else {
                $qb->andWhere($qb->expr()->eq('source', $qb->createNamedParameter($source, IQueryBuilder::PARAM_STR)));
            }
        }
        if ($success !== null) {
            $qb->andWhere($qb->expr()->eq('success', $qb->createNamedParameter($success ? 1 : 0, IQueryBuilder::PARAM_INT)));
        }
        if ($search !== '') {
            $like = '%' . $this->db->escapeLikeParameter($search) . '%';
            $searchParam = $qb->createNamedParameter($like, IQueryBuilder::PARAM_STR);
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('action', $searchParam),
                $qb->expr()->like('source', $searchParam),
                $qb->expr()->like('user_id', $searchParam),
                $qb->expr()->like('ip_address', $searchParam),
                $qb->expr()->like('user_agent', $searchParam),
                $qb->expr()->like('error_message', $searchParam)
            ));
        }

        $allowedSorts = [
            'createdAt' => 'created_at',
            'jobId' => 'job_id',
            'action' => 'action',
            'source' => 'source',
            'userId' => 'user_id',
            'ipAddress' => 'ip_address',
            'success' => 'success',
        ];
        $sortColumn = $allowedSorts[$sort] ?? 'created_at';
        $sortDirection = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy($sortColumn, $sortDirection)
            ->addOrderBy('id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit + 1);

        return $this->findEntities($qb);
    }

    public function allentries(): int {
        $qb = $this->db->getQueryBuilder();

        $qb->select(
            $qb->func()->count('id')
        )
        ->from($this->tableName);

        return (int) $qb
            ->executeQuery()
            ->fetchOne();
    }

    public function countfindFiltered(
        ?int $jobId,
        string $action,
        string $source,
        ?bool $success,
        string $search,
        string $sort,
        string $direction
    ): int {
        $qb = $this->db->getQueryBuilder();
        $qb->select(
            $qb->func()->count('id')
        )
        ->from($this->tableName);

        if ($jobId !== null && $jobId > 0) {
            $qb->andWhere($qb->expr()->eq('job_id', $qb->createNamedParameter($jobId, IQueryBuilder::PARAM_INT)));
        }
        if ($action !== '') {
            $qb->andWhere($qb->expr()->eq('action', $qb->createNamedParameter($action, IQueryBuilder::PARAM_STR)));
        }
        if ($source !== '') {
            if ($source === 'external-api') {
                $qb->andWhere($qb->expr()->like('source', $qb->createNamedParameter('external-api%', IQueryBuilder::PARAM_STR)));
            } else {
                $qb->andWhere($qb->expr()->eq('source', $qb->createNamedParameter($source, IQueryBuilder::PARAM_STR)));
            }
        }
        if ($success !== null) {
            $qb->andWhere($qb->expr()->eq('success', $qb->createNamedParameter($success ? 1 : 0, IQueryBuilder::PARAM_INT)));
        }
        if ($search !== '') {
            $like = '%' . $this->db->escapeLikeParameter($search) . '%';
            $searchParam = $qb->createNamedParameter($like, IQueryBuilder::PARAM_STR);
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('action', $searchParam),
                $qb->expr()->like('source', $searchParam),
                $qb->expr()->like('user_id', $searchParam),
                $qb->expr()->like('ip_address', $searchParam),
                $qb->expr()->like('user_agent', $searchParam),
                $qb->expr()->like('error_message', $searchParam)
            ));
        }

        $allowedSorts = [
            'createdAt' => 'created_at',
            'jobId' => 'job_id',
            'action' => 'action',
            'source' => 'source',
            'userId' => 'user_id',
            'ipAddress' => 'ip_address',
            'success' => 'success',
        ];
        $sortColumn = $allowedSorts[$sort] ?? 'created_at';
        $sortDirection = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy($sortColumn, $sortDirection)
            ->addOrderBy('id', 'DESC');

        return (int) $qb
            ->executeQuery()
            ->fetchOne();
    }

}
