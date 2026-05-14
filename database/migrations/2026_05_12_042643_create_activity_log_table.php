<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50);
            $table->string('subject_type', 100);
            $table->unsignedBigInteger('subject_id');
            $table->jsonb('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
            $table->index('user_id');
            $table->index('created_at');
        });

        // Keep in sync with ACTIONS in app/Models/ActivityLog.php
        DB::statement("ALTER TABLE activity_log ADD CONSTRAINT activity_log_action_check CHECK (action IN ('created', 'updated', 'deleted', 'status_changed'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};