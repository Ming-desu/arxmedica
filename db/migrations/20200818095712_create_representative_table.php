<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRepresentativeTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('representatives', ['signed' => false]);
        $table
            ->addColumn('first_name', 'string', ['limit' => 32])
            ->addColumn('last_name', 'string', ['limit' => 32])
            ->addColumn('contact_number', 'string', ['limit' => 32, 'null' => true])
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('representatives'))
            $this->table('representatives')->drop()->save();
    }
}
