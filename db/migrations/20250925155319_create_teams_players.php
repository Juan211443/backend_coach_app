<?php
// create_teams_players.php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class CreateTeamsPlayers extends AbstractMigration
{
    public function change(): void
    {
        $this->table('team')
            ->addColumn('sports_academy_id', 'integer', ['signed' => false])
            ->addColumn('name', 'string', ['limit' => 80])
            ->addColumn('category_id', 'integer', ['signed' => false])
            ->addColumn('coach_person_id', 'integer', ['null' => true, 'signed' => false])
            ->addForeignKey('sports_academy_id', 'sports_academy', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
            ->addForeignKey('category_id', 'category', 'id', ['delete' => 'NO_ACTION', 'update' => 'NO_ACTION'])
            ->addForeignKey('coach_person_id', 'person', 'person_id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->create();

        $this->table('player', ['id' => false, 'primary_key' => ['person_id']])
            ->addColumn('person_id', 'integer', ['signed' => false])
            ->addColumn('jersey_number', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'null' => true])
            ->addColumn('position_id', 'integer', ['limit' => MysqlAdapter::INT_TINY, 'null' => true, 'signed' => false])
            ->addColumn('current_category_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('sports_academy_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('enrollment_year', 'integer', ['limit' => MysqlAdapter::INT_SMALL, 'null' => true])
            ->addColumn('health_status', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('current_injuries', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('current_team_id', 'integer', ['null' => true, 'signed' => false])
            ->addForeignKey('person_id', 'person', 'person_id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('position_id', 'player_position', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->addForeignKey('current_category_id', 'category', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->addForeignKey('sports_academy_id', 'sports_academy', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->addForeignKey('current_team_id', 'team', 'id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->addIndex(['current_team_id', 'jersey_number'], ['unique' => true, 'name' => 'uq_team_jersey'])
            ->create();
    }
}
