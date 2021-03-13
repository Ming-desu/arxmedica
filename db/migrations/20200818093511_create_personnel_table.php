<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePersonnelTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('personnels', ['signed' => false]);
        $table
            ->addColumn('first_name', 'string', ['limit' => 32])
            ->addColumn('last_name', 'string', ['limit' => 32])
            ->addColumn('contact_number', 'string', ['limit' => 32, 'null' => true])
            ->addTimestamps()
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('personnels'))
            $this->table('personnels')->drop()->save();
    }
}
