<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MySQL の JSON 型はキーを並べ替えて保存するため、取り込み時の列順（追加項目の並び）が失われる。
     * 中身は JSON のまま、テキスト型にして書いたとおりの順序を保つ。
     */
    public function up(): void
    {
        Schema::table('recipients', function (Blueprint $table) {
            $table->longText('custom_fields')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipients', function (Blueprint $table) {
            $table->json('custom_fields')->nullable()->change();
        });
    }
};
