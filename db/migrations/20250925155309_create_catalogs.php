<?php
// create_catalogs.php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateCatalogs extends AbstractMigration
{
    public function change(): void
    {
        if (!$this->hasTable('player_position')) {
            $this->table('player_position', ['id' => false, 'primary_key' => ['id']])
                ->addColumn('id', 'integer', ['limit' => MysqlAdapter::INT_TINY])
                ->addColumn('code', 'string', ['limit' => 5, 'null' => true])
                ->addColumn('name', 'string', ['limit' => 40])
                ->addIndex(['code'], ['unique' => true])
                ->create();
        }

        if (!$this->hasTable('sports_academy')) {
            $this->table('sports_academy')
                ->addColumn('name', 'string', ['limit' => 120])
                ->create();
        }

        if (!$this->hasTable('category')) {
            $this->table('category')
                ->addColumn('name', 'string', ['limit' => 40])
                ->addColumn('year', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'null' => true])
                ->create();
        }
    }
}
