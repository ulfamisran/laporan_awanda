<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait PersistsRekapFilters
{
    /**
     * @param  list<string>  $keys
     * @param  array<string, mixed>  $defaults
     * @return array<string, mixed>
     */
    protected function rekapFilters(Request $request, string $tab, array $keys, array $defaults): array
    {
        $sessionKey = 'laporan_rekap.'.$tab;
        $prev = session($sessionKey, []);

        $hasAnyQueryKey = false;
        foreach ($keys as $k) {
            if ($request->query->has($k)) {
                $hasAnyQueryKey = true;
                break;
            }
        }

        if ($hasAnyQueryKey) {
            foreach ($keys as $k) {
                if ($request->query->has($k)) {
                    $prev[$k] = $request->input($k);
                }
            }
            session([$sessionKey => $prev]);
        }

        return array_merge($defaults, session($sessionKey, []));
    }
}
