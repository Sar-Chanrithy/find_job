<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['candidate', 'employer', 'admin'])->default('candidate')->index();
            $table->boolean('is_active')->default(true);
            $table->string('cv_path')->nullable();
            $table->string('cv_name')->nullable();
        });
        Schema::table('companies', fn (Blueprint $table) => $table->unique('owner_id'));
        Schema::table('job_posts', fn (Blueprint $table) => $table->softDeletes());
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_post_id')->constrained()->restrictOnDelete();
            $table->foreignId('candidate_id')->constrained('users')->restrictOnDelete();
            $table->string('cv_path');
            $table->string('cv_name');
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['submitted', 'reviewing', 'shortlisted', 'accepted', 'rejected', 'withdrawn'])->default('submitted');
            $table->timestamps();
            $table->unique(['job_post_id', 'candidate_id']);
        });
        Schema::create('application_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30);
            $table->timestamp('created_at')->useCurrent();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('application_events');
        Schema::dropIfExists('applications');
        Schema::table('job_posts', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('companies', fn (Blueprint $table) => $table->dropUnique(['owner_id']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['role', 'is_active', 'cv_path', 'cv_name']));
    }
};
