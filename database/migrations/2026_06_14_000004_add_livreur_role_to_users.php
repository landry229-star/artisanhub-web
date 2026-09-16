<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // SQLite ne supporte pas ALTER COLUMN sur enum
        // Cette migration est pour MySQL/PostgreSQL uniquement
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','artisan','client','livreur') NOT NULL DEFAULT 'client'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','artisan','client') NOT NULL DEFAULT 'client'");
        }
    }
};
