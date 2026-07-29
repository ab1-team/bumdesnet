@extends('pelaporan.layouts.base')

@php
    use App\Utils\Tanggal;
@endphp
@section('content')
@foreach ($caters as $cater)
    @php
        $filterInstalasi = $cater->installations->filter(fn($ins) => $ins->usage->count() > 0);
        if ($filterInstalasi->isEmpty()) {
            continue;
        }
    @endphp

    @if ($loop->iteration > 1)
        <div class="break"></div>
    @endif

    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 18px; font-weight: bold;">
                    {{ strtoupper('Tagihan Pelanggan ') }}
                </div>
                <div style="font-size: 16px; font-weight: bold;">{{ strtoupper($sub_judul) }}</div>
            </td>
        </tr>

        <tr>
            <td colspan="3" height="10"></td>
        </tr>
    </table>
    <table border="0" width="100%" style="margin-top: 0; font-size: 12px;">
        <tr>
            <td style="text-align: right; font-weight: bold;">
                Marketing : {{ $cater->nama ?? '-' }}
            </td>
        </tr>
    </table>
    <table border="0" width="100%">
        <thead>
            <tr style="background: rgb(74, 74, 74); font-weight: bold; color: #fff;">
                <th width="4%" class="t l b" rowspan="2">No</th>
                <th width="21%" class="t l b" rowspan="2">Nama</th>
                <th width="15%" class="t l b" rowspan="2">No. Induk</th>
                <th width="10%" class="t l b" rowspan="2">Tgl Aktif</th>
                <th width="30%" class="t l b" colspan="3">Tagihan</th>
                <th width="10%" class="t l b" rowspan="2">Dibayar</th>
                <th width="10%" class="t l b r" rowspan="2">Keterangan</th>
            </tr>
            <tr>
                <th width="10%" class="t l b">s/d Bulan Lalu</th>
                <th width="10%" class="t l b">Bulan Ini</th>
                <th width="10%" class="t l b r">s/d Bulan Ini</th>
            </tr>
        </thead>

        <tbody>
            @php
                $data_desa = [];
                $section = '';
            @endphp
            @foreach ($filterInstalasi as $ins)
                @if (!in_array($ins->desa, $data_desa))
                    @if ($section != $ins->desa && count($data_desa) > 0)
                        <tr class="bold">
                            <td class="t l b" colspan="4" align="right">Jumlah</td>
                            <td class="t l b" align="right">
                                {{ number_format($jumlah_menunggak_bulan_lalu, 2) }}
                            </td>
                            <td class="t l b" align="right">
                                {{ number_format($jumlah_menunggak_bulan_ini, 2) }}
                            </td>
                            <td class="t l b" align="right">
                                {{ number_format($jumlah_menunggak_sampai_bulan_ini, 2) }}
                            </td>
                            <td class="t l b" align="right">
                                {{ number_format($jumlah_bayar, 2) }}
                            </td>
                            <td class="t l b r"></td>
                        </tr>
                    @endif

                    <tr class="bold">
                        <td class="t l b r" colspan="9" height="25">
                            Desa {{ optional($ins->village)->nama }} Dusun {{ optional($ins->village)->dusun }}
                        </td>
                    </tr>

                    @php
                        $data_desa[] = $ins->desa;
                        $section = $ins->desa;

                        $nomor = 1;
                        $jumlah_menunggak_bulan_lalu = 0;
                        $jumlah_menunggak_bulan_ini = 0;
                        $jumlah_menunggak_sampai_bulan_ini = 0;
                        $jumlah_bayar = 0;
                    @endphp
                @endif

                @php
                    $tgl_toleransi = $ins->settings->tanggal_toleransi ?? 0;

                    $bayar = 0;
                    $bulan_lalu = 0;
                    $bulan_ini = 0;
                    $sampai_bulan_ini = 0;
                    $jumlah_menunggak = 0;
                    foreach ($ins->usage as $usage) {
                        $bulan_tagihan = date('Y-m', strtotime($usage->tgl_akhir)) . '-01';
                        $bulan_kondisi = date('Y-m', strtotime($tgl_kondisi)) . '-01';
                        $toleransi = date('Y-m', strtotime($tgl_kondisi)) . '-' . $tgl_toleransi;

                        $tagihan = $usage->nominal + $ins->abodemen;
                        if ($usage->tgl_akhir < $toleransi) {
                            $tagihan += optional($ins->package)->denda ?? 0;
                        }

                        if ($bulan_tagihan < $bulan_kondisi) {
                            $bulan_lalu += $tagihan;
                        } elseif ($bulan_tagihan == $bulan_kondisi) {
                            $bulan_ini += $tagihan;
                        }

                        foreach ($usage->transaction as $trx) {
                            $bayar += $trx->total;
                        }

                        $jumlah_menunggak += 1;
                    }

                    $sampai_bulan_ini = $bulan_lalu + $bulan_ini;
                    $status = 'Lancar';
                    if ($jumlah_menunggak > 0) {
                        $status = 'Menunggak';
                    }

                    if ($jumlah_menunggak > 1) {
                        $status = 'SP';
                    }

                    if ($jumlah_menunggak > 2) {
                        $status = 'SPS';
                    }
                @endphp

                <tr>
                    <td class="t l b" align="center">{{ $nomor++ }}</td>
                    <td class="t l b">{{ optional($ins->customer)->nama }}</td>
                    <td class="t l b">{{ $ins->kode_instalasi }}</td>
                    <td class="t l b" align="center">{{ Tanggal::tglIndo($ins->aktif) }}</td>
                    <td class="t l b" align="right">
                        {{ number_format($bulan_lalu, 2) }}
                    </td>
                    <td class="t l b" align="right">
                        {{ number_format($bulan_ini, 2) }}
                    </td>
                    <td class="t l b" align="right">
                        {{ number_format($sampai_bulan_ini, 2) }}
                    </td>
                    <td class="t l b" align="right">
                        {{ number_format($bayar, 2) }}
                    </td>
                    @php
                        //    Menampilkan bulan terakhir yang belum dibayar (tunggakan terakhir)
                        //    $last = $ins->usage->where('status', 'UNPAID')->sortByDesc('tgl_akhir')->first(); .
                        $last = $ins->usage->sortByDesc('tgl_akhir')->first();
                    @endphp
                    <td style="border: 1px solid black;" align="center">
                        {{ \Carbon\Carbon::parse($last->tgl_akhir)->locale('id')->translatedFormat('F Y') }}
                    </td>

                </tr>

                @php
                    $jumlah_menunggak_bulan_lalu += $bulan_lalu;
                    $jumlah_menunggak_bulan_ini += $bulan_ini;
                    $jumlah_menunggak_sampai_bulan_ini += $sampai_bulan_ini;
                    $jumlah_bayar += $bayar;
                @endphp
            @endforeach
            <tr class="bold">
                <td class="t l b" colspan="4" align="right">Jumlah</td>
                <td class="t l b" align="right">
                    {{ number_format($jumlah_menunggak_bulan_lalu, 2) }}
                </td>
                <td class="t l b" align="right">
                    {{ number_format($jumlah_menunggak_bulan_ini, 2) }}
                </td>
                <td class="t l b" align="right">
                    {{ number_format($jumlah_menunggak_sampai_bulan_ini, 2) }}
                </td>
                <td class="t l b" align="right">
                    {{ number_format($jumlah_bayar, 2) }}
                </td>
                <td class="t l b r"></td>
            </tr>
        </tbody>
    </table>
@endforeach
@endsection
