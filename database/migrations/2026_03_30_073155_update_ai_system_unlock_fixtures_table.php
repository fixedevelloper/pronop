<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ai_system_unlock_fixtures', function (Blueprint $table) {

            // 🔥 supprimer ancienne colonne
            $table->dropColumn('fixture_id');
        });

        Schema::table('ai_system_unlock_fixtures', function (Blueprint $table) {

            // 🔥 recréer proprement
            $table->foreignId('fixture_id')
                ->unique()
                ->constrained('fixtures', 'id')
                ->cascadeOnDelete()
                ->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('ai_system_unlock_fixtures', function (Blueprint $table) {

            $table->dropForeign(['fixture_id']);
            $table->dropColumn('fixture_id');

            $table->string('fixture_id')->unique();
        });
    }
};
