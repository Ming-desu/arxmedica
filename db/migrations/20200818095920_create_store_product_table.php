<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStoreProductTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('store_products', ['signed' => false]);
        $table
            ->addColumn('store_id', 'integer', ['signed' => false])
            ->addColumn('product_id', 'integer', ['signed' => false])
            ->addColumn('unit_cost', 'double', ['signed' => false])
            ->addTimestamps()
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('store_products'))
            $this->table('store_products')->drop()->save();
    }
}
