<?php

namespace Database\Seeders;

use App\Enums\StatusPenggajian;
use App\Models\Penggajian;
use App\Models\PosisiRelawan;
use App\Models\ProfilMbg;
use App\Models\Relawan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RelawanSeeder extends Seeder
{
    public function run(): void
    {
        $posisis = PosisiRelawan::query()->orderBy('id')->get();
        if ($posisis->isEmpty()) {
            return;
        }

        $userId = User::query()->orderBy('id')->value('id');
        if (! $userId) {
            return;
        }

        $namaDepan = ['Ahmad', 'Siti', 'Budi', 'Dewi', 'Rizki', 'Maya', 'Andi', 'Lina', 'Eko', 'Rina', 'Hadi', 'Nina', 'Yoga', 'Putri', 'Tono'];
        $nikBase = 3201010000000000;
        $i = 0;

        foreach (ProfilMbg::query()->orderBy('id')->get() as $dapur) {
            for ($n = 0; $n < 5; $n++) {
                $pos = $posisis[$n % $posisis->count()];
                $nik = (string) ($nikBase + $i);
                $i++;

                $relawan = Relawan::query()->firstOrCreate(
                    ['nik' => $nik],
                    [
                        'nama_lengkap' => $namaDepan[$i % count($namaDepan)].' Relawan '.$dapur->kode_dapur,
                        'posisi_relawan_id' => $pos->getKey(),
                        'profil_mbg_id' => $dapur->getKey(),
                        'jenis_kelamin' => $n % 2 === 0 ? 'L' : 'P',
                        'no_hp' => '08'.str_pad((string) (8120000000 + $i), 11, '0', STR_PAD_LEFT),
                        'email' => 'relawan'.$i.'@contoh.test',
                        'alamat' => 'Jl. Contoh No. '.$i.', '.$dapur->kota,
                        'tanggal_lahir' => Carbon::now()->subYears(22 + $n)->subMonths(3),
                        'tanggal_bergabung' => Carbon::now()->subMonths(6 + $n),
                        'gaji_pokok' => 2500000 + ($n * 150000),
                        'status' => $n === 4 ? 'cuti' : 'aktif',
                        'keterangan' => null,
                    ]
                );

                for ($m = 0; $m < 6; $m++) {
                    $c = Carbon::now()->subMonths($m)->startOfMonth();
                    Penggajian::query()->updateOrCreate(
                        [
                            'relawan_id' => $relawan->getKey(),
                            'periode_bulan' => $c->month,
                            'periode_tahun' => $c->year,
                        ],
                        [
                            'profil_mbg_id' => $dapur->getKey(),
                            'gaji_pokok' => $relawan->gaji_pokok,
                            'tunjangan_transport' => 0,
                            'tunjangan_makan' => 0,
                            'tunjangan_lainnya' => 0,
                            'potongan' => 0,
                            'keterangan_potongan' => null,
                            'tanggal_bayar' => $c->copy()->endOfMonth()->toDateString(),
                            'status' => StatusPenggajian::Dibayar,
                            'catatan' => 'Contoh penggajian seeder',
                            'created_by' => $userId,
                            'approved_by' => null,
                        ]
                    );
                }
            }
        }
    }
}
