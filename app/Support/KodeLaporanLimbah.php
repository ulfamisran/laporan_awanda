<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class KodeLaporanLimbah
{
    public static function generate(): string
    {
        return DB::transaction(function (): string {
            $date = now()->format('Ymd');
            $pattern = 'LB-'.$date.'-%';

            $last = DB::table('laporan_limbah')
                ->where('kode_laporan', 'like', $pattern)
                ->lockForUpdate()
                ->orderByDesc('kode_laporan')
                ->value('kode_laporan');

            $next = 1;
            if ($last && preg_match('/-(\d{3})$/', (string) $last, $m)) {
                $next = (int) $m[1] + 1;
            }

            return 'LB-'.$date.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }

    public static function preview(): string
    {
        $date = now()->format('Ymd');
        $pattern = 'LB-'.$date.'-%';

        $last = DB::table('laporan_limbah')
            ->where('kode_laporan', 'like', $pattern)
            ->orderByDesc('kode_laporan')
            ->value('kode_laporan');

        $next = 1;
        if ($last && preg_match('/-(\d{3})$/', (string) $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return 'LB-'.$date.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
