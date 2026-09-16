<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('subject');
            $table->foreignId('assigned_to')->nullable()->after('priority')->constrained('users')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->after('assigned_to')->constrained()->nullOnDelete();
            $table->timestamp('sla_due_at')->nullable()->after('resolved_at');
            $table->timestamp('last_reply_at')->nullable()->after('sla_due_at');
            $table->string('dispute_reason')->nullable()->after('last_reply_at');
            $table->text('internal_note')->nullable()->after('dispute_reason');
        });

        Schema::create('support_ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('sender_type', ['customer', 'admin', 'system'])->default('customer');
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_messages');

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['order_id']);
            $table->dropColumn(['priority', 'assigned_to', 'order_id', 'sla_due_at', 'last_reply_at', 'dispute_reason', 'internal_note']);
        });
    }
};
