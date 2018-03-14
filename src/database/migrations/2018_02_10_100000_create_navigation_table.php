<?php

use Revys\Revy\App\Entity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNavigationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('navigation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->tinyInteger('status')->default(Entity::STATUS_PUBLISHED);
            $table->timestamps();
        });

        Schema::create('navigation_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('navigation_id')->unsigned();
            $table->string('locale')->index();
            
            $table->string('title');
        
            $table->unique(['navigation_id','locale']);
            $table->foreign('navigation_id')->references('id')->on('navigation')->onDelete('cascade');
        });

        Schema::table('navigation', function($table) {
            $table->foreign('parent_id')->references('id')->on('navigation')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('navigation_translations');
        Schema::dropIfExists('navigation');
    }
}
