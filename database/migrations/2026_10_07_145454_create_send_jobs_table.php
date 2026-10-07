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
        // 一括送信1回分
        Schema::create('send_jobs', function (Blueprint $table) {
            $table->id();
            // テンプレートを削除しても送信履歴は残す
            $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
            // 送信開始時点のテンプレート内容。送信中にテンプレートが編集・削除されても
            // 全員に同じ内容が届き、失敗分の再送信も同じ内容になる
            $table->string('subject');
            $table->longText('body_html');
            $table->longText('body_text');
            $table->string('status', 20)->default('sending'); // App\Enums\SendJobStatus
            $table->string('batch_id')->nullable(); // Bus::batch() の ID
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('sent')->default(0);
            $table->unsignedInteger('failed')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('send_jobs');
    }
};
