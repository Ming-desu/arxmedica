<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateRecipientTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('recipients', ['signed' => false]);
        $table
            ->addColumn('address_id', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('first_name', 'string', ['limit' => 32])
            ->addColumn('last_name', 'string', ['limit' => 32])
            ->addColumn('position', 'string', ['limit' => 255])
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('recipients'))
            $this->table('recipients')->drop()->save();
    }
}
