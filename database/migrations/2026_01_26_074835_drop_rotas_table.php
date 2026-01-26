<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only drop rota_id column if it exists (it was added to production but not in migrations)
        if (Schema::hasColumn('shifts', 'rota_id')) {
            $driver = DB::connection()->getDriverName();

            if ($driver === 'mysql') {
                Schema::table('shifts', function (Blueprint $table) {
                    $table->dropForeign(['rota_id']);
                    $table->dropIndex('shifts_rota_id_index');
                    $table->dropColumn('rota_id');
                });
            } else {
                Schema::table('shifts', function (Blueprint $table) {
                    $table->dropColumn('rota_id');
                });
            }
        }

        Schema::dropIfExists('rotas');
    }

    public function down(): void
    {
        // The rotas table is legacy and should not be recreated
    }
};
