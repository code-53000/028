<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('recruitment_post_id')->constrained()->onDelete('cascade');
            $table->text('motivation')->nullable();
            $table->text('experience')->nullable();
            $table->text('skills')->nullable();
            $table->enum('status', ['pending', 'reviewing', 'interview_scheduled', 'interview_completed', 'accepted', 'rejected'])->default('pending');
            $table->text('remark')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'recruitment_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
