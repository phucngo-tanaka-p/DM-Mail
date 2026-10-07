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
        // 送信リスト
        Schema::create('recipients', function (Blueprint $table) {
            $table->id(); // 画面では「No」として表示
            $table->string('email')->index(); // 宛先アドレス (To)
            $table->text('cc')->nullable(); // 複数の場合は「;」区切り
            $table->text('bcc')->nullable(); // 複数の場合は「;」区切り
            $table->string('company_name')->nullable(); // 宛先会社（団体）名
            $table->string('person_name')->nullable(); // 宛先名前
            $table->string('honorific')->default('様'); // 敬称
            $table->json('custom_fields')->nullable(); // 追加項目（旧 #$1$#〜#$30$#）。キー＝取り込み時の列見出し
            $table->boolean('exclude')->default(false); // 送信しない
            $table->dateTime('unsubscribed_at')->nullable(); // 配信停止日
            $table->dateTime('last_sent_at')->nullable(); // 送信済
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipients');
    }
};
