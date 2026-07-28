<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\Account;
use App\Models\Tenant\Business;
use App\Models\Tenant\Installations;
use App\Models\Tenant\Settings;
use App\Models\Tenant\Usage;
use App\Models\Tenant\User;
use App\Utils\Keuangan;
use App\Utils\Tanggal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Builder;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $keuangan = new Keuangan;

        $Permohonan = Installations::where('business_id', Session::get('business_id'))
            ->where('status', 'R')
            ->count();
        $Pasang = Installations::where('business_id', Session::get('business_id'))
            ->where('status', 'I')
            ->count();
        $Aktif = Installations::where('business_id', Session::get('business_id'))
            ->where('status', 'A')
            ->count();

        $Usages = Installations::where('business_id', Session::get('business_id'))->where('status', 'A')->with([
            'customer',
            'package',
            'oneUsage' => function ($query) {
                $query->where('tgl_akhir', '<=', date('Y-m-d'));
            }
        ])->get();
        $tgl_kondisi = request()->get('tgl_akhir') ?? date('Y-m-d');

        $Tagihan = Installations::where('business_id', Session::get('business_id'))
            ->whereIn('status', ['A', 'B', 'C'])
            ->whereHas('usage', function ($query) use ($tgl_kondisi) {
                $query->where('tgl_akhir', '<=', $tgl_kondisi)
                    ->where('status', 'UNPAID');
            });
        $tgl_kondisi = request()->get('tgl_akhir') ?? date('Y-m-d');

        $Tagihan = Installations::where('business_id', Session::get('business_id'))
            ->whereIn('status', ['A', 'B', 'C'])
            ->with([
                'usage' => function ($query) use ($tgl_kondisi) {
                    $query->where('tgl_akhir', '<=', $tgl_kondisi)
                        ->where('status', 'UNPAID');
                }
            ])
            ->get();

        $JumlahTagihan = $Tagihan->count();


        $UsageCount = 0;
        foreach ($Usages as $usage) {
            if ($usage->oneUsage != null) {
                $UsageCount += 1;
            }
        }

        $bulan = intval(date('m'));
        $chart = $this->chart();

        $pendapatan = $chart['pendapatan'];
        $beban = $chart['beban'];
        $surplus = $chart['surplus'];
        $SaldoPendapatanBulanini = $chart['pendapatan_bulan_ini'];
        $SaldoBebanBulanini = $chart['beban_bulan_ini'];
        $SaldoSurplusBulanini = $chart['surplus_bulan_ini'];

        $pros_pendapatan = $keuangan->ProsSaldo($pendapatan[$bulan - 1], $pendapatan[$bulan]);
        $pros_beban = $keuangan->ProsSaldo($beban[$bulan - 1], $beban[$bulan]);
        $pros_surplus = $keuangan->ProsSaldo($surplus[$bulan - 1], $surplus[$bulan]);

        $charts = json_encode($chart);

        $today = date('Y-m-d');
        $year = date('Y');
        $month = date('m');

        $title = 'Dashboard';
        $api = env('APP_API', 'http://localhost:8080');
        $business = Business::where('id', Session::get('business_id'))->first();
        return view('Dashboard.dashboard')->with(compact('JumlahTagihan', 'SaldoPendapatanBulanini', 'SaldoBebanBulanini', 'SaldoSurplusBulanini', 'Aktif', 'Permohonan', 'Pasang', 'title', 'charts', 'pendapatan', 'beban', 'surplus', 'pros_pendapatan', 'pros_beban', 'pros_surplus', 'business', 'api'));
    }


    private function chart()
    {
        $accounts = Account::where('business_id', Session::get('business_id'))->where(function ($query) {
            $query->where('lev1', '4')->orWhere('lev1', '5');
        })->with([
            'amount' => function ($query) {
                $query->where('tahun', date('Y'))->where('bulan', '<=', date('m'));
            }
        ])->get();

        $bulan = [];
        for ($i = 0; $i <= date('m'); $i++) {
            $bulan[$i] = [
                'pendapatan' => 0,
                'beban' => 0
            ];
        }

        foreach ($accounts as $account) {
            foreach ($account->amount as $amount) {
                $saldo = $amount->kredit - $amount->debit;
                if ($account->jenis_mutasi != 'kredit') {
                    $saldo = $amount->debit - $amount->kredit;
                }

                if ($account->lev1 == '4') {
                    $bulan[intval($amount->bulan)]['pendapatan'] += $saldo;
                } else {
                    $bulan[intval($amount->bulan)]['beban'] += $saldo;
                }
            }
        }

        $nama_bulan = [];
        $pendapatan = [];
        $beban = [];
        $surplus = [];
        $saldo_pendapatan_bulan_ini = 0;
        $saldo_beban_bulan_ini = 0;
        $saldo_surplus_bulan_ini = 0;
        foreach ($bulan as $key => $value) {
            $saldo_pendapatan = 0;
            $saldo_beban = 0;
            if ($key > 0) {
                $saldo_pendapatan = $value['pendapatan'] - $bulan[$key - 1]['pendapatan'];
                $saldo_beban = $value['beban'] - $bulan[$key - 1]['beban'];
            }

            $pendapatan[$key] = $saldo_pendapatan;
            $beban[$key] = $saldo_beban;
            $surplus[$key] = $saldo_pendapatan - $saldo_beban;

            $saldo_pendapatan_bulan_ini = $value['pendapatan'];
            $saldo_beban_bulan_ini = $value['beban'];
            $saldo_surplus_bulan_ini = $saldo_pendapatan_bulan_ini - $saldo_beban_bulan_ini;
            if ($key == 0) {
                $nama_bulan[$key] = 'Awal Tahun';
            } else {
                $tanggal = date('Y-m-d', strtotime(date('Y') . '-' . $key . '-01'));
                $nama_bulan[$key] = Tanggal::namaBulan($tanggal);
            }
        }

        return [
            'nama_bulan' => $nama_bulan,
            'pendapatan' => $pendapatan,
            'beban' => $beban,
            'surplus' => $surplus,
            'pendapatan_bulan_ini' => $saldo_pendapatan_bulan_ini,
            'beban_bulan_ini' => $saldo_beban_bulan_ini,
            'surplus_bulan_ini' => $saldo_surplus_bulan_ini,
        ];
    }

    public function permohonan()
    {
        $pendaftaran = Installations::where('business_id', Session::get('business_id'))->where('status', 'R')->with([
            'customer',
            'package',
            'village',
            'users',
        ])->get();

        return response()->json([
            'permohonan' => $pendaftaran
        ]);
    }
    public function pasang()
    {
        $pasang = Installations::where('business_id', Session::get('business_id'))->where('status', 'I')->with([
            'customer',
            'package',
            'village',
            'users',
        ])->get();

        return response()->json([
            'Pasang' => $pasang
        ]);
    }
    public function Pemakaian()
    {
        $Usages = Installations::where('business_id', Session::get('business_id'))->where('status', 'A')->with([
            'customer',
            'package',
            'village',
            'users',
        ])->get();

        return response()->json([
            'pemakaian' => $Usages
        ]);
    }

    public function tunggakan()
    {
        $tunggakan = Installations::where('business_id', Session::get('business_id'))
            ->where('status', 'A')
            ->whereHas('usage', function (Builder $query) {
                $query->where('status', 'UNPAID')
                    ->whereDate('tgl_akhir', '<=', date('Y-m-d'));
            })
            ->with([
                'customer',
                'package',
                'usage' => function ($query) {
                    $query->where('status', 'UNPAID')
                        ->whereDate('tgl_akhir', '<=', date('Y-m-d'));
                }
            ])
            ->get();

        $tunggakan->each(function ($item) {
            $item->jumlah_tunggakan = $item->usage->count();
        });

        return response()->json([
            'tunggakan' => $tunggakan
        ]);
    }

    public function tagihan()
    {
        $tgl_akhir = request()->get('tgl_akhir') ?: date('Y-m-d');

        $Tagihan = Usage::where('business_id', Session::get('business_id'))
            ->where([
                ['status', 'UNPAID'],
                ['tgl_akhir', '<', $tgl_akhir]
            ])
            ->with([
                'installation',
                'installation.customer',
                'installation.customer.village',
                'installation.package'
            ])
            ->get();

        $setting = Settings::where('business_id', Session::get('business_id'))->first();

        return response()->json([
            'Tagihan' => $Tagihan,
            'setting' => $setting,
        ]);
    }
    public function tagihan_dashboard()
    {
        $tgl_kondisi = request('tgl_akhir') ?? date('Y-m-d');

        $akun_piutang = Account::where('business_id', Session::get('business_id'))
            ->where('kode_akun', '1.1.03.01')
            ->first();

        $Tagihan = Installations::where('business_id', Session::get('business_id'))
            ->whereIn('status', ['A', 'B', 'C'])
            ->whereHas('usage', function ($query) use ($tgl_kondisi) {
                $query->where('tgl_akhir', '<=', $tgl_kondisi)
                    ->where('status', 'UNPAID');
            })
            ->with([
                'customer',
                'village',
                'package',
                'settings',
                'usage' => function ($query) use ($tgl_kondisi) {
                    $query->where('tgl_akhir', '<=', $tgl_kondisi)
                        ->where('status', 'UNPAID')
                        ->orderBy('tgl_akhir')
                        ->orderBy('id');
                },
                'usage.transaction' => function ($query) use ($tgl_kondisi) {
                    $query->where('tgl_transaksi', '<=', $tgl_kondisi);
                },
            ])
            ->get();


        return view('Dashboard.partials.tagihan', [
            'title' => 'Cetak Daftar Tagihan',
            'tgl_kondisi' => $tgl_kondisi,
            'akun_piutang' => $akun_piutang,
            'Tagihan' => $Tagihan,
        ]);
    }
    public function sps($id)
    {
        $keuangan = new Keuangan;

        $thn = request()->input('tahun');
        $bln = request()->input('bulan');
        $hari = request()->input('hari');

        $tgl = $thn . '-' . $bln . '-' . $hari;

        $data = [
            'tahun' => $thn,
            'bulan' => $bln,
            'hari' => $hari,
            'judul' => 'Laporan Keuangan',
            'tgl' => Tanggal::tahun($tgl),
            'sub_judul' => 'Tahun ' . Tanggal::tahun($tgl),
            'cater' => request()->input('cater', null),
        ];

        if (request()->input('bulanan')) {
            $data['bulanan'] = true;
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['dir'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '1']
        ])->first();

        $data['ket'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '8']
        ])->first();

        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();
        $data['tunggakan'] = Installations::where('business_id', Session::get('business_id'))
            ->where('status', 'A')
            ->where('id', $id)
            ->whereHas('usage', function ($query) {
                $query->where('status', 'UNPAID')
                    ->whereDate('tgl_akhir', '<=', date('Y-m-d'));
            }, '>=', 2)
            ->with([
                'customer',
                'settings',
                'package',
                'usage' => function ($query) {
                    $query->where('status', 'UNPAID')
                        ->whereDate('tgl_akhir', '<=', date('Y-m-d'));
                }
            ])
            ->first();
        $data['keuangan'] = $keuangan;
        $data['title'] = 'Cetak Tunggakan (SPS)';

        return view('Dashboard.partials.sps', $data);
    }
    public function CetakTagihan(Request $request)
    {
        $data['id'] = $request->input('id'); // array dari checkbox

        $data['tgl_kondisi'] = $request->get('tgl_akhir') ?: date('Y-m-d');

        $data['akun_piutang'] = Account::where('business_id', Session::get('business_id'))
            ->where('kode_akun', '1.1.03.01')
            ->first();

        $data['Tagihan'] = Installations::where('business_id', Session::get('business_id'))
            ->whereIn('id', $data['id']) // ✅ hanya data yang diceklist
            ->whereIn('status', ['A', 'B', 'C']) // ✅ diperbaiki
            ->with([
                'customer',
                'village',
                'package',
                'settings',
                'usage' => function ($query) use ($data) {
                    $query->where('tgl_akhir', '<=', $data['tgl_kondisi'])
                        ->where('status', 'UNPAID')
                        ->orderBy('tgl_akhir');
                },
                'usage.transaction' => function ($query) use ($data) {
                    $query->where('tgl_transaksi', '<=', $data['tgl_kondisi']);
                },
            ])
            ->get();

        $data['title'] = 'Cetak Tagihan';

        return view('Dashboard.partials.cetak_tagihan', $data);
    }


    public function Cetaktunggakan1($id)
    {
        $keuangan = new Keuangan;

        $thn = request()->input('tahun');
        $bln = request()->input('bulan');
        $hari = request()->input('hari');

        $tgl = $thn . '-' . $bln . '-' . $hari;

        $data = [
            'tahun' => $thn,
            'bulan' => $bln,
            'hari' => $hari,
            'judul' => 'Laporan Keuangan',
            'tgl' => Tanggal::tahun($tgl),
            'sub_judul' => 'Tahun ' . Tanggal::tahun($tgl),
            'cater' => request()->input('cater', null),
        ];

        if (request()->input('bulanan')) {
            $data['bulanan'] = true;
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['dir'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '1']
        ])->first();

        $data['ket'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '8']
        ])->first();

        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();
        $data['tunggakan'] = Installations::where('business_id', Session::get('business_id'))
            ->where('status', 'A')
            ->where('id', $id)
            ->whereHas('usage', function ($query) {
                $query->where([
                    ['status', 'UNPAID'],
                    ['tgl_akhir', '<=', date('Y-m-d')],
                ]);
            })
            ->with([
                'customer',
                'settings',
                'package',
                'usage' => function ($query) {
                    $query->where([
                        ['status', 'UNPAID'],
                        ['tgl_akhir', '<=', date('Y-m-d')],
                    ])
                        ->orderBy('tgl_akhir', 'asc')
                        ->limit(1);
                }
            ])
            ->first();
        $data['keuangan'] = $keuangan;
        $data['title'] = 'Cetak Tunggakan (ST)';

        return view('Dashboard.partials.tunggakan1', $data);
    }

    public function Cetaktunggakan2($id)
    {
        $keuangan = new Keuangan;

        $thn = request()->input('tahun');
        $bln = request()->input('bulan');
        $hari = request()->input('hari');

        $tgl = $thn . '-' . $bln . '-' . $hari;

        $data = [
            'tahun' => $thn,
            'bulan' => $bln,
            'hari' => $hari,
            'judul' => 'Laporan Keuangan',
            'tgl' => Tanggal::tahun($tgl),
            'sub_judul' => 'Tahun ' . Tanggal::tahun($tgl),
            'cater' => request()->input('cater', null),
        ];

        if (request()->input('bulanan')) {
            $data['bulanan'] = true;
            $data['sub_judul'] = 'Bulan ' . Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
            $data['tgl'] = Tanggal::namaBulan($tgl) . ' ' . Tanggal::tahun($tgl);
        }

        $data['dir'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '1']
        ])->first();

        $data['ket'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '8']
        ])->first();

        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();
        $data['tunggakan'] = Installations::where('business_id', Session::get('business_id'))
            ->where('status', 'A')
            ->where('id', $id)
            ->whereHas('usage', function ($query) {
                $query->where([
                    ['status', 'UNPAID'],
                    ['tgl_akhir', '<=', date('Y-m-d')],
                ]);
            })
            ->with([
                'customer',
                'settings',
                'package',
                'usage' => function ($query) {
                    $query->where([
                        ['status', 'UNPAID'],
                        ['tgl_akhir', '<=', date('Y-m-d')],
                    ])
                        ->orderBy('tgl_akhir', 'asc') // ambil tgl_akhir paling awal
                        ->limit(2); // hanya 1 data
                }
            ])
            ->first();
        $data['keuangan'] = $keuangan;
        $data['title'] = 'Cetak Tunggakan (SP)';

        return view('Dashboard.partials.tunggakan2', $data);
    }
}
