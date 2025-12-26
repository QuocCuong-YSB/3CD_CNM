<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('histories', function (Blueprint $table) {
            $table->foreignId('id_user')->constrained('users');

            $table->foreignId('id_product')->constrained('products');

            $table->decimal('price', 15, 2);
            $table->integer('quantity');

            $table->tinyInteger('status')->default(0);

            $table->string('order_code')->index();

            $table->string('payment_method')->default('cod');
            $table->string('address');
            $table->text('note')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histories');
    }
}
