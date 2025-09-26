<?php
// create_catalogs.php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCatalogs extends AbstractMigration
{
    public function change(): void
    {
        $this->table('player_position', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_TINY, 'null' => false, 'signed' => false, 'identity' => false,])
            ->addColumn('code', 'string', ['limit' => 5, 'null' => true])
            ->addColumn('name', 'string', ['limit' => 40])
            ->addIndex(['code'], ['unique' => true])
            ->create();

        $this->table('sports_academy')
            ->addColumn('name', 'string', ['limit' => 120])
            ->create();

        $this->table('category')
            ->addColumn('name', 'string', ['limit' => 40])
            ->addColumn('year', 'integer', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::INT_SMALL, 'null' => true])
            ->create();
    }
}
