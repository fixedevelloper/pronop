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
        // =========================
        // USERS (wallet)
        // =========================
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('tokens')->default(0)->index();
        });

        // =========================
        // USER MATCH UNLOCKS
        // =========================
        Schema::create('ia_user_match_unlocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fixture_id')
                ->constrained('fixtures', 'id')
                ->cascadeOnDelete()
                //->index()
            ; // 🔥 perf

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                //->index()
            ; // 🔥 perf

            $table->enum('type', ['analysis', 'prediction'])
                ->default('analysis')
                ->index(); // 🔥 filtrage rapide

            $table->unsignedInteger('price')->default(0);

            // 🔥 BONUS tracking
            $table->timestamp('unlocked_at')->useCurrent();

            $table->timestamps();

            // 🔥 anti double achat (critique)
            $table->unique(['user_id', 'fixture_id', 'type'], 'unique_user_fixture_type');
        });
        Schema::create('ia_unlock_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixture_id')->constrained('fixtures')->cascadeOnDelete();
            $table->enum('method', ['tokens', 'balance'])->default('balance');
            $table->integer('amount')->default(0);
            $table->enum('type', ['analysis', 'prediction'])->default('analysis');
            $table->timestamps();
        });

        // =========================
        // SYSTEM UNLOCK (admin/IA)
        // =========================
        Schema::create('ai_system_unlock_fixtures', function (Blueprint $table) {
            $table->id();

            $table->string('fixture_id')->unique();

            $table->date('date_play')->index();

            $table->boolean('is_free')->default(false); // 🔥 meilleur naming

            $table->timestamps();
        });

        // =========================
        // TOKEN PACKAGES
        // =========================
        Schema::create('ia_token_packages', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->unsignedInteger('tokens');
            $table->unsignedInteger('price');

            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
        });

        // =========================
        // SUBSCRIPTIONS
        // =========================
        Schema::create('ia_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('plan', ['weekly', 'monthly']);

            $table->unsignedInteger('price');

            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();

            $table->boolean('active')->default(true)->index();

            $table->timestamps();
        });

        // =========================
        // TRANSACTIONS (🔥 MANQUAIT)
        // =========================
        Schema::create('ia_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['deposit', 'purchase', 'subscription']);

            $table->unsignedInteger('amount');

            $table->string('reference')->nullable(); // MoMo, Stripe...

            $table->timestamps();
        });

        // =========================
        // LEAGUES
        // =========================
        Schema::table('leagues', function (Blueprint $table) {
            $table->string('country_name')->nullable()->index();
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
