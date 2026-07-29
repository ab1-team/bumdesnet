@extends('pelaporan.layouts.base')

@php
    use App\Utils\Tanggal;
@endphp
@section('content')
@foreach ($caters as $cater)
    @php
        $filterInstalasi = $cater->installations->filter(fn($ins) => $ins->usage->count() > 0);
    @endphp

    @if ($loop->iteration > 1)
        <div class="break"></div>
    @endif

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 18px; font-weight: bold;">
                    {{ strtoupper('Piutang Pelanggan ') }}
                </div>
                <div style="font-size: 16px; font-weight: bold;">{{ strtoupper($sub_judul) }}</div>
            </td>
        </tr>
    </table>
    <table border="0" width="100%" style="margin-top: 0; font-size: 12px;">
        <tr>
            <td style="text-align: right; font-weight: bold;">
                Marketing : {{ $cater->nama ?? '-' }}
            </td>
        </tr>
    </table>
    <table border="0" width="100%" cellspacing="0" cellpadding="0">
        <thead>
            <tr style="background: rgb(74, 74, 74); font-weight: bold; color: #fff;">
                <th width="4%" class="t l b" rowspan="2">No</th>
                <th width="21%" class="t l b" rowspan="2">Nama</th>
                <th width="15%" class="t l b" rowspan="2">No. Induk</th>
                <th width="30%" class="t l b" colspan="2">Tunggakan</th>
                <th width="10%" class="t l b" rowspan="2">Jumlah Tunggakan</th>
                <th width="10%" class="t l b r" rowspan="2">keterangan</th>
            </tr>
            <tr style="background: rgb(74, 74, 74); font-weight: bold; color: #fff;">
                <th width="10%" class="t l b">Sd.Bulan Lalu</th>
                <th width="10%" class="t l b">Bulan Ini</th>
            </tr>
        </thead>

        <tbody>
            @if ($filterInstalasi->isEmpty())
                <tr>
                    <td colspan="7" align="center">Tidak ada data</td>
                </tr>
            @else
                @php
                    $data_desa = [];
                    $section = '';
                @endphp
                @foreach ($filterInstalasi as $ins)
                    @if (!in_array($ins->desa, $data_desa))
                        @if ($section != $ins->desa && count($data_desa) > 0)
                            <tr class="bold">
                                <td class="t l b" colspan="3" align="right">Jumlah</td>
                                <td class="t l b" align="right">
                                    {{ number_format($jumlah_menunggak_bulan_lalu, 2) }}
                                </td>
                                <td class="t l b" align="right">
                                    {{ number_format($jumlah_menunggak_bulan_ini, 2) }}
                                </td>
                                <td class="t l b" align="right">
                                    {{ number_format($jumlah_tunggakan, 2) }}
                                </td>
                                <td class="t l b" align="right">
                                    {{ number_format($jumlah_bayar, 2) }}
                                </td>
                                <td class="t l b r"></td>
                            </tr>
                        @endif

                        <tr class="bold">
                            <td class="t l b r" colspan="7" height="25">
                                Desa {{ $ins->village->nama }} Dusun {{ $ins->village->dusun }}
                            </td>
                        </tr>

                        @php
                            $data_desa[] = $ins->desa;
                            $section = $ins->desa;

                            $nomor = 1;
                            $jumlah_menunggak_sampai_bulan_lalu = 0;
                            $jumlah_menunggak_bulan_lalu = 0;
                            $jumlah_menunggak_bulan_ini = 0;
                            $jumlah_bayar = 0;
                            $jumlah_tunggakan = 0;
                        @endphp
                    @endif

                    @php
                        $tgl_toleransi = $ins->settings->tanggal_toleransi;

                        $bayar = 0;
                        $sampai_bulan_lalu = 0;
                        $bulan_lalu = 0;
                        $bulan_ini = 0;
                        $jumlah_menunggak = 0;
                        $sd_bulan_lalu = 0;

                        foreach ($ins->usage as $usage) {
                            foreach ($ins->transaction as $trx) {
                                $bulan_tagihan = date('Y-m', strtotime($usage->tgl_akhir)) . '-01';
                                $bulan_kondisi = date('Y-m', strtotime($tgl_kondisi)) . '-01';
                                $bulan_kondisi_lalu =
                                    date('Y-m', strtotime('-1 month', strtotime($bulan_kondisi))) . '-01';
                                $toleransi = date('Y-m', strtotime($bulan_kondisi)) . '-' . $tgl_toleransi;

                                if ($trx->rekening_debit == $akun_piutang->id) {
                                    if ($bulan_tagihan < $bulan_kondisi_lalu) {
                                        $sampai_bulan_lalu += $trx->total;
                                    } elseif ($bulan_tagihan < $bulan_kondisi) {
                                        $bulan_lalu += $trx->total;
                                    } else {
                                        $bulan_ini += $trx->total;
                                    }
                                } else {
                                    $bayar += $trx->total;
                                }
                            }
                        }

                        $tunggakan = $sampai_bulan_lalu + $bulan_lalu + $bulan_ini;
                        $sd_bulan_lalu = $sampai_bulan_lalu + $bulan_lalu;

                        $jumlah_menunggak_sampai_bulan_lalu += $sampai_bulan_lalu;
                        $jumlah_menunggak_bulan_lalu += $sd_bulan_lalu;
                        $jumlah_menunggak_bulan_ini += $bulan_ini;
                        $jumlah_tunggakan += $tunggakan;
                        $jumlah_bayar += $bayar;
                    @endphp

                    <tr>
                        <td class="t l b" align="center">{{ $nomor++ }}</td>
                        <td class="t l b">{{ $ins->customer->nama }}</td>
                        <td class="t l b">{{ $ins->kode_instalasi }}</td>
                        <td class="t l b" align="right">
                            {{ number_format($sd_bulan_lalu, 2) }}
                        </td>
                        <td class="t l b" align="right">
                            {{ number_format($bulan_ini, 2) }}
                        </td>
                        <td class="t l b" align="right">
                            {{ number_format($tunggakan, 2) }}
                        </td>

                        @php
                            //    Menampilkan bulan terakhir yang belum dibayar (tunggakan terakhir)
                            //    $last = $ins->usage->where('status', 'UNPAID')->sortByDesc('tgl_akhir')->first(); .
                            $last = $ins->usage->sortByDesc('tgl_akhir')->first();
                        @endphp
                        <td class="t l b" align="center">
                            {{ \Carbon\Carbon::parse($last->tgl_akhir)->locale('id')->translatedFormat('F Y') }}
                        </td>


                    </tr>
                @endforeach
                <tr class="bold">
                    <td class="t l b" colspan="3" align="right">Jumlah</td>
                    <td class="t l b" align="right">
                        {{ number_format($jumlah_menunggak_bulan_lalu, 2) }}
                    </td>
                    <td class="t l b" align="right">
                        {{ number_format($jumlah_menunggak_bulan_ini, 2) }}
                    </td>
                    <td class="t l b" align="right">
                        {{ number_format($jumlah_tunggakan, 2) }}
                    </td>
                    <td class="t l b r"></td>
                </tr>
            @endif
        </tbody>
    </table>
@endforeach
@endsection
