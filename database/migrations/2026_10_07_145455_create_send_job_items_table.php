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
        // 一括送信の宛先ごとの結果
        Schema::create('send_job_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('send_job_id')->constrained()->cascadeOnDelete();
            // 送信リストを「すべて置き換える」で取り込み直しても履歴は残す
            $table->foreignId('recipient_id')->nullable()->constrained()->nullOnDelete();
            // 宛先が削除されても「誰に送ったか」が分かるよう、ジョブ作成時点の値を保存
            $table->string('email');
            $table->string('status', 20)->default('pending'); // App\Enums\SendJobItemStatus
            $table->text('error_message')->nullable();
            $table->dateTime('sent_at')->nullable();

            $table->index(['send_job_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('send_job_items');
    }
};
