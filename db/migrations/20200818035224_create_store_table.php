<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStoreTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('stores', ['signed' => false]);
        $table
            ->addColumn('slug', 'text')
            ->addColumn('banner_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('address_id', 'integer', ['signed' => false])
            ->addColumn('personnel_id', 'integer', ['signed' => false])
            ->addColumn('name', 'string', ['limit' => 255])
            ->addTimestamps()
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('stores'))
            $this->table('stores')->drop()->save();
    }
}
