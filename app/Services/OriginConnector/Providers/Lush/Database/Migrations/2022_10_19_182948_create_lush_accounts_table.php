<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLushAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lush_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lush_player_id');
            $table->foreign('lush_player_id')->references('id')->on('lush_players');
            $table->enum('type', ['points', 'comps', 'promo', 'points_earned_today', 'comps_earned_today', 'promo_earned_today']);
            $table->bigInteger('balance')->default(0);
            $table->boolean('is_currency')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lush_accounts');
    }
}
