<?php

use App\Http\Controllers\AkunDanaController;
use App\Http\Controllers\ArusStokController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\DanaKeluarController;
use App\Http\Controllers\DanaMasukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriBarangController;
use App\Http\Controllers\KategoriDanaKeluarController;
use App\Http\Controllers\KategoriDanaMasukController;
use App\Http\Controllers\KategoriLimbahController;
use App\Http\Controllers\KeuanganBukuKasUmumController;
use App\Http\Controllers\KeuanganSaldoApiController;
use App\Http\Controllers\KeuanganTransaksiController;
use App\Http\Controllers\LaporanKeuanganController;
use App\Http\Controllers\LaporanLimbahController;
use App\Http\Controllers\LaporanRekapController;
use App\Http\Controllers\MutasiStokController;
use App\Http\Controllers\PenggajianController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\PosisiRelawanController;
use App\Http\Controllers\ProfilMbgController;
use App\Http\Controllers\RelawanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StokAwalBarangController;
use App\Http\Controllers\StokBarangApiController;
use App\Http\Controllers\StokDanaAwalController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\ResolvePeriode;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
});

Route::post('/keluar', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'dapur.role:super_admin,admin_pusat,admin', ResolvePeriode::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

    Route::prefix('periode')->name('periode.')->group(function () {
        Route::get('/', [PeriodeController::class, 'index'])->name('index');
        Route::get('/create', [PeriodeController::class, 'create'])->name('create');
        Route::post('/pilih', [PeriodeController::class, 'pilih'])->name('pilih');
        Route::post('/', [PeriodeController::class, 'store'])->name('store');
        Route::get('/{periode}/edit', [PeriodeController::class, 'edit'])->name('edit');
        Route::put('/{periode}', [PeriodeController::class, 'update'])->name('update');
        Route::delete('/{periode}', [PeriodeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('master')->name('master.')->group(function () {
        Route::middleware('dapur.role:super_admin')->group(function () {
            Route::get('pengguna/data', [UserController::class, 'data'])->name('pengguna.data');
            Route::post('pengguna/{user}/reset-password', [UserController::class, 'resetPassword'])->name('pengguna.reset-password');
            Route::resource('pengguna', UserController::class)->parameters(['pengguna' => 'user'])->except(['show']);

            Route::get('profil-mbg', [ProfilMbgController::class, 'index'])->name('profil-mbg.index');
            Route::get('profil-mbg/edit', [ProfilMbgController::class, 'edit'])->name('profil-mbg.edit');
            Route::put('profil-mbg', [ProfilMbgController::class, 'update'])->name('profil-mbg.update');

            Route::get('peran', [RoleController::class, 'index'])->name('peran.index');
        });

        Route::get('kategori-barang', [KategoriBarangController::class, 'index'])->name('kategori-barang.index');
        Route::middleware('dapur.role:super_admin,admin_pusat')->group(function () {
            Route::get('kategori-barang/create', [KategoriBarangController::class, 'create'])->name('kategori-barang.create');
            Route::post('kategori-barang', [KategoriBarangController::class, 'store'])->name('kategori-barang.store');
            Route::get('kategori-barang/{kategori_barang}/edit', [KategoriBarangController::class, 'edit'])->name('kategori-barang.edit');
            Route::put('kategori-barang/{kategori_barang}', [KategoriBarangController::class, 'update'])->name('kategori-barang.update');
            Route::delete('kategori-barang/{kategori_barang}', [KategoriBarangController::class, 'destroy'])->name('kategori-barang.destroy');
        });

        Route::get('kategori-limbah', [KategoriLimbahController::class, 'index'])->name('kategori-limbah.index');
        Route::middleware('dapur.role:super_admin,admin_pusat')->group(function () {
            Route::get('kategori-limbah/create', [KategoriLimbahController::class, 'create'])->name('kategori-limbah.create');
            Route::post('kategori-limbah', [KategoriLimbahController::class, 'store'])->name('kategori-limbah.store');
            Route::get('kategori-limbah/{kategori_limbah}/edit', [KategoriLimbahController::class, 'edit'])->name('kategori-limbah.edit');
            Route::put('kategori-limbah/{kategori_limbah}', [KategoriLimbahController::class, 'update'])->name('kategori-limbah.update');
            Route::delete('kategori-limbah/{kategori_limbah}', [KategoriLimbahController::class, 'destroy'])->name('kategori-limbah.destroy');
        });

        Route::get('kategori-dana-masuk', [KategoriDanaMasukController::class, 'index'])->name('kategori-dana-masuk.index');
        Route::middleware('dapur.role:super_admin,admin_pusat')->group(function () {
            Route::get('kategori-dana-masuk/create', [KategoriDanaMasukController::class, 'create'])->name('kategori-dana-masuk.create');
            Route::post('kategori-dana-masuk', [KategoriDanaMasukController::class, 'store'])->name('kategori-dana-masuk.store');
            Route::get('kategori-dana-masuk/{kategori_dana_masuk}/edit', [KategoriDanaMasukController::class, 'edit'])->name('kategori-dana-masuk.edit');
            Route::put('kategori-dana-masuk/{kategori_dana_masuk}', [KategoriDanaMasukController::class, 'update'])->name('kategori-dana-masuk.update');
            Route::delete('kategori-dana-masuk/{kategori_dana_masuk}', [KategoriDanaMasukController::class, 'destroy'])->name('kategori-dana-masuk.destroy');
        });

        Route::get('kategori-dana-keluar', [KategoriDanaKeluarController::class, 'index'])->name('kategori-dana-keluar.index');
        Route::middleware('dapur.role:super_admin,admin_pusat')->group(function () {
            Route::get('kategori-dana-keluar/create', [KategoriDanaKeluarController::class, 'create'])->name('kategori-dana-keluar.create');
            Route::post('kategori-dana-keluar', [KategoriDanaKeluarController::class, 'store'])->name('kategori-dana-keluar.store');
            Route::get('kategori-dana-keluar/{kategori_dana_keluar}/edit', [KategoriDanaKeluarController::class, 'edit'])->name('kategori-dana-keluar.edit');
            Route::put('kategori-dana-keluar/{kategori_dana_keluar}', [KategoriDanaKeluarController::class, 'update'])->name('kategori-dana-keluar.update');
            Route::delete('kategori-dana-keluar/{kategori_dana_keluar}', [KategoriDanaKeluarController::class, 'destroy'])->name('kategori-dana-keluar.destroy');
        });

        Route::get('akun-dana', [AkunDanaController::class, 'index'])->name('akun-dana.index');
        Route::middleware('dapur.role:super_admin,admin_pusat')->group(function () {
            Route::get('akun-dana/create', [AkunDanaController::class, 'create'])->name('akun-dana.create');
            Route::post('akun-dana', [AkunDanaController::class, 'store'])->name('akun-dana.store');
            Route::get('akun-dana/{akun_dana}/edit', [AkunDanaController::class, 'edit'])->name('akun-dana.edit');
            Route::put('akun-dana/{akun_dana}', [AkunDanaController::class, 'update'])->name('akun-dana.update');
            Route::delete('akun-dana/{akun_dana}', [AkunDanaController::class, 'destroy'])->name('akun-dana.destroy');
        });

        Route::get('barang/data', [BarangController::class, 'data'])->name('barang.data');
        Route::get('barang', [BarangController::class, 'index'])->name('barang.index');

        Route::middleware('dapur.role:super_admin,admin_pusat,admin')->group(function () {
            Route::get('barang/create', [BarangController::class, 'create'])->name('barang.create');
            Route::post('barang', [BarangController::class, 'store'])->name('barang.store');
            Route::get('barang/{barang}/edit', [BarangController::class, 'edit'])->name('barang.edit');
            Route::put('barang/{barang}', [BarangController::class, 'update'])->name('barang.update');
        });

        Route::get('barang/{barang}', [BarangController::class, 'show'])->name('barang.show');

        Route::middleware('dapur.role:super_admin,admin_pusat')->group(function () {
            Route::delete('barang/{barang}', [BarangController::class, 'destroy'])->name('barang.destroy');
        });

        Route::get('posisi-relawan', [PosisiRelawanController::class, 'index'])->name('posisi-relawan.index');
        Route::middleware('dapur.role:super_admin,admin_pusat')->group(function () {
            Route::get('posisi-relawan/create', [PosisiRelawanController::class, 'create'])->name('posisi-relawan.create');
            Route::post('posisi-relawan', [PosisiRelawanController::class, 'store'])->name('posisi-relawan.store');
            Route::get('posisi-relawan/{posisi_relawan}/edit', [PosisiRelawanController::class, 'edit'])->name('posisi-relawan.edit');
            Route::put('posisi-relawan/{posisi_relawan}', [PosisiRelawanController::class, 'update'])->name('posisi-relawan.update');
            Route::delete('posisi-relawan/{posisi_relawan}', [PosisiRelawanController::class, 'destroy'])->name('posisi-relawan.destroy');
        });

        Route::get('relawan/data', [RelawanController::class, 'data'])->name('relawan.data');
        Route::get('relawan/export-excel', [RelawanController::class, 'exportExcel'])->name('relawan.export-excel');
        Route::get('relawan', [RelawanController::class, 'index'])->name('relawan.index');
        Route::get('relawan/create', [RelawanController::class, 'create'])->name('relawan.create');
        Route::post('relawan', [RelawanController::class, 'store'])->name('relawan.store');
        Route::get('relawan/{relawan}/cetak-kartu', [RelawanController::class, 'cetakKartu'])->name('relawan.cetak-kartu');
        Route::get('relawan/{relawan}', [RelawanController::class, 'show'])->name('relawan.show');
        Route::get('relawan/{relawan}/edit', [RelawanController::class, 'edit'])->name('relawan.edit');
        Route::put('relawan/{relawan}', [RelawanController::class, 'update'])->name('relawan.update');
        Route::delete('relawan/{relawan}', [RelawanController::class, 'destroy'])->name('relawan.destroy');
    });

    Route::prefix('stok')->name('stok.')->group(function () {
        Route::get('api/barang/{barang}', [StokBarangApiController::class, 'show'])->name('api.barang');

        Route::get('awal', [StokAwalBarangController::class, 'index'])->name('awal.index');
        Route::post('awal/generate-dari-periode-sebelumnya', [StokAwalBarangController::class, 'generateFromPeriodeSebelumnya'])->name('awal.generate-prev');
        Route::get('awal/create', [StokAwalBarangController::class, 'create'])->name('awal.create');
        Route::post('awal', [StokAwalBarangController::class, 'store'])->name('awal.store');
        Route::get('awal/batch', [StokAwalBarangController::class, 'batch'])->name('awal.batch');
        Route::post('awal/batch', [StokAwalBarangController::class, 'batchStore'])->name('awal.batch.store');
        Route::get('awal/{stok_awal_barang}/edit', [StokAwalBarangController::class, 'edit'])->name('awal.edit');
        Route::put('awal/{stok_awal_barang}', [StokAwalBarangController::class, 'update'])->name('awal.update');
        Route::delete('awal/{stok_awal_barang}', [StokAwalBarangController::class, 'destroy'])->name('awal.destroy');

        Route::get('masuk/data', [BarangMasukController::class, 'data'])->name('masuk.data');
        Route::get('masuk/export-pdf', [BarangMasukController::class, 'exportPdf'])->name('masuk.export-pdf');
        Route::get('masuk/export-word', [BarangMasukController::class, 'exportWord'])->name('masuk.export-word');
        Route::get('masuk', [BarangMasukController::class, 'index'])->name('masuk.index');
        Route::get('masuk/create', [BarangMasukController::class, 'create'])->name('masuk.create');
        Route::post('masuk', [BarangMasukController::class, 'store'])->name('masuk.store');
        Route::get('masuk/{masuk}', [BarangMasukController::class, 'show'])->name('masuk.show');
        Route::get('masuk/{masuk}/edit', [BarangMasukController::class, 'edit'])->name('masuk.edit');
        Route::put('masuk/{masuk}', [BarangMasukController::class, 'update'])->name('masuk.update');
        Route::delete('masuk/{masuk}', [BarangMasukController::class, 'destroy'])->name('masuk.destroy');

        Route::get('keluar/data', [BarangKeluarController::class, 'data'])->name('keluar.data');
        Route::get('keluar', [BarangKeluarController::class, 'index'])->name('keluar.index');
        Route::get('keluar/create', [BarangKeluarController::class, 'create'])->name('keluar.create');
        Route::post('keluar', [BarangKeluarController::class, 'store'])->name('keluar.store');
        Route::get('keluar/{keluar}', [BarangKeluarController::class, 'show'])->name('keluar.show');
        Route::get('keluar/{keluar}/edit', [BarangKeluarController::class, 'edit'])->name('keluar.edit');
        Route::put('keluar/{keluar}', [BarangKeluarController::class, 'update'])->name('keluar.update');
        Route::delete('keluar/{keluar}', [BarangKeluarController::class, 'destroy'])->name('keluar.destroy');

        Route::get('mutasi', [MutasiStokController::class, 'index'])->name('mutasi.index');
        Route::get('mutasi/export-excel', [MutasiStokController::class, 'exportExcel'])->name('mutasi.export-excel');
        Route::get('mutasi/export-pdf', [MutasiStokController::class, 'exportPdf'])->name('mutasi.export-pdf');
        Route::get('mutasi/{barang}/detail', [MutasiStokController::class, 'detail'])->name('mutasi.detail');

        Route::get('arus', [ArusStokController::class, 'index'])->name('arus.index');
        Route::get('arus/export-excel', [ArusStokController::class, 'exportExcel'])->name('arus.export-excel');
        Route::get('arus/export-pdf', [ArusStokController::class, 'exportPdf'])->name('arus.export-pdf');
    });

    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        Route::get('api/saldo', [KeuanganSaldoApiController::class, 'show'])->name('api.saldo');

        Route::get('stok-dana-awal', [StokDanaAwalController::class, 'index'])->name('stok-dana-awal.index');
        Route::post('stok-dana-awal', [StokDanaAwalController::class, 'store'])->name('stok-dana-awal.store');
        Route::put('stok-dana-awal/{stok_dana_awal}', [StokDanaAwalController::class, 'update'])->name('stok-dana-awal.update');

        Route::get('dana-masuk/data', [DanaMasukController::class, 'data'])->name('masuk.data');
        Route::get('dana-masuk', [DanaMasukController::class, 'index'])->name('masuk.index');
        Route::get('dana-masuk/create', [DanaMasukController::class, 'create'])->name('masuk.create');
        Route::post('dana-masuk', [DanaMasukController::class, 'store'])->name('masuk.store');
        Route::get('dana-masuk/{masuk}', [DanaMasukController::class, 'show'])->name('masuk.show');
        Route::get('dana-masuk/{masuk}/bukti-pdf', [DanaMasukController::class, 'buktiPdf'])->name('masuk.bukti-pdf');
        Route::get('dana-masuk/{masuk}/edit', [DanaMasukController::class, 'edit'])->name('masuk.edit');
        Route::put('dana-masuk/{masuk}', [DanaMasukController::class, 'update'])->name('masuk.update');
        Route::delete('dana-masuk/{masuk}', [DanaMasukController::class, 'destroy'])->middleware('dapur.role:super_admin')->name('masuk.destroy');

        Route::get('dana-keluar/data', [DanaKeluarController::class, 'data'])->name('keluar.data');
        Route::get('dana-keluar', [DanaKeluarController::class, 'index'])->name('keluar.index');
        Route::get('dana-keluar/create', [DanaKeluarController::class, 'create'])->name('keluar.create');
        Route::post('dana-keluar', [DanaKeluarController::class, 'store'])->name('keluar.store');
        Route::get('dana-keluar/{keluar}', [DanaKeluarController::class, 'show'])->name('keluar.show');
        Route::get('dana-keluar/{keluar}/bukti-pdf', [DanaKeluarController::class, 'buktiPdf'])->name('keluar.bukti-pdf');
        Route::get('dana-keluar/{keluar}/edit', [DanaKeluarController::class, 'edit'])->name('keluar.edit');
        Route::put('dana-keluar/{keluar}', [DanaKeluarController::class, 'update'])->name('keluar.update');
        Route::delete('dana-keluar/{keluar}', [DanaKeluarController::class, 'destroy'])->middleware('dapur.role:super_admin')->name('keluar.destroy');

        Route::get('transaksi/data', [KeuanganTransaksiController::class, 'data'])->name('transaksi.data');
        Route::get('transaksi', [KeuanganTransaksiController::class, 'index'])->name('transaksi.index');

        Route::get('buku-kas-umum', [KeuanganBukuKasUmumController::class, 'index'])->name('buku-kas-umum.index');

        Route::get('laporan', [LaporanKeuanganController::class, 'index'])->name('laporan.index');
        Route::get('laporan/neraca', [LaporanKeuanganController::class, 'neraca'])->name('laporan.neraca');
        Route::get('laporan/neraca/export-excel', [LaporanKeuanganController::class, 'exportNeracaExcel'])->name('laporan.neraca.export-excel');
        Route::get('laporan/neraca/export-pdf', [LaporanKeuanganController::class, 'exportNeracaPdf'])->name('laporan.neraca.export-pdf');
    });

    Route::prefix('penggajian')->name('penggajian.')->group(function () {
        Route::get('/', [PenggajianController::class, 'index'])->name('index');
        Route::get('/create', [PenggajianController::class, 'create'])->name('create');
        Route::post('/generate-bulk', [PenggajianController::class, 'generateBulk'])->name('generate-bulk');
        Route::post('/', [PenggajianController::class, 'store'])->name('store');
        Route::get('/export-excel', [PenggajianController::class, 'exportExcel'])->name('export-excel');
        Route::get('/cetak-rekap', [PenggajianController::class, 'cetakRekap'])->name('cetak-rekap');
        Route::get('/{penggajian}/slip-pdf', [PenggajianController::class, 'cetakSlip'])->name('slip-pdf');
        Route::post('/{penggajian}/approve', [PenggajianController::class, 'approve'])->name('approve');
        Route::post('/{penggajian}/bayar', [PenggajianController::class, 'bayar'])->name('bayar');
        Route::get('/{penggajian}/edit', [PenggajianController::class, 'edit'])->name('edit');
        Route::put('/{penggajian}', [PenggajianController::class, 'update'])->name('update');
        Route::delete('/{penggajian}', [PenggajianController::class, 'destroy'])->name('destroy');
        Route::get('/{penggajian}', [PenggajianController::class, 'show'])->name('show');
    });
    Route::prefix('laporan-limbah')->name('laporan-limbah.')->group(function () {
        Route::get('/', [LaporanLimbahController::class, 'index'])->name('index');
        Route::get('/data', [LaporanLimbahController::class, 'data'])->name('data');
        Route::get('/export-excel', [LaporanLimbahController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [LaporanLimbahController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/rekapitulasi', [LaporanLimbahController::class, 'rekapitulasi'])->name('rekapitulasi');
        Route::get('/create', [LaporanLimbahController::class, 'create'])->name('create');
        Route::post('/', [LaporanLimbahController::class, 'store'])->name('store');
        Route::get('/harian/{harian}/edit', [LaporanLimbahController::class, 'edit'])->name('harian.edit');
        Route::put('/harian/{harian}', [LaporanLimbahController::class, 'update'])->name('harian.update');
        Route::delete('/harian/{harian}', [LaporanLimbahController::class, 'destroy'])->name('harian.destroy');
        Route::get('/harian/{harian}', [LaporanLimbahController::class, 'show'])->name('harian.show');
    });
    Route::prefix('laporan/rekap')->name('laporan-rekap.')->group(function () {
        Route::get('/', [LaporanRekapController::class, 'redirectIndex'])->name('index');
        Route::get('/stok', [LaporanRekapController::class, 'stokBarang'])->name('stok');
        Route::get('/stok/export-excel', [LaporanRekapController::class, 'exportStokExcel'])->name('stok.export-excel');
        Route::get('/stok/export-pdf', [LaporanRekapController::class, 'exportStokPdf'])->name('stok.export-pdf');
        Route::get('/arus', [LaporanRekapController::class, 'arusStok'])->name('arus');
        Route::get('/arus/export-excel', [LaporanRekapController::class, 'exportArusExcel'])->name('arus.export-excel');
        Route::get('/arus/export-pdf', [LaporanRekapController::class, 'exportArusPdf'])->name('arus.export-pdf');
        Route::get('/keuangan', [LaporanRekapController::class, 'keuangan'])->name('keuangan');
        Route::get('/keuangan/export-excel', [LaporanRekapController::class, 'exportKeuanganExcel'])->name('keuangan.export-excel');
        Route::get('/keuangan/export-pdf', [LaporanRekapController::class, 'exportKeuanganPdf'])->name('keuangan.export-pdf');
        Route::get('/penggajian', [LaporanRekapController::class, 'penggajian'])->name('penggajian');
        Route::get('/penggajian/export-excel', [LaporanRekapController::class, 'exportPenggajianExcel'])->name('penggajian.export-excel');
        Route::get('/penggajian/export-pdf', [LaporanRekapController::class, 'exportPenggajianPdf'])->name('penggajian.export-pdf');
        Route::get('/limbah', [LaporanRekapController::class, 'limbah'])->name('limbah');
        Route::get('/limbah/export-excel', [LaporanRekapController::class, 'exportLimbahExcel'])->name('limbah.export-excel');
        Route::get('/limbah/export-pdf', [LaporanRekapController::class, 'exportLimbahPdf'])->name('limbah.export-pdf');
        Route::get('/komprehensif', [LaporanRekapController::class, 'komprehensif'])->name('komprehensif');
        Route::get('/komprehensif/export-pdf', [LaporanRekapController::class, 'exportKomprehensifPdf'])->name('komprehensif.export-pdf');
    });
});

Route::middleware(['auth', 'dapur.role:super_admin'])->prefix('pengaturan')->name('pengaturan.')->group(function () {
    Route::view('/', 'pages.shell', ['title' => 'Pengaturan Sistem'])->name('index');
});
