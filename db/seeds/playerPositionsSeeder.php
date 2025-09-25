<?php
// playerPositionsSeeder.php
use Phinx\Seed\AbstractSeed;

class PlayerPositionsSeeder extends AbstractSeed
{
    public function run(): void
    {
        $rows = [
            ['id'=>1, 'code'=>'GK', 'name'=>'Goalkeeper'],
            ['id'=>2, 'code'=>'RB', 'name'=>'Right Back'],
            ['id'=>3, 'code'=>'CB', 'name'=>'Center Back'],
            ['id'=>4, 'code'=>'LB', 'name'=>'Left Back'],
            ['id'=>5, 'code'=>'DM', 'name'=>'Defensive Midfielder'],
            ['id'=>6, 'code'=>'CM', 'name'=>'Central Midfielder'],
            ['id'=>7, 'code'=>'AM', 'name'=>'Attacking Midfielder'],
            ['id'=>8, 'code'=>'RW', 'name'=>'Right Winger'],
            ['id'=>9, 'code'=>'ST', 'name'=>'Striker'],
            ['id'=>10,'code'=>'LW', 'name'=>'Left Winger'],
        ];

        $this->table('player_position')->insert($rows)->saveData();
    }
}
