<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAddressTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('addresses', ['signed' => false]);
        $table
            ->addColumn('street', 'string', ['limit' => 255])
            ->addColumn('municipality', 'string', ['limit' => 255])
            ->addColumn('province', 'string', ['limit' => 255])
            ->addTimestamps()
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('addresses'))
            $this->table('addresses')->drop()->save();
    }
}
