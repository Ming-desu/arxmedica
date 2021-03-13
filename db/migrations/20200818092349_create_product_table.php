<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProductTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('products', ['signed' => false]);
        $table
            ->addColumn('category_id', 'integer', ['signed' => false])
            ->addColumn('unit_id', 'integer', ['signed' => false])
            ->addColumn('slug', 'text', ['null' => true])
            ->addColumn('brand', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('description', 'string', ['limit' => 255])
            ->addTimestamps()
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('products'))
            $this->table('products')->drop()->save();
    }
}
