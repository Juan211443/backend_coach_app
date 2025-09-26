<?php
// create_sessions_attendance.php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSessionsAttendance extends AbstractMigration
{
    public function change(): void
    {
        if (!$this->hasTable('session')) {
            $this->table('session')
                ->addColumn('team_id', 'integer', ['signed' => false])
                ->addColumn('type', 'enum', ['values' => ['training','match']])
                ->addColumn('date', 'date')
                ->addColumn('starts_at', 'time', ['null' => true])
                ->addColumn('ends_at', 'time', ['null' => true])
                ->addColumn('location', 'string', ['limit' => 120, 'null' => true])
                ->addColumn('opponent', 'string', ['limit' => 120, 'null' => true])
                ->addColumn('notes', 'string', ['limit' => 255, 'null' => true])
                ->addForeignKey('team_id', 'team', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->create();
        }

        if (!$this->hasTable('attendance')) {
            $this->table('attendance')
                ->addColumn('session_id', 'integer', ['signed' => false])
                ->addColumn('player_id', 'integer', ['signed' => false])
                ->addColumn('status', 'enum', ['values' => ['present','absent','late','excused']])
                ->addColumn('checkin_at', 'datetime', ['null' => true])
                ->addColumn('remarks', 'string', ['limit' => 255, 'null' => true])
                ->addIndex(['session_id','player_id'], ['unique' => true, 'name' => 'uq_session_player'])
                ->addForeignKey('session_id', 'session', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->addForeignKey('player_id', 'player', 'person_id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->create();
        } else {
            if (!$this->table('attendance')->hasIndexByName('uq_session_player')) {
                $this->table('attendance')->addIndex(
                    ['session_id','player_id'],
                    ['unique' => true, 'name' => 'uq_session_player']
                )->update();
            }
        }
    }
}
