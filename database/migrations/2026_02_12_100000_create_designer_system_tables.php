<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Designer Applications Table
        Schema::create('designer_applications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 15);
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->text('experience')->nullable();
            $table->text('skills')->nullable();
            $table->json('portfolio_links')->nullable(); // Previous work links
            $table->json('uploaded_samples')->nullable(); // Uploaded design samples
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
        });

        // Designer Profiles Table
        Schema::create('designer_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->unsignedBigInteger('application_id');
            $table->string('display_name');
            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();
            $table->json('specializations')->nullable(); // ['logo', 'social-media', 'invitation']
            $table->decimal('commission_rate', 5, 2)->default(30.00); // 30% commission
            $table->boolean('is_active')->default(true);
            $table->integer('total_designs')->default(0);
            $table->integer('approved_designs')->default(0);
            $table->integer('live_designs')->default(0);
            $table->decimal('total_earnings', 10, 2)->default(0);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('application_id')->references('id')->on('designer_applications')->onDelete('cascade');
        });

        // Designer Wallets Table
        Schema::create('designer_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('designer_id')->unique();
            $table->decimal('balance', 10, 2)->default(0);
            $table->decimal('total_earned', 10, 2)->default(0);
            $table->decimal('total_withdrawn', 10, 2)->default(0);
            $table->decimal('pending_amount', 10, 2)->default(0);
            $table->decimal('withdrawal_threshold', 10, 2)->default(500.00); // Min 500 to withdraw
            $table->timestamps();
            
            $table->foreign('designer_id')->references('id')->on('designer_profiles')->onDelete('cascade');
        });

        // Designer Transactions Table
        Schema::create('designer_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('designer_id');
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('transaction_type'); // 'design_sale', 'withdrawal', 'bonus', 'adjustment'
            $table->text('description')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable(); // design_id or withdrawal_id
            $table->string('reference_type')->nullable(); // 'design_submission', 'withdrawal'
            $table->timestamps();
            
            $table->foreign('designer_id')->references('id')->on('designer_profiles')->onDelete('cascade');
            $table->index(['designer_id', 'created_at']);
        });

        // Design Submissions Table
        Schema::create('design_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('designer_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category'); // 'template', 'video', 'sticker', etc.
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('design_file_path'); // Main design file
            $table->json('preview_images')->nullable(); // Preview images
            $table->json('tags')->nullable();
            $table->enum('status', ['pending_designer_head', 'approved_by_designer_head', 'rejected_by_designer_head', 'pending_seo', 'approved_by_seo', 'rejected_by_seo', 'live'])->default('pending_designer_head');
            $table->text('designer_head_notes')->nullable();
            $table->text('seo_head_notes')->nullable();
            $table->unsignedBigInteger('designer_head_reviewed_by')->nullable();
            $table->timestamp('designer_head_reviewed_at')->nullable();
            $table->unsignedBigInteger('seo_head_reviewed_by')->nullable();
            $table->timestamp('seo_head_reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->integer('total_sales')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->timestamps();
            
            $table->foreign('designer_id')->references('id')->on('designer_profiles')->onDelete('cascade');
            $table->foreign('designer_head_reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('seo_head_reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['designer_id', 'status']);
        });

        // Design SEO Details Table
        Schema::create('design_seo_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('design_submission_id')->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('slug')->unique()->nullable();
            $table->json('keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->integer('priority')->default(0);
            $table->timestamps();
            
            $table->foreign('design_submission_id')->references('id')->on('design_submissions')->onDelete('cascade');
        });

        // Designer Withdrawals Table
        Schema::create('designer_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('designer_id');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'rejected'])->default('pending');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('upi_id')->nullable();
            $table->enum('payment_method', ['bank_transfer', 'upi'])->default('bank_transfer');
            $table->string('transaction_reference')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('designer_id')->references('id')->on('designer_profiles')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['designer_id', 'status']);
        });

        // Design Sales Tracking Table
        Schema::create('design_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('design_submission_id');
            $table->unsignedBigInteger('designer_id');
            $table->unsignedBigInteger('purchase_history_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable(); // Buyer
            $table->decimal('sale_amount', 10, 2);
            $table->decimal('designer_commission', 10, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->foreign('design_submission_id')->references('id')->on('design_submissions')->onDelete('cascade');
            $table->foreign('designer_id')->references('id')->on('designer_profiles')->onDelete('cascade');
            $table->index(['designer_id', 'payment_status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('design_sales');
        Schema::dropIfExists('designer_withdrawals');
        Schema::dropIfExists('design_seo_details');
        Schema::dropIfExists('design_submissions');
        Schema::dropIfExists('designer_transactions');
        Schema::dropIfExists('designer_wallets');
        Schema::dropIfExists('designer_profiles');
        Schema::dropIfExists('designer_applications');
    }
};
