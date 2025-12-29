<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('order_code')->unique();
            $table->decimal('total_amount', 15, 2);
            $table->string('payment_method')->default('cod');
            $table->integer('status')->default(0)->comment('0: Waiting, 1: Confirmed, 2: Delivered, 3: Cancelled');

            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('address');
            $table->text('note')->nullable();

            $table->decimal('eco_tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->string('voucher_code')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
