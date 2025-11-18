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
        // LEAGUES
        Schema::create('leagues', function (Blueprint $table) {
            $table->id();
            $table->integer('league_id')->index();
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        // FIXTURES (FOOT)
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->integer('fixture_id');
            $table->string('referee');
            $table->string('timezone');
            $table->string('timestamp');
            $table->string('date');
            $table->string('st_long');//status
            $table->string('st_short');
            $table->string('st_elapsed');
            $table->integer('league_id');
            $table->integer('league_season')->nullable();
            $table->string('league_round')->nullable();
            $table->string('team_home_name')->nullable();
            $table->string('team_home_logo')->nullable();
            $table->string('team_away_name')->nullable();
            $table->string('team_away_logo')->nullable();
            $table->boolean('team_away_winner')->default(false);
            $table->boolean('team_home_winner')->default(false);
            $table->integer('goal_home')->nullable();
            $table->integer('goal_away')->nullable();
            $table->integer('score_ht_home')->nullable();
            $table->integer('score_ht_away')->nullable();
            $table->integer('score_ft_home')->nullable();
            $table->integer('score_ft_away')->nullable();
            $table->integer('score_ext_home')->nullable();
            $table->integer('score_ext_away')->nullable();
            $table->integer('score_pt_home')->nullable();//score penalty
            $table->integer('score_pt_away')->nullable();
            $table->string('day_timestamp')->nullable();

            $table->timestamps();
        });

        // PMU COURSES
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('reunion')->nullable();
            $table->string('date_course');
            $table->timestamps();
        });

        // PMU Participants
        Schema::create('partant_courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->enum('status', ['1v', '2v', 'x'])->default('x');
            $table->timestamps();
        });

        // POTS (FOOT OU PMU)
        Schema::create('pots', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->decimal('entry_fee', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();

            $table->enum('type', ['foot', 'pmu']);

            $table->enum('status', ['open', 'closed', 'settled'])->default('open');

            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();

            $table->string('distribution_rule')->default('winner_takes_all');
            $table->foreignId('createdBy')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Line pot FOOT
        Schema::create('line_pot_foot', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('pot_id')->constrained('pots')->cascadeOnDelete();
            $table->foreignId('fixture_id')->constrained('fixtures')->cascadeOnDelete();
            $table->enum('result', ['1v', '2v', 'x', 'pending'])->default('pending');
            $table->timestamps();
        });

        // Line pot PMU
        Schema::create('line_pot_pmu', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('pot_id')->constrained('pots')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->enum('status', ['1v', '2v', 'x'])->default('x');
            $table->timestamps();
        });

        // PREDICTIONS (FOOT)
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_pot_foot_id')->constrained('line_pot_foot')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('prediction', ['1v', '2v', 'x']);
            $table->timestamps();
        });

        // SUBSCRIPTION TO POTS
        Schema::create('subscription_pots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pot_id')->constrained()->cascadeOnDelete();
            $table->string('gateway')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamps();
        });
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pot_id')->nullable();
            $table->enum('type', ['deposit', 'commission', 'win','withdrawal','pot_entry']);
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->string('reference')->unique();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
