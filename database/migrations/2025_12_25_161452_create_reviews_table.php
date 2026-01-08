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

            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('order_code');
            $table->foreign('order_code')->references('order_code')->on('histories')->cascadeOnDelete();

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
