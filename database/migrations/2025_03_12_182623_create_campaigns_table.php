<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_name');
            $table->string('campaign_type');
            $table->json('platforms')->nullable();
            $table->string('campaign_goal')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('team')->nullable();
            $table->string('media_upload')->nullable();
            $table->tinyInteger('campaign_status')->default(0);
            $table->integer('attempted_users')->default(0);
            $table->integer('sent_users')->default(0);
            $table->integer('read_users')->default(0);
            $table->integer('replied_users')->default(0);
            $table->text('notes')->nullable();
            $table->string('ad_type')->nullable();
            $table->string('destination_url')->nullable();
            $table->string('goal')->nullable();
            $table->string('topic')->nullable();
            $table->integer('engagement')->default(0);
            $table->json('target_audience')->nullable();
            $table->integer('link_clicks')->default(0);
            $table->json('branches')->nullable();
            $table->text('ad_copy')->nullable();
            $table->string('video_url')->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->string('distribution_area')->nullable();
            $table->string('distribution_method')->nullable();
            $table->integer('total_quantity_distributed')->default(0);
            $table->timestamp('created_at')->nullable(); // Allow NULL values
            $table->timestamp('updated_at')->nullable(); // Allow NULL values
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            //
        });
    }
};
