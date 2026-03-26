<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moment_images', function (Blueprint $table): void {
            $table->foreignId('moment_id')->nullable()->change();
        });
    }
};
