<?php
declare(strict_types=1);

namespace OCA\UptimeKuma\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000001Date20260910000000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        $schema = $schemaClosure();

        if (!$schema->hasTable('uptimekuma_instances')) {
            $table = $schema->createTable('uptimekuma_instances');
            $table->addColumn('id', 'bigint', ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $table->addColumn('name', 'string', ['length' => 128, 'notnull' => true]);
            $table->addColumn('url', 'string', ['length' => 2048, 'notnull' => true]);
            $table->addColumn('username', 'string', ['length' => 255, 'notnull' => true]);
            $table->addColumn('password_encrypted', 'text', ['notnull' => true]);
            $table->addColumn('created_at', 'bigint', ['notnull' => true]);
            $table->addColumn('updated_at', 'bigint', ['notnull' => true]);
            $table->setPrimaryKey(['id']);
        }

        return $schema;
    }
}
