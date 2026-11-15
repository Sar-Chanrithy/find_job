<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('logo_text', 12);
            $table->string('brand_color', 7)->default('#8250ce');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category', 80)->index();
            $table->string('location', 80)->index();
            $table->enum('employment_type', ['Full time', 'Part time', 'Internship']);
            $table->enum('work_mode', ['Remote', 'Hybrid', 'On site']);
            $table->unsignedInteger('salary_min');
            $table->unsignedInteger('salary_max');
            $table->string('currency', 3)->default('USD');
            $table->text('description');
            $table->json('responsibilities');
            $table->json('requirements');
            $table->text('benefits');
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->boolean('is_demo')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
        Schema::dropIfExists('companies');
    }
};
