<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->date('deadline')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('client_id');
            $table->index('status');
        });

        // Keep in sync with PROJECT_STATUSES in app/Models/Project.php
        DB::statement("ALTER TABLE projects ADD CONSTRAINT projects_status_check CHECK (status IN ('draft', 'active', 'completed'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};