<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Tenant\Customer;
use App\Models\Tenant\Installations;
use App\Models\Tenant\Package;
use App\Models\Tenant\Settings;
use App\Models\Tenant\Transaction;
use App\Models\Tenant\Account;
use App\Models\Tenant\Business;
use App\Models\Tenant\Cater;
use App\Models\Tenant\Usage;
use App\Models\Tenant\User;
use App\Models\Tenant\Village;
use App\Utils\Keuangan;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Utils\Tanggal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Controller;

class InstallationsController extends Controller
{
    /**
     * index menampilkan data per status.
     */
    public function index()
    {
        //
    }

    public function permohonan(Request $request)
    {
        if ($request->ajax()) {
            $business_id = Session::get('business_id');
            return DataTables::eloquent(
                Installations::select([
                    'id',
                    'kode_instalasi',
                    'desa',
                    'customer_id',
                    'order',
                    'status'
                ])->where('business_id', $business_id)->where('status', 'R')->with([
                    'customer',
                    'package',
                    'village'
                ])
            )->make(true);
        }
        return view('perguliran.permohonan', ['title' => 'Data Permohonan']);
    }
    public function pasang(Request $request)
    {
        if ($request->ajax()) {
            $business_id = Session::get('business_id');
            return DataTables::eloquent(
                Installations::select([
                    'id',
                    'kode_instalasi',
                    'desa',
                    'customer_id',
                    'pasang',
                    'status'
                ])->where('business_id', $business_id)->where('status', 'I')->with([
                    'customer',
                    'package',
                    'village'
                ])
            )->make(true);
        }
        return view('perguliran.pasang', ['title' => 'Data Pasang']);
    }
    public function aktif(Request $request)
    {
        if ($request->ajax()) {
            $business_id = Session::get('business_id');
            return DataTables::eloquent(
                Installations::select([
                    'id',
                    'kode_instalasi',
                    'desa',
                    'customer_id',
                    'package_id',
                    'cater_id',
                    'aktif',
                    'status'
                ])->where('business_id', $business_id)->where('status', 'A')->with([
                    'customer',
                    'package',
                    'users',
                    'village'
                ])
            )->make(true);
        }
        return view('perguliran.aktif', ['title' => 'Data Aktif']);
    }
    public function blokir(Request $request)
    {
        if ($request->ajax()) {
            $business_id = Session::get('business_id');
            return DataTables::eloquent(
                Installations::select([
                    'id',
                    'kode_instalasi',
                    'desa',
                    'customer_id',
                    'package_id',
                    'cater_id',
                    'aktif',
                    'status'
                ])->where('business_id', $business_id)->where('status', 'B')->with([
                    'customer',
                    'package',
                    'users',
                    'village'
                ])
            )->make(true);
        }
        return view('perguliran.blokir', ['title' => 'Data Blokir']);
    }
    public function cabut(Request $request)
    {
        if ($request->ajax()) {
            $business_id = Session::get('business_id');
            return DataTables::eloquent(
                Installations::select([
                    'id',
                    'kode_instalasi',
                    'desa',
                    'customer_id',
                    'package_id',
                    'cater_id',
                    'aktif',
                    'status'
                ])->where('business_id', $business_id)->where('status', 'C')->with([
                    'customer',
                    'package',
                    'users',
                    'village'
                ])
            )->make(true);
        }
        return view('perguliran.cabut', ['title' => 'Data Cabut']);
    }

    /**
     * cari custommer trx instalasi.
     */
    public function CariPelunasanInstalasi(Request $request)
    {
        $query = $request->input('query');

        // Cari akun berdasarkan kode_akun
        $rekening_debit = Account::where([
            ['kode_akun', '1.1.01.01'],
            ['business_id', Session::get('business_id')]
        ])->first();

        $rekening_kredit = Account::where([
            ['kode_akun', '4.1.01.01'],
            ['business_id', Session::get('business_id')]
        ])->first();

        $customers = Customer::where('business_id', Session::get('business_id'))
            ->where(function ($q) use ($query) {
                $q->where('nama', 'LIKE', "%{$query}%")
                    ->orWhere('nik', 'LIKE', "%{$query}%");
            })
            ->with([
                'installation',
                'installation.transaction' => function ($q) use ($rekening_debit, $rekening_kredit) {
                    if ($rekening_debit && $rekening_kredit) {
                        $q->where([
                            ['rekening_debit', $rekening_debit->id],
                            ['rekening_kredit', $rekening_kredit->id]
                        ]);
                    }
                },
                'installation.village',
                'installation.package',
            ])->get();

        return response()->json($customers);
    }

    /**
     * cari custommers trx tagihan bulanan .
     */
    public function CariTagihanbulanan(Request $request)
    {
        $params = $request->input('query');

        $installations = Installations::select(
            'installations.*',
            'customers.nama',
            'customers.alamat',
            'customers.nik',
            'customers.hp'
        )
            ->join('customers', 'customers.id', 'installations.customer_id')
            ->where(function ($query) use ($params) {
                $query->where('customers.nama', 'LIKE', "%{$params}%")
                    ->orWhere('customers.nik', 'LIKE', "%{$params}%")
                    ->orWhere('installations.kode_instalasi', 'LIKE', "%{$params}%");
            })
            ->where(function ($query) {
                $query->where('installations.business_id', Session::get('business_id'))
                    ->orWhere('customers.business_id', Session::get('business_id'));
            })
            ->whereNotIn('installations.status', ['R', 'I'])
            ->whereHas('usage')
            ->get();

        return response()->json($installations);
    }

    /**
     * cari & menampilkan data custommers trx tagihan bulanan .
     */
    public function usage($kode_instalasi)
    {
        $business_id = Session::get('business_id');

        $rekening_debit = Account::where([
            ['kode_akun', '1.1.01.01'],
            ['business_id', $business_id]
        ])->first();

        $rekening_kredit = Account::where([
            ['kode_akun', '4.1.01.03'],
            ['business_id', $business_id]
        ])->first();

        $installations = Installations::where('kode_instalasi', $kode_instalasi)
            ->with([
                'package',
                'customer',
                'village',
                'settings'
            ])
            ->withSum([
                'transaction' => function ($query) use ($rekening_debit, $rekening_kredit) {
                    if ($rekening_debit && $rekening_kredit) {
                        $query->where([
                            ['rekening_debit', $rekening_debit->id],
                            ['rekening_kredit', $rekening_kredit->id]
                        ]);
                    }
                },
            ], 'total')->first();

        $usages = Usage::where('business_id', Session::get('business_id'))->where([
            ['id_instalasi', $installations->id],
            ['status', 'NOT LIKE', 'PAID']
        ])->get();

        $jumlah_trx = $installations->transaction_sum_total;
        $biaya_instal = $installations->biaya_instalasi;

        $qr = QrCode::generate($installations->id);
        return response()->json([
            'success' => true,
            'view' => view('transaksi.partials.usage')->with(compact('qr', 'installations',  'usages'))->render(),
            'rek_debit' => $rekening_debit,
            'rek_kredit' => $rekening_kredit,
        ]);
    }

    /**
     * kode instalasi register instalasi.
     */
    public function kode_instalasi()
    {
        $bisnis = Session::get('business_id');
        $kd_desa = request()->get('kode_desa');
        $rt = request()->get('kode_rt');

        $jumlah_kode_instalasi_by_desa = Installations::where('business_id', Session::get('business_id'))->where('desa', $kd_desa)->orderBy('kode_instalasi', 'DESC');

        $desa = Village::where('id', $kd_desa)->first();
        $kd_bisnis = substr($desa->kode, 0, 3);
        $kd_desa = substr($desa->kode, 4, 4);
        $kd_dusun = substr($desa->kode, 9,);

        $kode_instalasi = $bisnis . '.' . $kd_dusun  . '.' .  $rt;

        if ($jumlah_kode_instalasi_by_desa->count() > 0) {
            $jumlah = str_pad(($jumlah_kode_instalasi_by_desa->count() + 1), 3, "0", STR_PAD_LEFT);
        } else {
            $jumlah = str_pad(Installations::where('business_id', Session::get('business_id'))->where('desa', $kd_desa)->count() + 1, 3, "0", STR_PAD_LEFT);
        }

        $kode_instalasi .= '.' . $jumlah;

        if (request()->get('kd_instalasi')) {
            $kd_ins = request()->get('kd_instalasi');
            $instalasi = Installations::where('business_id', Session::get('business_id'))->where('kd_instalasi', $kd_ins);
            if ($instalasi->count() > 0) {
                $data_ins = $instalasi->first();

                if ($kd_desa == $data_ins->desa) {
                    $kode_instalasi = $data_ins->kd_instalasi;
                }
            }
        }

        return response()->json([
            'kd_instalasi' => $kode_instalasi
        ], Response::HTTP_ACCEPTED);
    }

    /**
     * jenis paket register instalasi.
     */
    public function jenis_paket($id)
    {
        $business_id = Session::get('business_id');
        $pengaturan = Settings::where('business_id', $business_id);
        $package = Package::where('business_id', Session::get('business_id'))->where('id', $id)->first();


        $tampil_settings = $pengaturan->first();
        return response()->json([
            'success' => true,
            'view' => view('perguliran.partials.jenis_paket')->with(compact('tampil_settings', 'package'))->render()
        ]);
    }

    /**
     * register instalasi.
     */

    public function create()
    {
        $paket = Package::where('business_id', Session::get('business_id'))->get();
        $business_id = Session::get('business_id');
        $pengaturan = Settings::where('business_id', $business_id);
        $settings = $pengaturan->first();

        $caters = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '5']
        ])->get();
        $customer = Customer::where('business_id', Session::get('business_id'))->with(
            'Village',
            'installation'
        )->orderBy('id', 'ASC')->get();

        $desa = Village::all();

        $pilih_desa = 0;
        $title = 'Register Installlation';
        return view('perguliran.create')->with(compact('settings', 'paket', 'caters', 'customer', 'desa', 'pilih_desa', 'title'));
    }

    /**
     * proses simpan register instalasi.
     */
    public function store(Request $request)
    {
        $data = $request->only([
            "customer_id",
            "order",
            "desa",
            "cater",
            "jalan",
            "rw",
            "rt",
            "koordinate",
            "package_id",
            "pasang_baru",
            "abodemen",
            "kode_instalasi",
            "harga_paket",
            "total",
        ]);
        $rules = [
            'kode_instalasi' => 'required',
            'customer_id' => 'required',
            'order' => 'required',
            'desa' => 'required',
            'jalan' => 'required',
            'harga_paket' => 'required',
            'rw' => 'required',
            'rt' => 'required',
            'koordinate' => 'required',
            'package_id' => 'required'
        ];

        $validate = Validator::make($data, $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        $data['pasang_baru'] = str_replace(',', '', $data['pasang_baru']);
        $data['pasang_baru'] = str_replace('.00', '', $data['pasang_baru']);
        $data['pasang_baru'] = floatval($data['pasang_baru']);

        $data['abodemen'] = str_replace(',', '', $data['abodemen']);
        $data['abodemen'] = str_replace('.00', '', $data['abodemen']);
        $data['abodemen'] = floatval($data['abodemen']);

        $data['harga_paket'] = str_replace(',', '', $data['harga_paket']);
        $data['harga_paket'] = str_replace('.00', '', $data['harga_paket']);
        $data['harga_paket'] = floatval($data['harga_paket']);

        $data['total'] = str_replace(',', '', $data['total']);
        $data['total'] = str_replace('.00', '', $data['total']);
        $data['total'] = floatval($data['total']) ?? 0;

        $pasangbaru        = $data['pasang_baru'];
        $abodemen          = $data['abodemen'];
        $biaya_instalasi   = $data['total'];
        $harga_paket       = $data['harga_paket'];

        $biaya_instal = $pasangbaru - $biaya_instalasi;

        $status = 'R';
        $jumlah = $biaya_instal;

        // INSTALLATION = simpan database 
        $install = Installations::create([
            'business_id' => Session::get('business_id'),
            'kode_instalasi' => $request->kode_instalasi,
            'customer_id' => $request->customer_id,
            'cater_id' => $request->cater ?? null,
            'order' => Tanggal::tglNasional($request->order),
            'desa' => $request->desa,
            'alamat' => $request->jalan,
            'rw' => $request->rw,
            'rt' => $request->rt,
            'koordinate' => $request->koordinate,
            'package_id' => $request->package_id,
            'abodemen' => $abodemen,
            'harga_paket' => $harga_paket,
            'biaya_instalasi' => $pasangbaru,
            'status' => $status,
        ]);

        // TRANSACTION = simpan database
        $jumlah_instal = ($biaya_instal >= 0) ? $biaya_instalasi : $pasangbaru;

        $perse = round(100 - ($jumlah / $pasangbaru * 100));
        $persen = max(1, min($perse, 100));

        if ($jumlah_instal > 0) {
            $business_id = Session::get('business_id');
            $rekening_debit = 1;
            $rekening_kredit = 47;

            if ($rekening_debit && $rekening_kredit) {
                $transaksi = Transaction::create([
                    'business_id' => Session::get('business_id'),
                    'rekening_debit' => $rekening_debit,
                    'rekening_kredit' => $rekening_kredit,
                    'tgl_transaksi' => Tanggal::tglNasional($request->order),
                    'total' => $jumlah_instal,
                    'installation_id' => $install->id,
                    'keterangan' => 'Biaya instalasi atas nama ' . $install->customer->nama . ' (' . $install->kode_instalasi . ')',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'msg' => 'Rekening tidak ditemukan, transaksi gagal'
                ], 400);
            }
        }

        return response()->json([
            'success' => true,
            'msg' => 'Daftar & Instalasi berhasil disimpan',
            'installation' => $install
        ]);
    }

    /**
     * Memecah dan menampilkan Detail di Status Instalasi.
     */
    public function show(Installations $installation)
    {
        $func = 'detail' . $installation->status;
        return $this->$func($installation);
    }
    /**
     * Menampilkan Detail dengan status 0.
     */
    private function detail0($installation)
    {
        $business_id = Session::get('business_id');
        $settings = Settings::where('business_id', $business_id)->first();
        $installation = $installation->with([
            'customer',
            'package',
            'village'
        ])->where('id', $installation->id)->first();
        $rekening_debit = 1;
        $rekening_kredit = 47;
        $trx = Transaction::where([
            ['installation_id', $installation->id],
            ['rekening_debit', $rekening_debit],
            ['rekening_kredit', $rekening_kredit]
        ])->sum('total');

        $title = "Detail Permohonan";
        $qr = QrCode::generate($installation->id);
        return view('perguliran.partials.DetailPermohonan')->with(compact('settings', 'title', 'installation', 'trx', 'qr'));
    }

    /**
     * Menampilkan Detail dengan status R.
     */
    private function detailR($installation)
    {
        $business_id = Session::get('business_id');
        $settings = Settings::where('business_id', $business_id)->first();
        $installation = $installation->with([
            'customer',
            'package',
            'village'
        ])->where('id', $installation->id)->first();
        $rekening_debit = 1;
        $rekening_kredit = 47;
        $trx = Transaction::where([
            ['installation_id', $installation->id],
            ['rekening_debit', $rekening_debit],
            ['rekening_kredit', $rekening_kredit]
        ])->sum('total');

        $qr = QrCode::generate($installation->id);

        $title = "Detail Permohonan";
        return view('perguliran.partials.DetailPermohonan')->with(compact('settings', 'title', 'installation', 'trx', 'qr'));
    }
    /**
     * Menampilkan Detail dengan status I.
     */
    private function detailI($installation)
    {
        $business_id = Session::get('business_id');
        $tampil_settings = Settings::where('business_id', $business_id)->first();

        $installation = $installation->with([
            'customer',
            'package',
            'village'
        ])->where('id', $installation->id)->first();
        $rekening_debit = 1;
        $rekening_kredit = 47;

        $trx = Transaction::where([
            ['installation_id', $installation->id],
            ['rekening_debit', $rekening_debit],
            ['rekening_kredit', $rekening_kredit]
        ])->sum('total');
        $qr = QrCode::generate($installation->id);

        return view('perguliran.partials.DetailPasang')->with(compact('installation', 'tampil_settings', 'trx', 'qr'));
    }


    /**
     * Menampilkan Detail dengan status A.
     */
    private function detailA(Installations $installation)
    {
        $business_id = Session::get('business_id');
        $tampil_settings = Settings::where('business_id', $business_id)->first();
        $rekening_debit = 1;
        $rekening_kredit = 49;
        $trx = Transaction::where([
            ['installation_id', $installation->id],
            ['rekening_debit', $rekening_debit],
            ['rekening_kredit', $rekening_kredit]
        ])->sum('total');
        $qr = QrCode::generate($installation->id);

        return view('perguliran.partials.DetailAktif')->with(compact('installation', 'tampil_settings', 'trx', 'qr'));
    }

    /**
     * Menampilkan Detail dengan status B.
     */
    private function detailB($installation)
    {
        $business_id = Session::get('business_id');
        $tampil_settings = Settings::where('business_id', $business_id)->first();
        $installation = $installation->with([
            'customer',
            'package',
            'village'
        ])->where('id', $installation->id)->first();
        $rekening_debit = 1;
        $rekening_kredit = 47;
        $trx = Transaction::where([
            ['installation_id', $installation->id],
            ['rekening_debit', $rekening_debit],
            ['rekening_kredit', $rekening_kredit]
        ])->sum('total');
        $qr = QrCode::generate($installation->id);

        return view('perguliran.partials.DetailBlokir')->with(compact('installation', 'tampil_settings', 'trx', 'qr'));
    }

    /**
     * Menampilkan Detail dengan status C.
     */
    private function detailC($installation)
    {
        $business_id = Session::get('business_id');
        $tampil_settings = Settings::where('business_id', $business_id)->first();

        $installation = $installation->with([
            'customer',
            'package',
            'village'
        ])->where('id', $installation->id)->first();

        $rekening_debit = 1;
        $rekening_kredit = 49;

        $trx = Transaction::where([
            ['installation_id', $installation->id],
            ['rekening_debit', $rekening_debit],
            ['rekening_kredit', $rekening_kredit]
        ])->sum('total');
        $qr = QrCode::generate($installation->id);
        return view('perguliran.partials.DetailCopot')->with(compact('installation', 'tampil_settings', 'trx', 'qr'));
    }
    public function surat_tagihan(Installations $installation)
    {
        $data['installation'] = $installation;
        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();
        $data['usages'] = Usage::where('id_instalasi', $installation->id)->where('status', 'UNPAID')->get();
        $data['title'] = 'Surat Tagihan';
        $view = view('perguliran.document.surat_tagihan', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'margin-top'    => 10,
            'margin-bottom' => 20,
            'margin-left'   => 25,
            'margin-right'  => 20,
            'enable-local-file-access' => true,
        ]);
        return $pdf->inline();
    }

    public function strukTagihan(Installations $installation)
    {
        $keuangan = new Keuangan;
        $id = $installation->id;
        $data['bisnis'] = Business::where('id', Session::get('business_id'))->first();
        $data['usage'] = Usage::where('business_id', Session::get('business_id'))
            ->whereIn('id_instalasi', [$id])->with(
                'customers',
                'installation',
                'usersCater',
                'installation.package'
            )->get();
        $data['jabatan'] = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '3']
        ])->first();
        $logo = $data['bisnis']->logo;
        $data['gambar'] = $logo;
        $data['keuangan'] = $keuangan;

        $view = view('perguliran.document.struk', $data)->render();
        $pdf = PDF::loadHTML($view)->setOptions([
            'margin-top'    => 10,
            'margin-bottom' => 20,
            'margin-left'   => 25,
            'margin-right'  => 20,
            'enable-local-file-access' => true,
        ]);
        return $pdf->inline();
    }
    public function cetak_pemakaian(Installations $installation)
    {
        $keuangan = new Keuangan;
        $bisnis = Business::where('id', Session::get('business_id'))->first();
        $installation = $installation->with([
            'customer',
            'package',
            'village'
        ])->where('id', $installation->id)->first();
        // $logo = $bisnis->logo; 

        // $data['gambar'] = $logo;

        $data['gambar'] = $bisnis->logo;
        $data['keuangan'] = $keuangan;
        $data['qr'] = QrCode::size(50)->generate((string) $installation->id);
        $data['installation'] = $installation;
        $data['bisnis'] = $bisnis;

        return view('perguliran.document.cetak', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Installations $installation)
    {
        $business_id = Session::get('business_id');
        $pengaturan = Settings::where('business_id', $business_id);
        $settings = $pengaturan->first();
        $paket = Package::where('business_id', Session::get('business_id'))->get();
        $customer = Customer::where('business_id', Session::get('business_id'))->with('Village')->orderBy('id', 'ASC')->get();
        $installations = $installation->with([
            'customer',
            'package',
            'village'
        ])->where('id', $installation->id)->first();

        $caters = User::where([
            ['business_id', Session::get('business_id')],
            ['jabatan', '5']
        ])->get();

        $desa = Village::all();

        $qr = QrCode::generate($installations->id);

        $pilih_desa = 0;
        $title = 'Register Proposal';
        return view('perguliran.partials.edit_permohonan')->with(compact('settings', 'qr', 'caters', 'paket', 'installations', 'customer', 'desa', 'pilih_desa', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Installations $installation)
    {
        //progres simpan detail| $request->status = 0
        $func = 'update' . $request->status; // update0
        return $this->$func($request, $installation); // $this->update0()
        //end progres simpan detail
    }

    /**
     * Update Edit data.
     */
    private function updateEditData($request, $installation)
    {
        $data = $request->only([
            "order",
            "alamat",
            "koordinate"
        ]);

        $rules = [
            'order' => 'required',
            'alamat' => 'sometimes',
            'koordinate' => 'sometimes'
        ];
        $validate = Validator::make($data, $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        // Update data 
        $update = Installations::where('business_id', Session::get('business_id'))->where('id', $installation->id)->update([
            'business_id' => Session::get('business_id'),
            'order' => Tanggal::tglNasional($request->order),
            'alamat' => $request->alamat,
            'koordinate' => $request->koordinate
        ]);
        return response()->json([
            'success' => true,
            'msg' => 'Edit berhasil disimpan',
            'Editpermohonan' => $update
        ]);
    }

    /**
     * Update Edit data Status 0.
     */
    private function update0($request, $installation)
    {
        $data = $request->only([
            "pasang",
        ]);

        $rules = [
            'pasang' => 'required',
        ];
        $validate = Validator::make($data, $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        // Update data 
        $update = Installations::where('business_id', Session::get('business_id'))->where('id', $installation->id)->update([
            'business_id' => Session::get('business_id'),
            'pasang' => Tanggal::tglNasional($request->pasang),
            'status' => 'I',
        ]);
        return response()->json([
            'success' => true,
            'msg' => 'Progres Pasang berhasil disimpan',
            'Pasang' => $installation
        ]);
    }

    /**
     * Update Detail Status R.
     */
    private function updateR($request, $installation)
    {
        $data = $request->only([
            "pasang",
        ]);

        $rules = [
            'pasang' => 'required',
        ];
        $validate = Validator::make($data, $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        // Update data 
        $update = Installations::where('business_id', Session::get('business_id'))->where('id', $installation->id)->update([
            'business_id' => Session::get('business_id'),
            'pasang' => Tanggal::tglNasional($request->pasang),
            'status' => 'I',
        ]);
        return response()->json([
            'success' => true,
            'msg' => 'Progres Pasang berhasil disimpan',
            'Pasang' => $installation
        ]);
    }

    /**
     * Update Detail Status I.
     */
    private function updateI($request, $installation)
    {
        $data = $request->only([
            "aktif",
        ]);

        $rules = [
            'aktif' => 'required',
        ];

        $validate = Validator::make($data, $rules);
        if ($validate->fails()) {
            return response()->json($validate->errors(), Response::HTTP_MOVED_PERMANENTLY);
        }

        // INSTALLATION
        $instal = Installations::where('business_id', Session::get('business_id'))->where('id', $installation->id)->update([
            'business_id' => Session::get('business_id'),
            'aktif' => Tanggal::tglNasional($request->aktif),
            'status' => 'A',
        ]);

        return response()->json([
            'success' => true,
            'msg' => 'Daftar & Pemakaian awal berhasil disimpan',
            'aktif' => $installation
        ]);
    }

    /**
     * Update Detail Status A.
     */
    private function updateA($request, $installation)
    {
        $data = $request->only([
            "id",
            "tgl_akhir",
            "kode_instalasi",
        ]);

        $rules = [
            'tgl_akhir' => 'required|date_format:d/m/Y',
        ];

        $validate = Validator::make($data, $rules);

        if ($validate->fails()) {
            return response()->json($validate->errors(), 422);
        }

        $package = Package::find($installation->package_id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'msg' => 'Package tidak ditemukan.'
            ], 404);
        }

        $lastUsage = Usage::where('id_instalasi', $installation->id)->latest()->first();

        if (!$lastUsage) {
            return response()->json([
                'success' => false,
                'msg' => 'Tidak bisa dicabut! Tidak ada pemakaian bulan ini.'
            ], 422);
        }

        $date = Carbon::createFromFormat('d/m/Y', $request->tgl_akhir);
        $tgl_akhir_hari = $date->format('d');
        $jumlah_hari_bulan_ini = $date->daysInMonth;

        $harga = $package->harga;
        $jumlah_rasio = round($tgl_akhir_hari / $jumlah_hari_bulan_ini, 2);
        $nominal = $harga * $jumlah_rasio;

        Usage::where('business_id', Session::get('business_id'))
            ->where('id', $lastUsage->id)
            ->update([
                'business_id' => Session::get('business_id'),
                'akhir'       => $tgl_akhir_hari,
                'jumlah'      => $jumlah_rasio,
                'nominal'     => $nominal,
                'tgl_akhir'   => Tanggal::tglNasional($request->tgl_akhir),
            ]);

        Installations::where('business_id', Session::get('business_id'))
            ->where('id', $installation->id)
            ->update([
                'business_id' => Session::get('business_id'),
                'cabut'       => Tanggal::tglNasional($request->tgl_akhir),
                'status'      => 'C',
            ]);

        return response()->json([
            'success' => true,
            'msg'     => 'Pencabutan Customer Berhasil',
            'aktif'   => $installation
        ]);
    }

    /**
     * Update Detail Status B.
     */
    private function updateB($request, $installation)
    {
        $data = $request->only([
            "cabut",
            "id"
        ]);

        $rules = [
            'cabut' => 'required|date_format:d/m/Y',
        ];

        $validate = Validator::make($data, $rules);

        if ($validate->fails()) {
            return response()->json([
                'success' => false,
                'msg' => $validate->errors()->first()
            ], 422);
        }

        $package = Package::find($installation->package_id);

        if (!$package) {
            return response()->json([
                'success' => false,
                'msg' => 'Package tidak ditemukan.'
            ], 404);
        }

        $lastUsage = Usage::where('id_instalasi', $installation->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->latest()
            ->first();

        if (!$lastUsage) {
            return response()->json([
                'success' => false,
                'msg' => 'Tidak bisa diblokir! Tidak ada pemakaian bulan ini.'
            ], 422);
        }

        $date = Carbon::createFromFormat('d/m/Y', $request->cabut);
        $hari_blokir = $date->format('d');
        $jumlah_hari_bulan_ini = $date->daysInMonth;

        $harga = $package->harga;
        $jumlah_rasio = round($hari_blokir / $jumlah_hari_bulan_ini, 2);
        $nominal = $harga * $jumlah_rasio;

        Usage::where('business_id', Session::get('business_id'))
            ->where('id', $lastUsage->id)
            ->update([
                'business_id' => Session::get('business_id'),
                'akhir'       => $hari_blokir,
                'jumlah'      => $jumlah_rasio,
                'nominal'     => $nominal,
                'tgl_akhir'   => Tanggal::tglNasional($request->cabut),
            ]);

        Installations::where('business_id', Session::get('business_id'))
            ->where('id', $installation->id)
            ->update([
                'business_id' => Session::get('business_id'),
                'blokir'      => Tanggal::tglNasional($request->cabut),
                'status'      => 'B',
            ]);

        return response()->json([
            'success' => true,
            'msg'     => 'Blokir Customer Berhasil',
            'blokir'  => $installation
        ]);
    }

    private function updateC($request, $installation)
    {
        $data = $request->only(['id']);
        $validator = Validator::make($data, [
            'id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $installationId = $installation->id;
            Transaction::where('installation_id', $installationId)->delete();
            Usage::where('id_instalasi', $installationId)->delete();
            Installations::where('business_id', session('business_id'))
                ->where('id', $installationId)
                ->delete();

            return response()->json([
                'success' => true,
                'msg' => 'Instalasi dan seluruh data terkait berhasil dihapus permanen.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Update Detail Status B kembali menjaddi Aktif.
     */
    public function blokirStatus(Request $request, $id)
    {
        $data = $request->only([
            'id',
            'tgl_blokir',
        ]);
        $instal = Installations::where('business_id', Session::get('business_id'))->where('id', $id)->update([
            'business_id' => Session::get('business_id'),
            'status' => 'B',
            'blokir' => Tanggal::tglNasional($request->tgl_blokir),
        ]);

        return response()->json([
            'success' => true,
            'msg' => '"Data berhasil diblokir dan statusnya  menjadi Blokir."',
            'kembaliA' => $instal
        ]);
    }
    /**
     * menghapus data instalasi status R.
     */
    public function destroy(Installations $installation)
    {
        // Menghapus Installations berdasarkan id yang diterima
        Installations::where('id', $installation->id)->delete();
        Transaction::where('installation_id', $installation->id)->delete();
        Usage::where('id_instalasi', $installation->id)->delete();

        // Redirect ke halaman Installations dengan pesan sukses
        return response()->json([
            'success' => true,
            'msg' => 'Permohonan berhasil dihapus',
            'installation' => $installation
        ]);
    }
    public function list($cater_id = 0)
    {
        $tanggal = request()->get('tanggal') ?: date('d/m/Y');
        $tanggal = Tanggal::tglNasional($tanggal);

        if ($cater_id == '0') {
            return response()->json([
                'success' => true,
                'installations' => []
            ]);
        }

        $installations = Installations::where('business_id', Session::get('business_id'))->where('cater_id', $cater_id)->with([
            'oneUsage' => function ($query) use ($tanggal) {
                $query->orderBy('id', 'DESC');
            },
            'customer.village',
            'package',
            'village',
            'users'
        ])->get();

        return response()->json([
            'success' => true,
            'installations' => $installations
        ]);
    }
}
