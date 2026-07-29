<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\Account;
use App\Models\Tenant\Usage;
use Carbon\Carbon;
use App\Models\Tenant\AkunLevel1;
use App\Models\Tenant\Amount;
use App\Models\Tenant\Business;
use App\Models\Tenant\Calk;
use App\Models\Tenant\Village;
use App\Models\Tenant\Installations;
use App\Models\Tenant\Inventory;
use App\Models\Tenant\JenisLaporan;
use App\Models\Tenant\JenisLaporanPinjaman;
use App\Models\Tenant\MasterArusKas;
use App\Models\Tenant\Settings;
use App\Models\Tenant\Transaction;
use App\Models\Tenant\User;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;

class PelaporanController extends Controller
{

    public function index()
    {
        $busines = Business::where('id', Session::get('business_id'))->first();
        $laporan = JenisLaporan::where([['file', '!=', '0']])->orderBy('urut', 'ASC')->get();

        $title = 'Pelaporan';
        return view('pelaporan.index')->with(compact('title', 'laporan', 'busines'));
    }
    public function subLaporan($file)
    {
        $sub_laporan = [
            0 => [
                'value' => '',
                'title' => 'Pilih Sub Laporan'
            ]
        ];

        if ($file == 'buku_besar') {
            $accounts = Account::where('kode_akun', '!=', '3.2.02.01')->where('business_id', Session::get('business_id'))->get();
            foreach ($accounts as $acc) {
                $sub_laporan[] = [
                    'value' => $acc->kode_akun,
                    'title' => $acc->kode_akun . '. ' . $acc->nama_akun
                ];
            }
        }

        if ($file == 'e_budgeting') {
            $sub_laporan = [
                0 => [
                    'title' => 'Pilih Sub Laporan',
                    'value' => ''
                ],
                1 => [
                    'title' => '01. Januari - Maret',
                    'value' => '1,2,3'
                ],
                2 => [
                    'title' => '02. April - Juni',
                    'value' => '4,5,6'
                ],
                3 => [
                    'title' => '03. Juli - September',
                    'value' => '7,8,9'
                ],
                4 => [
                    'title' => '04. Oktober - Desember',
                    'value' => '10,11,12'
                ]
            ];
        }

        if ($file == 'tutup_buku') {
            $sub_laporan = [
                0 => [
                    'title' => 'Pengalokasian Laba',
                    'value' => 'alokasi_laba'
                ],
                1 => [
                    'title' => 'Jurnal Tutup Buku',
                    'value' => 'jurnal_tutup_buku'
                ],
                2 => [
                    'title' => 'Neraca',
                    'value' => 'neraca_tutup_buku'
                ],
                3 => [
                    'title' => 'Laba Rugi',
                    'value' => 'laba_rugi_tutup_buku'
                ],
                4 => [
                    'title' => 'CALK',
                    'value' => 'CALK_tutup_buku'
                ]
            ];
        }

        if ($file == 'calk') {
            $tahun = request()->get('tahun');
            $bulan = request()->get('bulan');

            $calk = Calk::where([
                ['tanggal', 'LIKE', $tahun . '-' . $bulan . '%']
            ])->first();

            $keterangan = '';
            if ($calk) {
                $keterangan = $calk->catatan;
            }
        }

        if ($file == 'daftar_pelanggan') {
            $caters = User::where([
                ['business_id', Session::get('business_id')],
                ['jabatan', '1']
            ])->get();

            $sub_laporan = [
                0 => [
                    'value' => '',
                    'title' => 'Pilih Marketing'
                ]
            ];

            foreach ($caters as $ct) {
                $sub_laporan[] = [
                    'value' => $ct->id,
                    'title' => $ct->nama
                ];
            }
        }

        if ($file == 'piutang_pelanggan') {
            $caters = User::where([
                ['business_id', Session::get('business_id')],
                ['jabatan', '1']
            ])->get();

            $sub_laporan = [
                0 => [
                    'value' => '',
                    'title' => 'Pilih Marketing'
                ]
            ];

            foreach ($caters as $ct) {
                $sub_laporan[] = [
                    'value' => $ct->id,
                    'title' => $ct->nama
                ];
            }
        }
        if ($file == 'tagihan_pelanggan') {
            $caters = User::where([
                ['business_id', Session::get('business_id')],
                ['jabatan', '1']
            ])->get();

            $sub_laporan = [
                0 => [
                    'value' => '',
                    'title' => 'Pilih Marketing'
                ]
            ];

            foreach ($caters as $ct) {
                $sub_laporan[] = [
                    'value' => $ct->id,
                    'title' => $ct->nama
                ];
            }
        }

        return view('pelaporan.partials.sub_laporan')->with(compact('sub_laporan'));
    }

    public function preview(Request $request, $business_id = null)
    {
        $data = [
            'tahun' => $request->get('tahun'),
            'bulan' => $request->get('bulan'),
            'hari' => $request->get('hari'),
            'laporan' => $request->get('laporan'),
            'sub_laporan' => $request->get('sub_laporan'),
            'type' => $request->get('type'),
        ];

        $busines = Business::where('id', Session::get('business_id'))->first();
        $direktur = User::where([
            ['jabatan', '1'],
            ['business_id', Session::get('business_id')]
        ])->with(['position'])->first();

        if ($data['tahun'] == null) {
            abort(404);
        }

        $data['bulanan'] = true;
        if ($data['bulan'] == null) {
            $data['bulanan'] = false;
            $data['bulan'] = '12';
        }

        $data['harian'] = true;
        if ($data['hari'] == null) {
            $data['harian'] = false;
            $data['hari'] = date('t', strtotime($data['tahun'] . '-' . $data['bulan'] . '-01'));
        }

        $data['tgl_kondisi'] = $data['tahun'] . '-' . $data['bulan'] . '-' . $data['hari'];
        $laporan = $request->get('laporan');
        if ($laporan == 'tutup_buku') {
            $laporan = $request->get('sub_laporan');
            $data['laporan'] = $laporan;
        }

        if ($laporan == 'daftar_pelanggan' || $laporan == 'piutang_pelanggan' || $laporan == 'tagihan_pelanggan') {
            $data['cater'] = $request->get('sub_laporan');
        }

        $logoPath = public_path('storage/logo/' . $busines->logo);
        $data['logo'] = (is_file($logoPath) ? 'file:///' . str_replace('\\', '/', $logoPath) : null);

        $data['nomor_usaha'] = 'SK Kemenkumham RI No.' . $busines->nomor_bh;
        $data['info'] = $busines->alamat . ', Telp.' . $busines->telpon;
        $data['email'] = $busines->email;
        $data['nama'] = $busines->nama;
        $data['alamat'] = $busines->alamat;
        $data['jabatan'] = $direktur->positions;
        $data['direktur'] = $direktur;

        return $this->$laporan($data);
    }

    private function cover(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['title'] = 'Cover';
        $view = view('pelaporan.partials.views.cover', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'margin-top'    => 20,
            'margin-bottom' => 20,
            'margin-left'   => 25,
            'margin-right'  => 20,
            'enable-local-file-access' => true,
        ]);
        return $pdf->inline();
    }

    private function surat_pengantar(array $data)
    {
        $villages = Village::where('id', Session::get('business_id'))->first();

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        if (strlen($hari) > 0 && strlen($bln) > 0) {
            $tgl = $thn . '-' . $bln . '-' . $hari;
            $data['judul'] = 'Laporan Harian';
            $data['sub_judul'] = 'Tanggal ' . Tanggal::tglLatin($tgl);
            $data['tgl'] = Tanggal::tglLatin($tgl);
        } elseif (strlen($bln) > 0) {
            $tgl = $thn . '-' . $bln . '-' . $hari;
            $data['judul'] = 'Laporan Bulanan';
            $data['sub_judul'] = 'Tanggal ' . Tanggal::tglLatin(date('Y-m-t', strtotime($thn . '-' . $bln . '-01')));
            $data['tgl'] = Tanggal::tglLatin(date('Y-m-t', strtotime($thn . '-' . $bln . '-01')));
        } else {
            $tgl = $thn . '-' . $bln . '-' . $hari;
            $data['judul'] = 'Laporan Tahunan';
            $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::tahun($tgl);
        }

        $data['nama_desa'] = $villages->nama;
        $data['alamat_desa'] = $villages->alamat;

        $data['title'] = 'Surat Pengantar';
        $view = view('pelaporan.partials.views.surat_pengantar', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'    => 30,
            'margin-bottom' => 20,
            'margin-left'   => 25,
            'margin-right'  => 20,
            'enable-local-file-access' => true,
        ]);
        return $pdf->inline();
    }

    private function jurnal_transaksi(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];
        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['rows'] = 500;
        $transactions = Transaction::where([
            ['tgl_transaksi', 'LIKE', $data['tahun'] . '-' . $data['bulan'] . '%'],
            ['business_id', Session::get('business_id')]
        ])->with([
            'acc_debit',
            'acc_kredit',
            'transaction' => function ($query) use ($data) {
                $query->where('tgl_transaksi', 'LIKE', $data['tahun'] . '-' . $data['bulan'] . '%');
            },
            'transaction.acc_debit',
            'transaction.acc_kredit',
        ])->get();

        $nomor = 1;
        $data['transaction_id'] = [];
        $data['transactions'] = [];
        foreach ($transactions as $trx) {
            if (!empty($trx->transaction_id) && in_array($trx->transaction_id, $data['transaction_id'])) {
                continue;
            }

            $trx_debit = [
                'id'            => $trx->id,
                'nomor'         => $nomor,
                'tgl_transaksi' => $trx->tgl_transaksi,
                'kode_akun'     => optional($trx->acc_debit)->kode_akun,
                'nama_akun'     => optional($trx->acc_debit)->nama_akun,
                'jumlah'        => $trx->total ?? 0,
                'ins'           => '',
                'trx_kredit'    => []
            ];

            $trx_kredit = [];

            if (!empty($trx->transaction_id)) {
                $trx_debit['jumlah'] = 0;
                foreach ($trx->transaction ?? [] as $child) {
                    $trx_kredit[] = [
                        'kode_akun' => optional($child->acc_kredit)->kode_akun,
                        'nama_akun' => optional($child->acc_kredit)->nama_akun,
                        'jumlah'    => $child->total ?? 0,
                    ];
                    $trx_debit['jumlah'] += ($child->total ?? 0);
                }
            } else {
                $trx_kredit[] = [
                    'kode_akun' => optional($trx->acc_kredit)->kode_akun,
                    'nama_akun' => optional($trx->acc_kredit)->nama_akun,
                    'jumlah'    => $trx->total ?? 0,
                ];
            }

            $trx_debit['trx_kredit'] = $trx_kredit;
            $data['transactions'][] = $trx_debit;

            if (!empty($trx->transaction_id)) {
                $data['transaction_id'][] = $trx->transaction_id;
            }

            $nomor++;
        }

        $data['title'] = 'Jurnal Transaksi';
        $view = view('pelaporan.partials.views.jurnal_transaksi', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function buku_besar(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['kode_akun'] = $data['sub_laporan'];
        $data['account'] = Account::where([
            ['business_id', Session::get('business_id')],
            ['kode_akun', $data['kode_akun']]
        ])->first();

        $data['saldo_awal_tahun'] = Amount::where([
            ['tahun', $data['tahun']],
            ['bulan', '0'],
            ['account_id', $data['account']->id]
        ])->first();

        $data['saldo_bulan_lalu'] = Amount::where([
            ['tahun', $data['tahun']],
            ['bulan', $data['bulan'] - 1],
            ['account_id', $data['account']->id]
        ])->first();

        $data['transactions'] = Transaction::where([
            ['tgl_transaksi', 'LIKE', $thn . '-' . $bln . '%'],
            ['business_id', Session::get('business_id')]
        ])->where(function ($query) use ($data) {
            $query->where('rekening_debit', $data['account']->id)->orwhere('rekening_kredit', $data['account']->id);
        })->get();

        $data['title'] = 'Buku Besar';
        $view = view('pelaporan.partials.views.buku_besar', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function neraca_saldo(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $tanggal = Tanggal::tglLatin($tgl);
            $data['sub_judul'] = 'PER ' . $tanggal;
        }
        $tanggal = Tanggal::tglLatin($tgl);
        $data['sub_judul'] = 'PER ' . $tanggal;

        $data['accounts'] = Account::where('business_id', Session::get('business_id'))->with([
            'amount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orWhere('bulan', $data['bulan']);
                });
            }
        ])->get();

        $data['title'] = 'Neraca Saldo';
        $view = view('pelaporan.partials.views.neraca_saldo', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ])->setOrientation('landscape');
        return $pdf->inline();
    }

    private function neraca(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $tanggal = Tanggal::tglLatin($tgl);
            $data['sub_judul'] = 'PER ' . $tanggal;
        }
        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.accounts' => function ($query) {
                $query->where('business_id', Session::get('business_id'));
            },
            'akun2.akun3.accounts.amount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $laba_rugi = Account::where('business_id', Session::get('business_id'))->where('lev1', '>=', '4')->with([
            'amount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            },
        ])->get();

        $pendapatan = 0;
        $beban = 0;
        foreach ($laba_rugi as $lr) {
            $saldo = $keuangan->komSaldo($lr);
            if ($lr->lev1 == '4') {
                $pendapatan += $saldo;
            }

            if ($lr->lev1 == '5') {
                $beban += $saldo;
            }
        }

        $data['surplus'] = $pendapatan - $beban;

        $data['title'] = 'Neraca';
        $view = view('pelaporan.partials.views.neraca', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function laba_rugi(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $bulanSekarang = $bln;

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);

        if ($data['bulanan']) {
            $awal_tahun = Tanggal::tglLatin(date('Y-m-d', strtotime($thn . '-01-01')));
            $tanggal = Tanggal::tglLatin($tgl);

            $data['sub_judul'] = 'PERIODE ' . $awal_tahun . ' s.d. ' . $tanggal;
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['pendapatan'] = Account::where([
            ['kode_akun', 'LIKE', '4.1.%'],
            ['business_id', Session::get('business_id')]
        ])->with([
            'amount' => function ($query) use ($thn, $bulanSekarang) {
                $query->where('tahun', $thn)->where(function ($query) use ($bulanSekarang) {
                    // Data untuk Bulan Lalu (bulan aktif - 1)
                    $query->where('bulan', '0')->orWhere('bulan', '=', $bulanSekarang);
                });
            },
            'oneAmount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where('bulan', $data['bulan'] - 1);
            }
        ])->orderBy('kode_akun', 'ASC')->get();

        $data['beban'] = Account::where([
            ['kode_akun', 'LIKE', '5.1.%'],
            ['business_id', Session::get('business_id')]
        ])
            ->with([
                'amount' => function ($query) use ($thn, $bulanSekarang) {
                    $query->where('tahun', $thn)->where(function ($query) use ($bulanSekarang) {
                        // Data untuk Bulan Lalu (bulan aktif - 1)
                        $query->where('bulan', '0')->orWhere('bulan', '=', $bulanSekarang);
                    });
                },
                'oneAmount' => function ($query) use ($data) {
                    $query->where('tahun', $data['tahun'])->where('bulan', $data['bulan'] - 1);
                }
            ])->orderBy('kode_akun', 'ASC')->get();

        $data['pen'] = Account::where([
            ['kode_akun', 'LIKE', '4.2.%'],
            ['business_id', Session::get('business_id')]
        ])->orWhere('kode_akun', 'LIKE', '4.3.%')
            ->whereNotIn('kode_akun', ['4.3.01.01', '4.3.01.02', '4.3.01.03'])
            ->with([
                'amount' => function ($query) use ($thn, $bulanSekarang) {
                    $query->where('tahun', $thn)->where(function ($query) use ($bulanSekarang) {
                        // Data untuk Bulan Lalu (bulan aktif - 1)
                        $query->where('bulan', '0')->orWhere('bulan', '=', $bulanSekarang);
                    });
                },
                'oneAmount' => function ($query) use ($data) {
                    $query->where('tahun', $data['tahun'])->where('bulan', $data['bulan'] - 1);
                }
            ])->orderBy('kode_akun', 'ASC')->get();

        $data['beb'] = Account::where([
            ['kode_akun', 'LIKE', '5.3.%'],
            ['business_id', Session::get('business_id')]
        ])
            ->orWhere('kode_akun', 'LIKE', '5.4%')
            ->where('kode_akun', '!=', '5.4.01.01') // Mengecualikan kode akun 5.4.01.01
            ->with([
                'amount' => function ($query) use ($thn, $bulanSekarang) {
                    $query->where('tahun', $thn)->where(function ($query) use ($bulanSekarang) {
                        // Data untuk Bulan Lalu (bulan aktif - 1)
                        $query->where('bulan', '0')->orWhere('bulan', '=', $bulanSekarang);
                    });
                },
                'oneAmount' => function ($query) use ($data) {
                    $query->where('tahun', $data['tahun'])->where('bulan', $data['bulan'] - 1);
                }
            ])->orderBy('kode_akun', 'ASC')->get();

        $data['ph'] = Account::where([['kode_akun', '5.4.01.01'], ['business_id', Session::get('business_id')]])->with([
            'amount' => function ($query) use ($thn, $bulanSekarang) {
                $query->where('tahun', $thn)->where(function ($query) use ($bulanSekarang) {
                    // Data untuk Bulan Lalu (bulan aktif - 1)
                    $query->where('bulan', '0')->orWhere('bulan', '=', $bulanSekarang);
                });
            },
            'oneAmount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where('bulan', $data['bulan'] - 1);
            }
        ])->orderBy('kode_akun', 'ASC')->get();

        $data['bp'] = Account::where([['kode_akun', 'LIKE', '5.2.%'],
            ['business_id', Session::get('business_id')]])->with([
            'amount' => function ($query) use ($thn, $bulanSekarang) {
                $query->where('tahun', $thn)->where(function ($query) use ($bulanSekarang) {
                    // Data untuk Bulan Lalu (bulan aktif - 1)
                    $query->where('bulan', '0')->orWhere('bulan', '=', $bulanSekarang);
                });
            },
            'oneAmount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where('bulan', $data['bulan'] - 1);
            }
        ])->orderBy('kode_akun', 'ASC')->get();

        $data['pendl'] = Account::whereIn('kode_akun', ['4.3.01.01', '4.3.01.02', '4.3.01.03'])->where([
            ['business_id', Session::get('business_id')]
        ])->with([
            'amount' => function ($query) use ($thn, $bulanSekarang) {
                $query->where('tahun', $thn)->where(function ($query) use ($bulanSekarang) {
                    $query->where('bulan', '0')->orWhere('bulan', '=', $bulanSekarang);
                });
            },
            'oneAmount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where('bulan', $data['bulan'] - 1);
            }
        ])->orderBy('kode_akun', 'ASC')->get();

        $data['title'] = 'Laba Rugi';
        $view = view('pelaporan.partials.views.laba_rugi', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function arus_kas(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['akun_kas'] = Account::where('business_id', Session::get('business_id'))->where(function ($query) {
            $query->where('kode_akun', 'like', '1.1.01%')->orWhere('kode_akun', 'like', '1.1.02%');
        })->pluck('id');

        $data['tgl_awal'] = $thn . '-' . $bln . '-01';
        $data['arus_kas'] = MasterArusKas::where('parent_id', '0')->with([
            'child',
            'child.rek_debit',
            'child.rek_debit.accounts',
            'child.rek_debit.accounts.rek_debit' => function ($query) use ($data) {
                $query->whereBetween('tgl_transaksi', [$data['tgl_awal'], $data['tgl_kondisi']])->where(function ($query) use ($data) {
                    $query->whereIn('rekening_kredit', $data['akun_kas']);
                });
            },
            'child.rek_kredit',
            'child.rek_kredit.accounts.rek_kredit' => function ($query) use ($data) {
                $query->whereBetween('tgl_transaksi', [$data['tgl_awal'], $data['tgl_kondisi']])->where(function ($query) use ($data) {
                    $query->whereIn('rekening_debit', $data['akun_kas']);
                });
            },
        ])->get();

        $data['title'] = 'Arus Kas';
        $view = view('pelaporan.partials.views.arus_kas', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function LPM(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }
        $data['accounts'] = Account::where('business_id', Session::get('business_id'))->where('lev1', '3')->with([
            'amount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orWhere('bulan', $data['bulan']);
                });
            }
        ])->get();

        $data['title'] = 'Laporan Perubahan Modal';
        $view = view('pelaporan.partials.views.laporan_perubahan_modal', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function calkk(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }
        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.accounts' => function ($query) {
                $query->where('business_id', Session::get('business_id'));
            },
            'akun2.akun3.accounts.amount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $laba_rugi = Account::where('business_id', Session::get('business_id'))->where('lev1', '>=', '4')->with([
            'amount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            },
        ])->get();

        $pendapatan = 0;
        $beban = 0;
        foreach ($laba_rugi as $lr) {
            $saldo = $keuangan->komSaldo($lr);
            if ($lr->lev1 == '4') {
                $pendapatan += $saldo;
            }

            if ($lr->lev1 == '5') {
                $beban += $saldo;
            }
        }

        $data['surplus'] = $pendapatan - $beban;

        $data['title'] = 'Catatan Atas Laporan Keuangan';
        $view = view('pelaporan.partials.views.calk', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function daftar_pelanggan(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);

        $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);

        $data['cater_id'] = $data['sub_laporan'];
        $caters = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '1']
        ])->with([
            'installations' => function ($query) use ($data) {
                $query->where('pasang', '<=', $data['tgl_kondisi'])->orderBy('desa');
            },
            'installations.customer',
            'installations.village',
            'installations.package',
            'installations.settings'
        ]);

        if (!empty($data['cater_id'])) {
            $caters->where('id', $data['cater_id']);
        }
        $data['caters'] = $caters->get();

        $data['title'] = 'Daftar Pelanggan';
        $view = view('pelaporan.partials.views.daftar_pelanggan', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
            'orientation' => 'landscape',
        ]);
        return $pdf->inline();
    }

    private function tagihan_pelanggan(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        $data['cater'] = $data['cater'] ?? request()->input('cater', null);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $akun_piutang = Account::where('business_id', Session::get('business_id'))->where('kode_akun', '1.1.03.01')->first();

        $data['cater_id'] = $data['sub_laporan'];
        $caters = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '1']
        ])->with([
            'installations' => function ($query) use ($data) {
                $query->where('aktif', '<=', $data['tgl_kondisi'])->orderBy('desa');
            },
            'installations.customer',
            'installations.village',
            'installations.package',
            'installations.settings',
            'installations.usage' => function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    $query->where('tgl_akhir', '<=', $data['tgl_kondisi'])->where('status', 'UNPAID');
                })->orderBy('tgl_akhir')->orderBy('id');
            },
            'installations.usage.transaction' => function ($query) use ($data, $akun_piutang) {
                $query->where([
                    ['tgl_transaksi', '<=', $data['tgl_kondisi']],
                    ['rekening_debit', '!=', $akun_piutang->id]
                ]);
            },
        ]);

        if (!empty($data['cater_id'])) {
            $caters->where('id', $data['cater_id']);
        }
        $data['caters'] = $caters->get();

        $data['title'] = 'Daftar Tagihan Pelanggan';
        $view = view('pelaporan.partials.views.tagihan_pelanggan', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
            'orientation' => 'landscape',
        ]);
        return $pdf->inline();
    }

    private function piutang_pelanggan(array $data)
    {

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        $data['cater'] = $data['cater'] ?? request()->input('cater', null);

        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['akun_piutang'] = Account::where('business_id', Session::get('business_id'))->where('kode_akun', '1.1.03.01')->first();

        $data['cater_id'] = $data['sub_laporan'];
        $caters = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '1']
        ])->with([
            'installations' => function ($query) use ($data) {
                $query->where('aktif', '<=', $data['tgl_kondisi'])->orderBy('desa');
            },
            'installations.customer',
            'installations.village',
            'installations.package',
            'installations.settings',
            'installations.usage' => function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    $query->where('tgl_akhir', '<=', $data['tgl_kondisi'])->where('status', 'UNPAID');
                })->orderBy('tgl_akhir')->orderBy('id');
            },
            'installations.usage.transaction' => function ($query) use ($data) {
                $query->where([
                    ['tgl_transaksi', '<=', $data['tgl_kondisi']]
                ]);
            },
        ]);

        if (!empty($data['cater_id'])) {
            $caters->where('id', $data['cater_id']);
        }
        $data['caters'] = $caters->get();
        $data['title'] = 'Daftar Piutang Pelanggan';
        $view = view('pelaporan.partials.views.piutang_pelanggan', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
            'orientation' => 'landscape',
        ]);
        return $pdf->inline();
    }

    private function ati(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }
        $data['accounts'] = Account::where([
            ['kode_akun', 'LIKE', '1.2.01%'],
            ['business_id', Session::get('business_id')]
        ])->with([
            'inventory' => function ($query) use ($data) {
                $query->where([
                    ['business_id', Session::get('business_id')],
                    ['tgl_beli', '<=', $data['tgl_kondisi']]
                ])->orderBy('kategori', 'ASC')->orderBy('tgl_beli', 'ASC');
            }
        ])->get();

        $data['title'] = 'Aset Tetap Dan Inventaris';
        $view = view('pelaporan.partials.views.aset_tetap', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
            'orientation' => 'landscape',
        ]);
        return $pdf->inline();
    }

    private function atb(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['accounts'] = Account::where([
            ['kode_akun', 'LIKE', '1.2.03%'],
            ['business_id', Session::get('business_id')]
        ])->with([
            'inventory' => function ($query) use ($data) {
                $query->where([
                    ['business_id', Session::get('business_id')],
                    ['tgl_beli', '<=', $data['tgl_kondisi']]
                ])->orderBy('kategori', 'ASC')->orderBy('tgl_beli', 'ASC');
            }
        ])->get();

        $data['title'] = 'Aset Tak Berwujud';
        $view = view('pelaporan.partials.views.aset_tak_berwujud', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
            'orientation' => 'landscape',
        ]);
        return $pdf->inline();
    }

    private function e_budgeting(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $title = [
            '1,2,3' => 'Januari - Maret',
            '4,5,6' => 'April - Juni',
            '7,8,9' => 'Juli - September',
            '10,11,12' => 'Oktober - Desember'
        ];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);

        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['thn'] = Tanggal::tahun($tgl);
        }

        $list_bulan = explode(',', $data['sub_laporan']);
        $bulan1 = $list_bulan[0];
        $bulan2 = $list_bulan[1];
        $bulan3 = $list_bulan[2];
        $bulan_komulatif = $bulan1 - 1;

        $daftar_header = [];
        $akun1 = AkunLevel1::where('lev1', '>=', '4')->with('akun2.akun3')->get();
        foreach ($akun1 as $lev1) {
            $daftar_header[$lev1->kode_akun] = $lev1->kode_akun . '. ' . $lev1->nama_akun;
            foreach ($lev1->akun2 as $lev2) {
                $daftar_header[$lev2->kode_akun] = $lev2->kode_akun . '. ' . $lev2->nama_akun;
                foreach ($lev2->akun3 as $lev3) {
                    $daftar_header[$lev3->kode_akun] = $lev3->kode_akun . '. ' . $lev3->nama_akun;
                }
            }
        }

        $bulan_tampil = [];
        foreach ($list_bulan as $key => $value) {
            $bulan_tampil[] = str_pad($value, 2, '0', STR_PAD_LEFT);
        }
        $data['bulan_tampil'] = $bulan_tampil;

        $data['query_komulatif'] = Account::where([
            ['business_id', Session::get('business_id')],
            ['lev1', '>=', 4],
            ['lev1', '<=', 5]
        ])->with([
            'amount' => function ($query) use ($thn, $bulan_komulatif) {
                $query->where([
                    ['tahun', $thn],
                    ['bulan', str_pad($bulan_komulatif, 2, '0', STR_PAD_LEFT)]
                ]);
            },
            'eb' => function ($query) use ($thn, $bulan_komulatif) {
                $query->where([
                    ['tahun', $thn],
                    ['bulan', $bulan_komulatif]
                ]);
            }
        ])->get();

        $data['query_bulan_1'] = Account::where([
            ['business_id', Session::get('business_id')],
            ['lev1', '>=', 4],
            ['lev1', '<=', 5]
        ])->with([
            'amount' => function ($query) use ($thn, $bulan1) {
                $query->where([
                    ['tahun', $thn],
                    ['bulan', str_pad($bulan1, 2, '0', STR_PAD_LEFT)]
                ]);
            },
            'eb' => function ($query) use ($thn, $bulan1) {
                $query->where([
                    ['tahun', $thn],
                    ['bulan', $bulan1]
                ]);
            }
        ])->get();

        $data['query_bulan_2'] = Account::where([
            ['business_id', Session::get('business_id')],
            ['lev1', '>=', 4],
            ['lev1', '<=', 5]
        ])->with([
            'amount' => function ($query) use ($thn, $bulan2) {
                $query->where([
                    ['tahun', $thn],
                    ['bulan', str_pad($bulan2, 2, '0', STR_PAD_LEFT)]
                ]);
            },
            'eb' => function ($query) use ($thn, $bulan2) {
                $query->where([
                    ['tahun', $thn],
                    ['bulan', $bulan2]
                ]);
            }
        ])->get();

        $data['query_bulan_3'] = Account::where([
            ['business_id', Session::get('business_id')],
            ['lev1', '>=', 4],
            ['lev1', '<=', 5]
        ])->with([
            'amount' => function ($query) use ($thn, $bulan3) {
                $query->where([
                    ['tahun', $thn],
                    ['bulan', str_pad($bulan3, 2, '0', STR_PAD_LEFT)]
                ]);
            },
            'eb' => function ($query) use ($thn, $bulan3) {
                $query->where([
                    ['tahun', $thn],
                    ['bulan', $bulan3]
                ]);
            }
        ])->get();

        $akun1 = [];
        $akun2 = [];
        $akun3 = [];
        $data_e_budgeting = [];
        foreach ($data['query_bulan_1'] as $index => $query) {
            $komulatif = $data['query_komulatif'][$index];
            $bulan1 = $data['query_bulan_1'][$index];
            $bulan2 = $data['query_bulan_2'][$index];
            $bulan3 = $data['query_bulan_3'][$index];

            $lev1 = $query->lev1 . '.0.00.00';
            $lev2 = $query->lev1 . '.' . $query->lev2 . '.00.00';
            $lev3 = $query->lev1 . '.' . $query->lev2 . '.' . str_pad($query->lev3, 2, '0', STR_PAD_LEFT) . '.00';

            if (!in_array($lev1, $akun1)) {
                $akun1[] = $lev1;
                $data_e_budgeting[] = [
                    'nama' => $daftar_header[$lev1],
                    'is_header' => true
                ];
            }

            if (!in_array($lev2, $akun2)) {
                $akun2[] = $lev2;
                $data_e_budgeting[] = [
                    'nama' => $daftar_header[$lev2],
                    'is_header' => true
                ];
            }

            $saldo_komulatif = $keuangan->komSaldo($komulatif);
            $saldo_bulan_1 = $keuangan->komSaldo($bulan1);
            $saldo_bulan_2 = $keuangan->komSaldo($bulan2);
            $saldo_bulan_3 = $keuangan->komSaldo($bulan3);

            $data_e_budgeting[] = [
                'nama' => $query->kode_akun . '. ' . $query->nama_akun,
                'komulatif' => $saldo_komulatif,
                'rencana1' => ($bulan1->eb) ? $bulan1->eb->jumlah : 0,
                'realisasi1' => $saldo_bulan_1,
                'rencana2' => ($bulan2->eb) ? $bulan2->eb->jumlah : 0,
                'realisasi2' => $saldo_bulan_2 - $saldo_bulan_1,
                'rencana3' => ($bulan3->eb) ? $bulan3->eb->jumlah : 0,
                'realisasi3' => $saldo_bulan_3 - $saldo_bulan_2 - $saldo_bulan_1,
                'total' => $saldo_komulatif + $saldo_bulan_3,
                'is_header' => false
            ];
        }

        $data['e_budgeting'] = $data_e_budgeting;
        $data['title'] = 'E - Budgeting';

        $view = view('pelaporan.partials.views.e_budgeting', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
            'orientation' => 'landscape',
        ]);
        return $pdf->inline();
    }

    private function alokasi_laba(array $data)
    {

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }
        $data['accounts'] = Account::where('business_id', Session::get('business_id'))->where('kode_akun', 'like', '2.1.04%')->with([
            'amount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun']);
            }
        ])->get();

        $data['title'] = 'Alokasi Laba';
        $view = view('pelaporan.partials.views.tutup_buku.alokasi_laba', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function jurnal_tutup_buku(array $data)
    {

        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['judul'] = 'Laporan Keuangan';
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['transactions'] = Transaction::where([
            ['business_id', Session::get('business_id')],
            ['tgl_transaksi', 'LIKE', $data['tahun'] . '-' . $data['bulan'] . '%']
        ])
            ->with([
                'acc_debit',
                'acc_kredit',
            ])->get();

        $data['title'] = 'Jurnal Tutup Buku';
        $view = view('pelaporan.partials.views.tutup_buku.jurnal', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function neraca_tutup_buku(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Awal Tahun ' . Tanggal::tahun($tgl);

        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.accounts' => function ($query) {
                $query->where('business_id', Session::get('business_id'));
            },
            'akun2.akun3.accounts.amount' => function ($query) use ($data) {
                $query->where([
                    ['tahun', $data['tahun']],
                    ['bulan', '0']
                ]);
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $laba_rugi = Account::where('business_id', Session::get('business_id'))->where('lev1', '>=', '4')->with([
            'amount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0');
                });
            },
        ])->get();

        $pendapatan = 0;
        $beban = 0;
        foreach ($laba_rugi as $lr) {
            $saldo = $keuangan->komSaldo($lr);
            if ($lr->lev1 == '4') {
                $pendapatan += $saldo;
            }

            if ($lr->lev1 == '5') {
                $beban += $saldo;
            }
        }
        $data['surplus'] = $pendapatan - $beban;

        $data['title'] = 'Neraca Awal Tahun';
        $view = view('pelaporan.partials.views.tutup_buku.neraca', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function laba_rugi_tutup_buku(array $data)
    {
        $thn = $data['tahun'];

        $data['judul'] = 'Laporan Keuangan';
        $data['tgl'] = 'Awal Tahun ' . $thn;

        $dataPendapatan = Account::where('business_id', Session::get('business_id'))->where('kode_akun', 'LIKE', '4.1.%')
            ->with([
                'amount' => function ($query) use ($data) {
                    $query->where([
                        ['tahun', $data['tahun']],
                        ['bulan', '0']
                    ]);
                },
                'oneAmount' => function ($query) use ($data) {
                    $query->where([
                        ['tahun', $data['tahun']],
                        ['bulan', '0']
                    ]);
                }
            ])->orderBy('kode_akun', 'ASC')->get();

        $dataBeban = Account::where('business_id', Session::get('business_id'))->where('kode_akun', 'LIKE', '5.1.%')
            ->orWhere('kode_akun', 'LIKE', '5.2.%')
            ->where('kode_akun', '!=', '5.2.01.01')
            ->with([
                'amount' => function ($query) use ($data) {
                    $query->where([
                        ['tahun', $data['tahun']],
                        ['bulan', '0']
                    ]);
                },
                'oneAmount' => function ($query) use ($data) {
                    $query->where([
                        ['tahun', $data['tahun']],
                        ['bulan', '0']
                    ]);
                }
            ])->orderBy('kode_akun', 'ASC')->get();

        $dataPen = Account::where('business_id', Session::get('business_id'))->where('kode_akun', 'LIKE', '4.2.%')
            ->orWhere('kode_akun', 'LIKE', '4.3.%')
            ->whereNotIn('kode_akun', ['4.3.01.01', '4.3.01.02', '4.3.01.03'])
            ->with([
                'amount' => function ($query) use ($data) {
                    $query->where([
                        ['tahun', $data['tahun']],
                        ['bulan', '0']
                    ]);
                },
                'oneAmount' => function ($query) use ($data) {
                    $query->where([
                        ['tahun', $data['tahun']],
                        ['bulan', '0']
                    ]);
                }
            ])->orderBy('kode_akun', 'ASC')->get();

        $dataBeb = Account::where('business_id', Session::get('business_id'))->where('kode_akun', 'LIKE', '5.3.%')
            ->orWhere('kode_akun', 'LIKE', '5.4.%')
            ->where('kode_akun', '!=', '5.4.01.01')
            ->with([
                'amount' => function ($query) use ($data) {
                    $query->where([
                        ['tahun', $data['tahun']],
                        ['bulan', '0']
                    ]);
                },
                'oneAmount' => function ($query) use ($data) {
                    $query->where([
                        ['tahun', $data['tahun']],
                        ['bulan', '0']
                    ]);
                }
            ])->orderBy('kode_akun', 'ASC')->get();

        $pph = Account::where('business_id', Session::get('business_id'))->where('kode_akun', '5.4.01.01')->with([
            'amount' => function ($query) use ($data) {
                $query->where([
                    ['tahun', $data['tahun']],
                    ['bulan', '0']
                ]);
            },
            'oneAmount' => function ($query) use ($data) {
                $query->where([
                    ['tahun', $data['tahun']],
                    ['bulan', '0']
                ]);
            }
        ])->orderBy('kode_akun', 'ASC')->get();

        $bebanPemasaran = Account::where('business_id', Session::get('business_id'))->where('kode_akun', '5.2.01.01')->with([
            'amount' => function ($query) use ($data) {
                $query->where([
                    ['tahun', $data['tahun']],
                    ['bulan', '0']
                ]);
            },
            'oneAmount' => function ($query) use ($data) {
                $query->where([
                    ['tahun', $data['tahun']],
                    ['bulan', '0']
                ]);
            }
        ])->orderBy('kode_akun', 'ASC')->get();

        $pendluar = Account::where('business_id', Session::get('business_id'))->whereIn('kode_akun', ['4.3.01.01', '4.3.01.02', '4.3.01.03'])->with([
            'amount' => function ($query) use ($data) {
                $query->where([
                    ['tahun', $data['tahun']],
                    ['bulan', '0']
                ]);
            },
            'oneAmount' => function ($query) use ($data) {
                $query->where([
                    ['tahun', $data['tahun']],
                    ['bulan', '0']
                ]);
            }
        ])->orderBy('kode_akun', 'ASC')->get();

        $data['pendapatan'] = $dataPendapatan;
        $data['beban'] = $dataBeban;
        $data['pen'] = $dataPen;
        $data['beb'] = $dataBeb;
        $data['ph'] = $pph;
        $data['bp'] = $bebanPemasaran;
        $data['pendl'] = $pendluar;

        $data['title'] = 'Laba Rugi Awal Tahun';
        $data['sub_judul'] = 'Awal Tahun ' . $thn;
        $view = view('pelaporan.partials.views.tutup_buku.laba_rugi', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function calk_tutup_buku(array $data)
    {
        $keuangan = new Keuangan;
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;
        $data['sub_judul'] = 'Tahun ' . Tanggal::tahun($tgl);
        $data['tgl'] = Tanggal::tahun($tgl);
        if ($data['bulanan']) {
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }
        $data['akun1'] = AkunLevel1::where('lev1', '<=', '3')->with([
            'akun2',
            'akun2.akun3',
            'akun2.akun3.accounts' => function ($query) {
                $query->where('business_id', Session::get('business_id'));
            },
            'akun2.akun3.accounts.amount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            },
        ])->orderBy('kode_akun', 'ASC')->get();

        $laba_rugi = Account::where('business_id', Session::get('business_id'))->where('lev1', '>=', '4')->with([
            'amount' => function ($query) use ($data) {
                $query->where('tahun', $data['tahun'])->where(function ($query) use ($data) {
                    $query->where('bulan', '0')->orwhere('bulan', $data['bulan']);
                });
            },
        ])->get();

        $pendapatan = 0;
        $beban = 0;
        foreach ($laba_rugi as $lr) {
            $saldo = $keuangan->komSaldo($lr);
            if ($lr->lev1 == '4') {
                $pendapatan += $saldo;
            }

            if ($lr->lev1 == '5') {
                $beban += $saldo;
            }
        }

        $data['surplus'] = $pendapatan - $beban;

        $data['title'] = 'Calk';
        $view = view('pelaporan.partials.views.tutup_buku.calk', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    private function persediaan(array $data)
    {
        $thn = $data['tahun'];
        $bln = $data['bulan'];
        $hari = $data['hari'];

        $tgl = $thn . '-' . $bln . '-' . $hari;

        $data['products'] = Product::where('stok', '>', '0')->with('category', 'variations', 'unit')->get();
        $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);

        $data['title'] = 'Daftar Persediaan';
        $view = view('pelaporan.partials.views.persediaan', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'header-html' => view('pelaporan.layouts.header', $data)->render(),
            'header-line' => true,
            'margin-top'     => 20,
            'margin-bottom'  => 15,
            'margin-left'    => 25,
            'margin-right'   => 20,
            'enable-local-file-access' => true,
            'header-spacing' => 2,
        ]);
        return $pdf->inline();
    }

    public function simpanSaldo($tahun, $bulan = 1)
    {
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $date = $tahun . '-' . $bulan . '-01';
        $tgl_kondisi = date('Y-m-t', strtotime($date));
        $accounts = Account::where('business_id', Session::get('business_id'))->with([
            'trx_debit' => function ($query) use ($date, $tgl_kondisi) {
                $query->whereBetween('tgl_transaksi', [$date, $tgl_kondisi])->where('business_id', Session::get('business_id'));
            },
            'trx_kredit' => function ($query) use ($date, $tgl_kondisi) {
                $query->whereBetween('tgl_transaksi', [$date, $tgl_kondisi])->where('business_id', Session::get('business_id'));
            },
            'oneAmount' => function ($query) use ($tahun, $bulan) {
                $bulan = str_pad(intval($bulan - 1), 2, '0', STR_PAD_LEFT);
                $query->where('tahun', $tahun)->where('bulan', $bulan);
            }
        ])->get();

        $amount = [];
        $data_id = [];
        foreach ($accounts as $account) {
            $id = $account->id . $tahun . $bulan;

            $saldo_debit = 0;
            $saldo_kredit = 0;
            if ($account->oneAmount && intval($bulan) > 1) {
                $saldo_debit = $account->oneAmount->debit;
                $saldo_kredit = $account->oneAmount->kredit;
            }

            foreach ($account->trx_debit as $trx_debit) {
                $saldo_debit += $trx_debit->total;
            }

            foreach ($account->trx_kredit as $trx_kredit) {
                $saldo_kredit += $trx_kredit->total;
            }

            $amount[] = [
                'id' => $id,
                'account_id' => $account->id,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'debit' => $saldo_debit,
                'kredit' => $saldo_kredit
            ];

            $data_id[] = $id;
        }

        Amount::whereIn('id', $data_id)->delete();
        Amount::insert($amount);

        $link = request()->url('');
        $param = '/' . $tahun . '/' . $bulan;

        $bulan += 1;
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $param_baru = '/' . $tahun . '/' . $bulan;

        $link = str_replace($param, $param_baru, $link);
        if ($bulan < 13) {
            echo '<a href="' . $link . '" id="next"></a><script>document.querySelector("#next").click()</script>';
            exit;
        } else {
            echo '<script>window.opener.postMessage("closed", "*"); window.close();</script>';
            exit;
        }
    }
}
