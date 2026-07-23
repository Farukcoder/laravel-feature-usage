<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('feature_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('controller_action')->nullable()->index();
            $table->string('route_name')->nullable()->index();
            $table->string('method', 10);
            $table->string('uri');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_usage_logs');
    }
};
