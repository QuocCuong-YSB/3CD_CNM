<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_product')->constrained('products')->cascadeOnDelete();

            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->tinyInteger('rating'); // 1-5
            $table->text('comment');
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
