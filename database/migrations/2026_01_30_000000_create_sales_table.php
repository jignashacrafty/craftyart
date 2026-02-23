<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('crafty_revenue_mysql')->create('sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_person_id');
            $table->string('user_name');
            $table->string('email');
            $table->string('contact_no');
            $table->enum('payment_method', ['razorpay', 'phonepe'])->default('razorpay');
            $table->string('plan_id')->nullable();
            $table->enum('subscription_type', ['old_sub', 'new_sub']);
            $table->decimal('amount', 10, 2);
            $table->enum('plan_type', ['personal', 'professional']);
            $table->string('reference_id')->unique();
            $table->string('payment_link_id')->nullable(); // Razorpay payment link ID
            $table->text('payment_link_url')->nullable();
            $table->text('short_url')->nullable();
            $table->enum('status', ['created', 'paid', 'failed', 'expired'])->default('created');
            $table->unsignedBigInteger('order_id')->nullable(); // Link to orders table after payment
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('sales_person_id');
            $table->index('reference_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('crafty_revenue_mysql')->dropIfExists('sales');
    }
};
