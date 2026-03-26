<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Privateer\Moments\Support\Moments;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('moments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained(Moments::userTable())->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->timestamps();
        });
    }
};
