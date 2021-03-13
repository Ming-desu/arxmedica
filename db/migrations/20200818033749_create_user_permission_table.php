<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserPermissionTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('user_permissions', ['id' => false, 'primary_key' => 'user_id']);
        $table
            ->addColumn('user_id', 'integer', ['signed' => false])
            ->addColumn('permission', 'text')
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('user_permissions'))
            $this->table('user_permissions')->drop()->save();
    }
}
