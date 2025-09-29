<?php
// create_refresh_tokens.php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRefreshTokens extends AbstractMigration
{
    public function change(): void
    {
        if(!$this->hasTable('refresh_tokens')) {
            $this->table('refresh_tokens', ['id' => false, 'primary_key' => 'id'])
                ->addColumn('id', 'integer', ['identity' => true, 'signed' => false])
                ->addColumn('user_id', 'integer', ['signed' => false])
                ->addColumn('token_hash', 'string', ['limit' => 255])
                ->addColumn('lookup_hash', 'char', ['limit' => 43])
                ->addColumn('expires_at', 'datetime')
                ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('revoked_at', 'datetime', ['null' => true, 'default' => null])
                ->addIndex(['user_id'])
                ->addIndex(['lookup_hash'], ['unique' => true])
                ->addIndex(['user_id', 'expires_at'])
                ->addForeignKey('user_id', 'user', 'user_id', ['delete'=> 'CASCADE', 'update'=> 'CASCADE'])
                ->create();
        }
    }
}
