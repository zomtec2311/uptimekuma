<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class InstanceMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'uptimekuma_instances', Instance::class);
    }

    public function findAll(): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')->from($this->tableName);
        return $this->findEntities($qb);
    }

    public function find(int $id): Instance {
        $qb = $this->db->getQueryBuilder();
        $qb->select('*')
            ->from($this->tableName)
            ->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
            ->setMaxResults(1);
        return $this->findEntity($qb);
    }
}
