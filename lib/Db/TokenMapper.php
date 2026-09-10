<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class TokenMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'uptimekuma_tokens', Token::class);
    }

    public function find(int $id): Token {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->tableName)
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
            ->setMaxResults(1);
        return $this->findEntity($qb);
    }

    public function findByJob(int $jobId): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->tableName)
            ->where($qb->expr()->eq('job_id', $qb->createNamedParameter($jobId, IQueryBuilder::PARAM_INT)));
        return $this->findEntities($qb);
    }

    public function findByHash(string $hash): ?Token {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->tableName)
            ->where($qb->expr()->eq('token_hash', $qb->createNamedParameter($hash, IQueryBuilder::PARAM_STR)))
            ->andWhere($qb->expr()->eq('enabled', $qb->createNamedParameter(1, IQueryBuilder::PARAM_INT)))
            ->setMaxResults(1);
        try {
            return $this->findEntity($qb);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
