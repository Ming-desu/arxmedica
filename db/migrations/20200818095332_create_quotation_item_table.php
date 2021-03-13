<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateQuotationItemTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('quotation_items', ['signed' => false]);
        $table
            ->addColumn('quotation_id', 'integer', ['signed' => false])
            ->addColumn('product_id', 'integer', ['signed' => false])
            ->addColumn('quantity', 'integer', ['signed' => false])
            ->addColumn('unit_cost', 'double', ['signed' => false])
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('quotation_items'))
            $this->table('quotation_items')->drop()->save();
    }
}
