<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePictureTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('pictures', ['signed' => false]);
        $table
            ->addColumn('path', 'text')
            ->addColumn('file_name', 'string', ['limit' => 255])
            ->addTimestamps()
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('pictures'))
            $this->table('pictures')->drop()->save();
    }
}
