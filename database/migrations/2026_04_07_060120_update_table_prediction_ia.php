<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ai_predictions', function (Blueprint $table) {
            $table->text('form_teams')->nullable();
            $table->text('h2h')->nullable();
            $table->text('stat_offensive')->nullable();
            $table->text('stat_defensive')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
