<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moments', function (Blueprint $table) {
            $table->string('threads_status', 20)->nullable()->after('body');
            $table->string('threads_post_id')->nullable()->after('threads_status');
            $table->text('threads_last_error')->nullable()->after('threads_post_id');
            $table->timestamp('threads_published_at')->nullable()->after('threads_last_error');
            $table->timestamp('threads_attempted_at')->nullable()->after('threads_published_at');
        });
    }

    public function down(): void
    {
        Schema::table('moments', function (Blueprint $table) {
            $table->dropColumn([
                'threads_status',
                'threads_post_id',
                'threads_last_error',
                'threads_published_at',
                'threads_attempted_at',
            ]);
        });
    }
};
