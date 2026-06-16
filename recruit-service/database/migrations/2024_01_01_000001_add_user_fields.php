<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['student', 'club_leader', 'admin'])->default('student')->after('phone');
            $table->string('student_id')->nullable()->unique()->after('role');
            $table->string('major')->nullable()->after('student_id');
            $table->string('grade')->nullable()->after('major');
            $table->text('bio')->nullable()->after('grade');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'student_id', 'major', 'grade', 'bio']);
        });
    }
};
