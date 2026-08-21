<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('problem_types', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('priorities', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->unsignedInteger('ordering')->default(0);
            // ponytail: sla_target_hours nullable, diisi setelah keputusan bisnis SLA [TBD]
            $table->unsignedInteger('sla_target_hours')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('technicians', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            // ponytail: team & contact nullable sesuai PRD [TBD]
            $table->string('team')->nullable();
            $table->string('contact')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['technicians', 'priorities', 'problem_types', 'categories', 'departments'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
