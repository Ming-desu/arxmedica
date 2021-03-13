<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateQuotationTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('quotations', ['signed' => false]);
        $table
            ->addColumn('representative_id', 'integer', ['signed' => false])
            ->addColumn('created_by', 'integer', ['signed' => false])
            ->addColumn('pr_no', 'string', ['limit' => 255])
            ->addColumn('date_issued', 'date')
            ->addColumn('project_title', 'string', ['limit' => 255])
            ->addColumn('project_description', 'string', ['limit' => 255])
            ->addColumn('recipients', 'string', ['limit' => 255])
            ->addTimestamps()
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('quotations'))
            $this->table('quotations')->drop()->save();
    }
}
