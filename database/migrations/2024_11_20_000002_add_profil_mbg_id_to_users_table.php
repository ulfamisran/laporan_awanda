<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('profil_mbg_id')->nullable()->after('email')->constrained('profil_mbg')->nullOnDelete();
            $table->string('foto')->nullable()->after('password');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('foto');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['foto', 'status']);
            $table->dropConstrainedForeignId('profil_mbg_id');
        });
    }
};
