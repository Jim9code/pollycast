<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database using bincom_test.sql.
     */
    public function run(): void
    {
        $sqlPath = database_path('bincom_test.sql');
        if (!file_exists($sqlPath)) {
            $sqlPath = base_path('bincom_test.sql');
        }

        $sql = file_get_contents($sqlPath);

        // Extract all INSERT INTO ... ; statements from bincom_test.sql
        preg_match_all('/INSERT INTO\s+`[^`]+`\s*\([^)]+\)\s*VALUES\s*.*?;/is', $sql, $matches);

        DB::beginTransaction();
        try {
            foreach ($matches[0] as $insertStatement) {
                DB::unprepared($insertStatement);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
