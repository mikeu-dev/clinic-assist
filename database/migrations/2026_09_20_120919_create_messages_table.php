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
        Schema::create('messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->string('wa_message_id')->nullable()->index();
            $table->string('direction'); // incoming, outgoing
            $table->string('message_type')->default('text'); // text, interactive, image, etc.
            $table->text('body');
            $table->json('raw_payload')->nullable();
            $table->string('status')->default('received'); // received, sent, delivered, read, failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
