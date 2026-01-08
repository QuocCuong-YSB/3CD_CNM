<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_product')->constrained('products')->cascadeOnDelete();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->string('order_code', 50); 

            $table->tinyInteger('rating'); // 1-5
            $table->text('comment');
            $table->timestamps();

            $table->unique(['id_user', 'id_product']);
            $table->foreign('order_code')->references('order_code')->on('histories')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};
