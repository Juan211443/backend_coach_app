<?php
// create_users_and_people.php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersAndPeople extends AbstractMigration
{
    public function change(): void
    {
        $this->table('user', ['id' => 'user_id'])
            ->addColumn('email', 'string', ['limit' => 100])
            ->addColumn('password_hash', 'string', ['limit' => 255])
            ->addColumn('role', 'enum', ['values' => ['player','coach'], 'default' => 'player'])
            ->addColumn('is_active', 'boolean', ['default' => 1])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['email'], ['unique' => true])
            ->create();

        $this->table('person', ['id' => 'person_id'])
            ->addColumn('user_id', 'integer', ['null' => true])
            ->addColumn('first_name', 'string', ['limit' => 80])
            ->addColumn('last_name',  'string', ['limit' => 80])
            ->addColumn('birth_date', 'date')
            ->addColumn('preferred_foot', 'enum', [
                'values'  => ['Left','Right','Both'],
                'default' => 'Right',
                'null'    => true
            ])
            ->addColumn('height_cm', 'decimal', ['precision' => 5, 'scale' => 2, 'null' => true])
            ->addColumn('weight_kg', 'decimal', ['precision' => 5, 'scale' => 2, 'null' => true])
            ->addColumn('phone', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('profile_photo_url', 'string', ['limit' => 512, 'null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('user_id', 'user', 'user_id', ['delete' => 'SET_NULL', 'update' => 'NO_ACTION'])
            ->create();
    }
}
