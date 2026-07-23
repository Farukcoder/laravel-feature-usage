<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_usage_summaries', function (Blueprint $table) {
            $table->id();
            $table->string('controller_action')->index();
            $table->string('route_name')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->date('usage_date')->index();
            $table->unsignedInteger('hit_count')->default(0);
            $table->timestamps();

            $table->unique(
                ['controller_action', 'user_id', 'usage_date'],
                'feature_usage_unique_bucket'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_usage_summaries');
    }
};
