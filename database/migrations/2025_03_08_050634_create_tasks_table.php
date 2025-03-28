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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('project_id')->nullable()->change(); // Project ID as foreign key
            $table->string('task_title'); // Task Title
            $table->string('task_type'); // Task Type
            $table->date('due_date')->nullable(); // Due Date
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium'); // Priority Levels
            $table->string('department')->nullable(); // Department
            $table->json('team')->nullable(); 
            $table->string('link')->nullable(); 
            $table->enum('visibility', ['Public', 'Private']); 
            $table->json('attachment')->nullable(); 
            $table->text('description')->nullable(); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
