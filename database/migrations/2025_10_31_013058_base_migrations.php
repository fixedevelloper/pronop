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
            $table->string('country_code')->nullable();
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

        // POTS (FOOT)
        Schema::create('pots', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->decimal('entry_fee', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();

            $table->enum('type', ['foot', 'others']);

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
            $table->enum('status', ['pending', 'success', 'failed','closed'])->default('pending');
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
        $this->iATable();

    }
    private function iATable(){
        Schema::create('ai_predictions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('fixture_id')->constrained()->cascadeOnDelete();
            $table->unique('fixture_id');
            $table->string('source')->default('gemini');
            $table->string('match_name');

            $table->string('score_exact')->nullable();
            $table->decimal('confidence', 5, 2)->nullable();

            $table->json('raw_response')->nullable();

            $table->timestamp('predicted_at')->index();

            $table->timestamps();
        });
        Schema::create('ai_prediction_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ai_prediction_id')->constrained()->cascadeOnDelete();

            // Probabilités
            $table->decimal('home_win_prob', 5, 2)->nullable();
            $table->decimal('draw_prob', 5, 2)->nullable();
            $table->decimal('away_win_prob', 5, 2)->nullable();

            // Over/Under
            $table->decimal('over_1_5', 5, 2)->nullable();
            $table->decimal('over_2_5', 5, 2)->nullable();
            $table->decimal('over_3_5', 5, 2)->nullable();
            $table->decimal('under_2_5', 5, 2)->nullable();

            // BTTS
            $table->decimal('btts_yes', 5, 2)->nullable();
            $table->decimal('btts_no', 5, 2)->nullable();

            // Odds
            $table->decimal('odds_home', 6, 2)->nullable();
            $table->decimal('odds_draw', 6, 2)->nullable();
            $table->decimal('odds_away', 6, 2)->nullable();

            $table->decimal('odds_over_2_5', 6, 2)->nullable();
            $table->decimal('odds_under_2_5', 6, 2)->nullable();

            $table->json('best_bets')->nullable();

            $table->timestamps();
        });
        Schema::create('ai_prediction_stats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ai_prediction_id')->constrained()->cascadeOnDelete();

            $table->string('real_score')->nullable();

            $table->boolean('is_score_correct')->default(false);
            $table->boolean('is_1x2_correct')->default(false);
            $table->boolean('is_over25_correct')->default(false);
            $table->boolean('is_btts_correct')->default(false);

            $table->decimal('accuracy_score', 5, 2)->nullable();

            $table->timestamps();
        });
        Schema::create('ai_prediction_logs', function (Blueprint $table) {
            $table->id();

            $table->text('prompt');
            $table->longText('response');

            $table->integer('tokens_input')->nullable();
            $table->integer('tokens_output')->nullable();
            $table->decimal('cost', 10, 6)->nullable();

            $table->timestamps();
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
