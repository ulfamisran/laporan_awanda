<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relawans', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('nik', 16)->unique()->after('id');
            $table->string('nama_lengkap')->after('nik');
            $table->foreignId('posisi_relawan_id')->after('nama_lengkap')->constrained('posisi_relawan')->restrictOnDelete();
            $table->string('jenis_kelamin', 1)->after('profil_mbg_id');
            $table->string('no_hp', 32)->after('jenis_kelamin');
            $table->string('email')->nullable()->after('no_hp');
            $table->text('alamat')->after('email');
            $table->date('tanggal_lahir')->after('alamat');
            $table->date('tanggal_bergabung')->after('tanggal_lahir');
            $table->string('foto')->nullable()->after('tanggal_bergabung');
            $table->decimal('gaji_pokok', 15, 2)->default(0)->after('foto');
            $table->string('status', 20)->default('aktif')->after('gaji_pokok');
            $table->text('keterangan')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('relawans', function (Blueprint $table) {
            $table->dropForeign(['posisi_relawan_id']);
            $table->dropColumn([
                'deleted_at',
                'nik',
                'nama_lengkap',
                'posisi_relawan_id',
                'jenis_kelamin',
                'no_hp',
                'email',
                'alamat',
                'tanggal_lahir',
                'tanggal_bergabung',
                'foto',
                'gaji_pokok',
                'status',
                'keterangan',
            ]);
        });
    }
};
