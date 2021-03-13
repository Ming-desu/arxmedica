<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCategoryTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('categories', ['signed' => false]);
        $table
            ->addColumn('name', 'string', ['limit' => 255])
            ->addTimestamps()
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('categories'))
            $this->table('categories')->drop()->save();
    }
}
