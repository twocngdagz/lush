<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLushplayersLushgroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lushplayers_lushgroups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lush_player_id')->nullable();
            $table->foreign('lush_player_id')->references('id')->on('lush_players')->onDelete('cascade');;
            $table->unsignedBigInteger('lush_group_id')->nullable();
            $table->foreign('lush_group_id')->references('id')->on('lush_groups')->onDelete('cascade');;
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
        Schema::dropIfExists('lushplayers_lushgroups');
    }
}
