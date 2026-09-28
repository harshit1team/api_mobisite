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
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('author_name');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('focus_keyword')->nullable()->after('seo_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn(['seo_title', 'seo_description', 'focus_keyword']);
        });
    }
};
