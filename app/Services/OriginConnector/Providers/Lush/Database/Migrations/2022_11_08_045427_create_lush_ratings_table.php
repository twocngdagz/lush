<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLushRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lush_ratings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lush_player_id');
            $table->foreign('lush_player_id')->references('id')->on('lush_players')->onDelete('cascade');
            $table->enum('play_type', ['slot', 'pit']);
            $table->unsignedBigInteger('points_earned');
            $table->unsignedBigInteger('cash_in');
            $table->bigInteger('theo_win');
            $table->bigInteger('actual_win');
            $table->unsignedBigInteger('comp_earned');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
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
        Schema::dropIfExists('lush_ratings');
    }
}
