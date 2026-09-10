<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class IncidentMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'uptimekuma_incidents', Incident::class);
    }

    public function find(int $id): Incident {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->tableName)
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
            ->setMaxResults(1);
        return $this->findEntity($qb);
    }

    public function findActiveByJob(int $jobId): ?Incident {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->tableName)
            ->where($qb->expr()->eq('job_id', $qb->createNamedParameter($jobId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->eq('state', $qb->createNamedParameter('active', IQueryBuilder::PARAM_STR)))
            ->setMaxResults(1);
        try {
            return $this->findEntity($qb);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function findOpenByJob(int $jobId): ?Incident {
        $qb = $this->db->getQueryBuilder();
        $stateActive = $qb->expr()->eq('state', $qb->createNamedParameter('active', IQueryBuilder::PARAM_STR));
        $stateFailed = $qb->expr()->eq('state', $qb->createNamedParameter('failed', IQueryBuilder::PARAM_STR));
        $qb->select('*')
            ->from($this->tableName)
            ->where($qb->expr()->eq('job_id', $qb->createNamedParameter($jobId, IQueryBuilder::PARAM_INT)))
            ->andWhere($qb->expr()->orX($stateActive, $stateFailed))
            ->orderBy('id', 'DESC')
            ->setMaxResults(1);
        try {
            return $this->findEntity($qb);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function findLatestByJob(int $jobId): ?Incident {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->tableName)
            ->where($qb->expr()->eq('job_id', $qb->createNamedParameter($jobId, IQueryBuilder::PARAM_INT)))
            ->orderBy('id', 'DESC')
            ->setMaxResults(1);
        try {
            return $this->findEntity($qb);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function findAllByJob(int $jobId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->tableName)
            ->where($qb->expr()->eq('job_id', $qb->createNamedParameter($jobId, IQueryBuilder::PARAM_INT)));
        return $this->findEntities($qb);
    }
}
