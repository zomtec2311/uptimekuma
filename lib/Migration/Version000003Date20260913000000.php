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

namespace OCA\UptimeKuma\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version000003Date20260913000000 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        $schema = $schemaClosure();

        if (!$schema->hasTable('uptimekuma_job_history')) {
            $table = $schema->createTable('uptimekuma_job_history');
            $table->addColumn('id', 'bigint', ['autoincrement' => true, 'notnull' => true, 'unsigned' => true]);
            $table->addColumn('job_id', 'bigint', ['notnull' => false, 'unsigned' => true]);
            $table->addColumn('action', 'string', ['length' => 32, 'notnull' => true]);
            $table->addColumn('source', 'string', ['length' => 128, 'notnull' => true]);
            $table->addColumn('user_id', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('ip_address', 'string', ['length' => 64, 'notnull' => true]);
            $table->addColumn('user_agent', 'text', ['notnull' => true]);
            $table->addColumn('token_id', 'bigint', ['notnull' => false, 'unsigned' => true]);
            $table->addColumn('token_description', 'string', ['length' => 255, 'notnull' => false]);
            $table->addColumn('success', 'smallint', ['notnull' => true, 'default' => 1]);
            $table->addColumn('error_message', 'text', ['notnull' => true]);
            $table->addColumn('created_at', 'bigint', ['notnull' => true]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['job_id', 'created_at'], 'uk_hist_job_time');
            $table->addIndex(['created_at'], 'uk_hist_time');
            $table->addIndex(['source', 'created_at'], 'uk_hist_source_time');
            $table->addIndex(['success', 'created_at'], 'uk_hist_success_time');
        }

        return $schema;
    }
}
