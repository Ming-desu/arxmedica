<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUnitTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('units', ['signed' => false]);
        $table
            ->addColumn('name', 'string', ['limit' => 255])
            ->addTimestamps()
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('units'))
            $this->table('units')->drop()->save();
    }
}
