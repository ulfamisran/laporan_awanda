<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akun_dana', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 32)->unique();
            $table->string('nama', 255);
            $table->foreignId('parent_id')->nullable()->constrained('akun_dana')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('is_grup')->default(false);
            $table->timestamps();
        });

        Schema::create('stok_dana_awal_akun', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stok_dana_awal_id')->constrained('stok_dana_awal')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('akun_dana_id')->constrained('akun_dana')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('saldo_awal', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['stok_dana_awal_id', 'akun_dana_id']);
        });

        if (DB::table('akun_dana')->count() === 0) {
            $this->seedAkunDana();
        }

        if (Schema::hasColumn('stok_dana_awal', 'jumlah')) {
            $this->migrateJumlahKeBaris();
            Schema::table('stok_dana_awal', function (Blueprint $table) {
                $table->dropColumn('jumlah');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_dana_awal_akun');

        if (Schema::hasTable('akun_dana')) {
            Schema::dropIfExists('akun_dana');
        }

        if (! Schema::hasColumn('stok_dana_awal', 'jumlah')) {
            Schema::table('stok_dana_awal', function (Blueprint $table) {
                $table->decimal('jumlah', 15, 2)->default(0)->after('tanggal');
            });
        }
    }

    private function seedAkunDana(): void
    {
        $t = now();

        $id1000 = DB::table('akun_dana')->insertGetId([
            'kode' => '1000', 'nama' => 'BUKU KAS UMUM', 'parent_id' => null, 'urutan' => 10, 'is_grup' => true, 'created_at' => $t, 'updated_at' => $t,
        ]);
        $id1100 = DB::table('akun_dana')->insertGetId([
            'kode' => '1100', 'nama' => 'BUKU PEMBANTU KAS', 'parent_id' => $id1000, 'urutan' => 20, 'is_grup' => true, 'created_at' => $t, 'updated_at' => $t,
        ]);
        DB::table('akun_dana')->insert([
            ['kode' => '1101', 'nama' => 'Petty Cash / Kas kecil', 'parent_id' => $id1100, 'urutan' => 30, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
            ['kode' => '1102', 'nama' => 'Kas di Bank', 'parent_id' => $id1100, 'urutan' => 40, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
        ]);
        $id2000 = DB::table('akun_dana')->insertGetId([
            'kode' => '2000', 'nama' => 'BUKU PEMBANTU JENIS DANA', 'parent_id' => $id1000, 'urutan' => 50, 'is_grup' => true, 'created_at' => $t, 'updated_at' => $t,
        ]);
        DB::table('akun_dana')->insert([
            ['kode' => '2110', 'nama' => 'Dana Bantuan Pemerintah', 'parent_id' => $id2000, 'urutan' => 60, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
            ['kode' => '2120', 'nama' => 'Biaya Bahan Baku', 'parent_id' => $id2000, 'urutan' => 70, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
            ['kode' => '2130', 'nama' => 'Biaya Operasional', 'parent_id' => $id2000, 'urutan' => 80, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
            ['kode' => '2140', 'nama' => 'Biaya Insentif Fasilitas', 'parent_id' => $id2000, 'urutan' => 90, 'is_grup' => false, 'created_at' => $t, 'updated_at' => $t],
        ]);
    }

    private function migrateJumlahKeBaris(): void
    {
        $akun1102 = DB::table('akun_dana')->where('kode', '1102')->value('id');
        if (! $akun1102) {
            return;
        }

        $rows = DB::table('stok_dana_awal')->select('id', 'jumlah')->get();
        foreach ($rows as $row) {
            $j = (float) $row->jumlah;
            if ($j <= 0) {
                continue;
            }
            DB::table('stok_dana_awal_akun')->insert([
                'stok_dana_awal_id' => $row->id,
                'akun_dana_id' => $akun1102,
                'saldo_awal' => $j,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
