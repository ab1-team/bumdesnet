@extends('pelaporan.layouts.base')

@php
    use App\Utils\Keuangan;
    $keuangan = new Keuangan();
@endphp
@section('content')

@php
    $saldo_bln_lalu1 = 0;
    $saldo_sd_bulan_ini1 = 0;
    $saldo_bln_lalu2 = 0;
    $saldo_sd_bulan_ini2 = 0;

@endphp
<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <tr>
        <td colspan="4" align="center">
            <div style="font-size: 18px;">
                <b>LAPORAN LABA RUGI</b>
            </div>
            <div style="font-size: 16px;">
                <b>{{ strtoupper($sub_judul) }}</b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="4" height="5"></td>
    </tr>
</table>
<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <thead>
        <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 12px;">
            <th align="center" width="55%" height="16">Rekening</th>
            <th align="center" width="15%">s.d. {{ $header_lalu ?? 'Bulan Lalu' }}</th>
            <th align="center" width="15%">{{ $header_sekarang ?? 'Bulan Ini' }}</th>
            <th align="center" width="15%">s.d. {{ $header_sekarang ?? 'Bulan Ini' }}</th>
        </tr>
    </thead>

    <tbody>
        <tr style="background: rgb(200, 200, 200); font-weight: bold; text-transform: uppercase;">
            <td colspan="4" height="14">4. Pendapatan</td>
        </tr>
        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td colspan="4" height="14">4.1.00.00. Pendapatan Usaha</td>
        </tr>

        @php
            $total_sd_bulan_lalu = 0;
            $total_bulan_ini = 0;
            $total_sd_bulan_ini = 0;
        @endphp
        @foreach ($pendapatan as $p)
            @php
                $saldo = $keuangan->komSaldoLB($p);
            @endphp
            <tr class="{{ $loop->iteration % 2 == 1 ? 'row-white' : 'row-black' }}">
                <td>{{ $p->kode_akun }}. {{ $p->nama_akun }}</td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
            </tr>
            @php
                $total_sd_bulan_lalu += $saldo['saldo_sd_bulan_lalu'];
                $total_bulan_ini += $saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'];
                $total_sd_bulan_ini += $saldo['saldo_sd_bulan_ini'];
            @endphp
            @php
                $saldo_bln_lalu1 += $saldo['saldo_sd_bulan_lalu'];
                $saldo_sd_bulan_ini1 += $saldo['saldo_sd_bulan_ini'];
            @endphp
        @endforeach

        <tr style="background: rgb(150, 150, 150);">
            <td align="left">Jumlah Pendapatan:</td>
            <td align="right">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td align="right">{{ number_format($total_bulan_ini, 2) }}</td>
            <td align="right">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>

        <tr style="background: rgb(200, 200, 200); font-weight: bold; text-transform: uppercase;">
            <td colspan="4" height="16">5. Beban</td>
        </tr>
        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td colspan="4" height="14">5.1.00.00. Beban Usaha</td>
        </tr>

        @php
            $total_sd_bulan_lalu = 0;
            $total_bulan_ini = 0;
            $total_sd_bulan_ini = 0;
        @endphp
        @foreach ($beban as $b)
            @php
                $saldo = $keuangan->komSaldoLB($b);
            @endphp
            <tr class="{{ $loop->iteration % 2 == 1 ? 'row-white' : 'row-black' }}">
                <td>{{ $b->kode_akun }}. {{ $b->nama_akun }}</td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td align="right">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
            </tr>
            @php
                $total_sd_bulan_lalu += $saldo['saldo_sd_bulan_lalu'];
                $total_bulan_ini += $saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'];
                $total_sd_bulan_ini += $saldo['saldo_sd_bulan_ini'];
            @endphp
            @php
                $saldo_bln_lalu1 -= $saldo['saldo_sd_bulan_lalu'];
                $saldo_sd_bulan_ini1 -= $saldo['saldo_sd_bulan_ini'];
            @endphp
        @endforeach

        <tr style="background: rgb(150, 150, 150);">
            <td align="left">Jumlah 5.1.00.00. Beban Usaha</td>
            <td align="right">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td align="right">{{ number_format($total_bulan_ini, 2) }}</td>
            <td align="right">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>
        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td colspan="4" height="16">5.2.00.00. Beban Non Usaha
            </td>
        </tr>

        @php
            $total_sd_bulan_lalu = 0;
            $total_bulan_ini = 0;
            $total_sd_bulan_ini = 0;
        @endphp
        @foreach ($bp as $bp)
            @php
                $saldo = $keuangan->komSaldoLB($bp);
            @endphp
            <tr class="{{ $loop->iteration % 2 == 1 ? 'row-white' : 'row-black' }}">
                <td>{{ $bp->kode_akun }}. {{ $bp->nama_akun }}</td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td align="right">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
            </tr>
            @php
                $total_sd_bulan_lalu += $saldo['saldo_sd_bulan_lalu'];
                $total_bulan_ini += $saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'];
                $total_sd_bulan_ini += $saldo['saldo_sd_bulan_ini'];
            @endphp
            @php
                $saldo_bln_lalu1 -= $saldo['saldo_sd_bulan_lalu'];
                $saldo_sd_bulan_ini1 -= $saldo['saldo_sd_bulan_ini'];
            @endphp
        @endforeach

        <tr style="background: rgb(150, 150, 150);">
            <td align="left">Jumlah 5.2.00.00. Beban Non Usaha</td>
            <td align="right">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td align="right">{{ number_format($total_bulan_ini, 2) }}</td>
            <td align="right">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>
        <tr style="background: rgb(200, 200, 200);">
            <td align="left">A. Laba Rugi OPERASIONAL (Kode Akun 4.1 - 5.1 - 5.2) </td>
            <td align="right">{{ number_format($saldo_bln_lalu1, 2) }}</td>
            <td align="right">{{ number_format($saldo_sd_bulan_ini1 - $saldo_bln_lalu1, 2) }}</td>
            <td align="right">{{ number_format($saldo_sd_bulan_ini1, 2) }}</td>
        </tr>

        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td colspan="4" height="16">4.2.00.00. Pendapatan Non Usaha</td>
        </tr>

        @php
            $total_sd_bulan_lalu = 0;
            $total_bulan_ini = 0;
            $total_sd_bulan_ini = 0;
        @endphp
        @foreach ($pen as $pn)
            @php
                $saldo = $keuangan->komSaldoLB($pn);
            @endphp
            <tr class="{{ $loop->iteration % 2 == 1 ? 'row-white' : 'row-black' }}">
                <td>{{ $pn->kode_akun }}. {{ $pn->nama_akun }}</td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td align="right">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
            </tr>
            @php
                $total_sd_bulan_lalu += $saldo['saldo_sd_bulan_lalu'];
                $total_bulan_ini += $saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'];
                $total_sd_bulan_ini += $saldo['saldo_sd_bulan_ini'];
            @endphp
            @php
                $saldo_bln_lalu2 += $saldo['saldo_sd_bulan_lalu'];
                $saldo_sd_bulan_ini2 += $saldo['saldo_sd_bulan_ini'];
            @endphp
        @endforeach

        <tr style="background: rgb(150, 150, 150);">
            <td align="left">Jumlah 4.2.00.00. Pendapatan Non Usaha</td>
            <td align="right">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td align="right">{{ number_format($total_bulan_ini, 2) }}</td>
            <td align="right">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>
        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td colspan="4" height="16">4.3.00.00. Pendapatan Luar biasa</td>
        </tr>

        @php
            $total_sd_bulan_lalu = 0;
            $total_bulan_ini = 0;
            $total_sd_bulan_ini = 0;
        @endphp
        @foreach ($pendl as $pendl)
            @php
                $saldo = $keuangan->komSaldoLB($pendl);
            @endphp
            <tr class="{{ $loop->iteration % 2 == 1 ? 'row-white' : 'row-black' }}">
                <td>{{ $pendl->kode_akun }}. {{ $pendl->nama_akun }}</td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td align="right">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
            </tr>
            @php
                $total_sd_bulan_lalu += $saldo['saldo_sd_bulan_lalu'];
                $total_bulan_ini += $saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'];
                $total_sd_bulan_ini += $saldo['saldo_sd_bulan_ini'];
            @endphp
        @endforeach

        <tr style="background: rgb(150, 150, 150);">
            <td align="left">Jumlah 4.3.00.00. Pendapatan Luar Biasa</td>
            <td align="right">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td align="right">{{ number_format($total_bulan_ini, 2) }}</td>
            <td align="right">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>

        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td colspan="4" height="16">5.3.00.00. Beban Non Usaha</td>
        </tr>
        @php
            $total_sd_bulan_lalu = 0;
            $total_bulan_ini = 0;
            $total_sd_bulan_ini = 0;
        @endphp

        @foreach ($beb as $bb)
            @php
                $saldo = $keuangan->komSaldoLB($bb);
            @endphp
            <tr class="{{ $loop->iteration % 2 == 1 ? 'row-white' : 'row-black' }}">
                <td>{{ $bb->kode_akun }}. {{ $bb->nama_akun }}</td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td align="right">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
            </tr>
            @php
                $total_sd_bulan_lalu += $saldo['saldo_sd_bulan_lalu'];
                $total_bulan_ini += $saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'];
                $total_sd_bulan_ini += $saldo['saldo_sd_bulan_ini'];
            @endphp
            @php
                $saldo_bln_lalu2 -= $saldo['saldo_sd_bulan_lalu'];
                $saldo_sd_bulan_ini2 -= $saldo['saldo_sd_bulan_ini'];
            @endphp
        @endforeach

        <tr style="background: rgb(150, 150, 150);">
            <td align="left">Jumlah 5.3.00.00. Beban Non Usaha</td>
            <td align="right">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td align="right">{{ number_format($total_bulan_ini, 2) }}</td>
            <td align="right">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>
        <tr style="background: rgb(200, 200, 200);">
            <td align="left">B. Laba Rugi OPERASIONAL (Kode Akun 4.2 - 5.3) </td>
            <td align="right">{{ number_format($saldo_bln_lalu2, 2) }}</td>
            <td align="right">{{ number_format($saldo_sd_bulan_ini2 - $saldo_bln_lalu2, 2) }}</td>
            <td align="right">{{ number_format($saldo_sd_bulan_ini2, 2) }}</td>
        </tr>
        <tr style="background: rgb(200, 200, 200);">
            <td align="left">C. Laba Rugi Sebelum Taksiran Pajak (A + B) </td>
            <td align="right">{{ number_format($saldo_bln_lalu1 + $saldo_bln_lalu2, 2) }}</td>
            <td align="right">
                {{ number_format($saldo_sd_bulan_ini1 - $saldo_bln_lalu1 + ($saldo_sd_bulan_ini2 - $saldo_bln_lalu2), 2) }}
            </td>
            <td align="right">{{ number_format($saldo_sd_bulan_ini1 + $saldo_sd_bulan_ini2, 2) }}</td>
        </tr>
        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td colspan="4" height="16">5.4 Beban Pajak</td>
        </tr>

        @foreach ($ph as $ph)
            @php
                $saldo = $keuangan->komSaldoLB($ph);
            @endphp
            <tr class="{{ $loop->iteration % 2 == 1 ? 'row-white' : 'row-black' }}">
                <td>{{ $ph->kode_akun }}. {{ $ph->nama_akun }}</td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td align="right">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td align="right">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
            </tr>
        @endforeach

        <tr style="background: rgb(200, 200, 200);">
            <td align="left">C. Laba Rugi Setelah Taksiran Pajak (A + B) </td>
            <td align="right">{{ number_format($saldo_bln_lalu1 + $saldo_bln_lalu2, 2) }}</td>
            <td align="right">
                {{ number_format($saldo_sd_bulan_ini1 - $saldo_bln_lalu1 + ($saldo_sd_bulan_ini2 - $saldo_bln_lalu2), 2) }}
            </td>
            <td align="right">{{ number_format($saldo_sd_bulan_ini1 + $saldo_sd_bulan_ini2, 2) }}</td>
        </tr>
    </tbody>
</table>
@endsection
