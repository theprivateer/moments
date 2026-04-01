<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Privateer\Moments\Support\Moments as MomentsSupport;

return new class extends Migration {
    public function up(): void
    {
        Schema::table(MomentsSupport::momentImageTable(), function (Blueprint $table): void {
            $table->text('alt_text')->nullable()->after('disk');
        });
    }
};
