<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLushPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lush_players', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('middle_initial')->nullable();
            $table->string('last_name');
            $table->date('birthday');
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->date('id_expiration_date')->nullable();
            $table->enum('gender', ['M', 'F']);
            $table->unsignedBigInteger('lush_rank_id')->nullable();
            $table->foreign('lush_rank_id')->references('id')->on('lush_ranks');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->string('card_swipe_data')->nullable();
            $table->unsignedBigInteger('card_pin')->default(1111);
            $table->unsignedBigInteger('card_pin_attempts')->default(0);
            $table->boolean('is_excluded')->default(false);
            $table->boolean('email_opt_in')->default(true);
            $table->boolean('phone_opt_in')->default(true);
            $table->timestamp('registered_at');
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
        Schema::dropIfExists('lush_players');
    }
}
