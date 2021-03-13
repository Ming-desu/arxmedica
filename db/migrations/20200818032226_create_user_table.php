<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

use function DI\add;

final class CreateUserTable extends AbstractMigration
{
    public function up(): void
    {
        $this->execute('create database db_arxmedica');
        $table = $this->table('users', ['signed' => false]);
        $table
            ->addColumn('picture_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('first_name', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('last_name', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('sex', 'integer', ['limit' => 1, 'null' => true])
            ->addColumn('username', 'string', ['limit' => 32])
            ->addColumn('password', 'string', ['limit' => 64])
            ->addColumn('password_salt', 'string', ['limit' => 64])
            ->addColumn('status', 'string', ['limit' => 32])
            ->addTimestamps()
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('users'))
            $this->table('users')->drop()->save();
    }
}
