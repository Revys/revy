<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Revys\Revy\App\Entity;

class CreateTextblockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('textblock', function (Blueprint $table) {
            $table->increments('id');
            $table->string(Entity::getStringIdField())->unique();
            $table->tinyInteger('status')->default(Entity::STATUS_PUBLISHED);
            $table->timestamps();
        });

        Schema::create('textblock_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('textblock_id')->unsigned();
            $table->string('locale')->index();
            
            $table->string('title');
            $table->text('text');
        
            $table->unique(['textblock_id','locale']);
            $table->foreign('textblock_id')->references('id')->on('textblock')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('textblock_translations');
        Schema::dropIfExists('textblock');
    }
}
