<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_category')->constrained('categories');

            $table->foreignId('id_brand')->constrained('brands');
            
            $table->string('name');
            $table->json('image')->nullable();

            $table->decimal('price', 15, 2);
            $table->integer('status');
            $table->integer('sale');

            $table->text('detail');

            $table->integer('quantity');
            $table->integer('quantity_sold')->default(0);

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
        Schema::dropIfExists('products');
    }
}
