<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moment_images', function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0)->after('disk');
            $table->index(['moment_id', 'sort_order']);
        });

        DB::table('moment_images')
            ->select('moment_id')
            ->whereNotNull('moment_id')
            ->groupBy('moment_id')
            ->orderBy('moment_id')
            ->lazy()
            ->each(function (object $row): void {
                $images = DB::table('moment_images')
                    ->where('moment_id', $row->moment_id)
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->get(['id']);

                foreach ($images as $index => $image) {
                    DB::table('moment_images')
                        ->where('id', $image->id)
                        ->update(['sort_order' => $index + 1]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('moment_images', function (Blueprint $table): void {
            $table->dropIndex(['moment_id', 'sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
