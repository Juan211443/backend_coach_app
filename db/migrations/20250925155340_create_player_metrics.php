<?php
// create_player_metrics.php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePlayerMetrics extends AbstractMigration
{
    public function change(): void
    {
        $this->table('player_metric')
            ->addColumn('player_id', 'integer')
            ->addColumn('metric', 'enum', [
                'values' => ['weight','height','bmi','speed','shots_accuracy','effective_touches']
            ])
            ->addColumn('value', 'decimal', ['precision' => 8, 'scale' => 2])
            ->addColumn('unit', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('recorded_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('player_id', 'player', 'person_id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
