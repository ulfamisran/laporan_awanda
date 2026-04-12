<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class KodeTransaksiKeuangan
{
    /**
     * @param  'DM'|'DK'  $prefix
     */
    public static function generate(string $prefix, string $table): string
    {
        if (! in_array($prefix, ['DM', 'DK'], true)) {
            throw new \InvalidArgumentException('Prefix tidak valid.');
        }

        return DB::transaction(function () use ($prefix, $table): string {
            $date = now()->format('Ymd');
            $pattern = $prefix.'-'.$date.'-%';

            $last = DB::table($table)
                ->where('kode_transaksi', 'like', $pattern)
                ->lockForUpdate()
                ->orderByDesc('kode_transaksi')
                ->value('kode_transaksi');

            $next = 1;
            if ($last && preg_match('/-(\d{3})$/', (string) $last, $m)) {
                $next = (int) $m[1] + 1;
            }

            return $prefix.'-'.$date.'-'.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }
}
