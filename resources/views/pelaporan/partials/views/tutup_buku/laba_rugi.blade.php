@php
    use App\Utils\Keuangan;
    $keuangan = new Keuangan();

    $saldo_bln_lalu1 = 0;
    $saldo_sd_bulan_ini1 = 0;
    $saldo_bln_lalu2 = 0;
    $saldo_sd_bulan_ini2 = 0;
@endphp

@include('pelaporan.layouts.style')
<title>{{ $title }} {{ $sub_judul }}</title>

<table border="0" width="100%">
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

<table border="0" width="100%">
    <thead>
        <tr style="background: rgb(232, 232, 232); font-weight: bold; font-size: 12px;">
            <th class="t l b" align="center" width="55%">Rekening</th>
            <th class="t l b" align="center" width="15%">s.d. Bulan Lalu</th>
            <th class="t l b" align="center" width="15%">Bulan Ini</th>
            <th class="t l b r" align="center" width="15%">s.d. Bulan Ini</th>
        </tr>
    </thead>

    <tbody>
        <tr style="background: rgb(200, 200, 200); font-weight: bold; text-transform: uppercase;">
            <td class="t l b r" colspan="4" height="14">4. Pendapatan</td>
        </tr>
        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td class="t l b r" colspan="4" height="14">4.1.00.00. Pendapatan Usaha</td>
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
            <tr class="{{ $loop->iteration % 2 == 1 ? 'bg-red' : 'bg-white' }}">
                <td class="t l b">{{ $p->kode_akun }}. {{ $p->nama_akun }}</td>
                <td class="t l b" align="center">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td class="t l b" align="center">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td class="t l b r" align="center">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
            </tr>
            @php
                // Menambahkan saldo ke total
                $total_sd_bulan_lalu += $saldo['saldo_sd_bulan_lalu'];
                $total_bulan_ini += $saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'];
                $total_sd_bulan_ini += $saldo['saldo_sd_bulan_ini'];
            @endphp
            @php
                $saldo_bln_lalu1 += $saldo['saldo_sd_bulan_lalu'];
                $saldo_sd_bulan_ini1 += $saldo['saldo_sd_bulan_ini'];
            @endphp
        @endforeach

        <tr style="background: rgb(150, 150, 150); font-weight: bold;">
            <td class="t l b" align="left">Jumlah Pendapatan:</td>
            <td class="t l b" align="center">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td class="t l b" align="center">{{ number_format($total_bulan_ini, 2) }}</td>
            <td class="t l b r" align="center">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>

        <tr style="background: rgb(200, 200, 200); font-weight: bold; text-transform: uppercase;">
            <td class="t l b r" colspan="4" height="16">5. Beban</td>
        </tr>
        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td class="t l b r" colspan="4" height="14">5.1.00.00. Beban Usaha</td>
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
            <tr class="{{ $loop->iteration % 2 == 1 ? 'bg-r' : 'bg-f' }}">
                <td class="t l b">{{ $b->kode_akun }}. {{ $b->nama_akun }}</td>
                <td class="t l b" align="center">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td class="t l b" align="center">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td class="t l b r" align="center">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
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

        <tr style="background: rgb(150, 150, 150); font-weight: bold;">
            <td class="t l b" align="left">Jumlah 5.1.00.00. Beban Usaha</td>
            <td class="t l b" align="center">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td class="t l b" align="center">{{ number_format($total_bulan_ini, 2) }}</td>
            <td class="t l b r" align="center">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>
        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td class="t l b r" colspan="4" height="16">5.2.00.00. Beban Pemasaran
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
            <tr class="{{ $loop->iteration % 2 == 1 ? 'bg-k' : 'bg-p' }}">
                <td class="t l b">{{ $bp->kode_akun }}. {{ $bp->nama_akun }}</td>
                <td class="t l b" align="center">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td class="t l b" align="center">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td class="t l b r" align="center">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
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

        <tr style="background: rgb(150, 150, 150); font-weight: bold;">
            <td class="t l b" align="left">Jumlah 5.2.00.00. Beban Pemasaran</td>
            <td class="t l b" align="center">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td class="t l b" align="center">{{ number_format($total_bulan_ini, 2) }}</td>
            <td class="t l b r" align="center">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>
        <tr style="background: rgb(200, 200, 200); font-weight: bold;">
            <td class="t l b" align="left">A. Laba Rugi OPERASIONAL (Kode Akun 4.1 - 5.1 - 5.2) </td>
            <td class="t l b" align="center">{{ number_format($saldo_bln_lalu1, 2) }}</td>
            <td class="t l b" align="center">{{ number_format($saldo_sd_bulan_ini1 - $saldo_bln_lalu1, 2) }}</td>
            <td class="t l b r" align="center">{{ number_format($saldo_sd_bulan_ini1, 2) }}</td>
        </tr>

        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td class="t l b" colspan="4" height="16">4.2.00.00. Pendapatan Non Usaha</td>
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
            <tr class="{{ $loop->iteration % 2 == 1 ? 'bg-k' : 'bg-p' }}">
                <td class="t l b">{{ $pn->kode_akun }}. {{ $pn->nama_akun }}</td>
                <td class="t l b" align="center">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td class="t l b" align="center">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td class="t l b r" align="center">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
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

        <tr style="background: rgb(150, 150, 150); font-weight: bold;">
            <td class="t l b" align="left">Jumlah 4.2.00.00. Pendapatan Non Usaha</td>
            <td class="t l b" align="center">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td class="t l b" align="center">{{ number_format($total_bulan_ini, 2) }}</td>
            <td class="t l b r" align="center">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>
        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td class="t l b r" colspan="4" height="16">4.3.00.00. Pendapatan Luar biasa</td>
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
            <tr class="{{ $loop->iteration % 2 == 1 ? 'bg-k' : 'bg-p' }}">
                <td class="t l b">{{ $pendl->kode_akun }}. {{ $pendl->nama_akun }}</td>
                <td class="t l b" align="center">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td class="t l b" align="center">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td class="t l b r" align="center">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
            </tr>
            @php
                $total_sd_bulan_lalu += $saldo['saldo_sd_bulan_lalu'];
                $total_bulan_ini += $saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'];
                $total_sd_bulan_ini += $saldo['saldo_sd_bulan_ini'];
            @endphp
        @endforeach

        <tr style="background: rgb(150, 150, 150); font-weight: bold;">
            <td class="t l b" align="left">Jumlah 4.3.00.00. Pendapatan Luar Biasa</td>
            <td class="t l b" align="center">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td class="t l b" align="center">{{ number_format($total_bulan_ini, 2) }}</td>
            <td class="t l b r" align="center">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>

        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td class="t l b r" colspan="4" height="16">5.3.00.00. Beban Non Usaha</td>
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
            <tr class="{{ $loop->iteration % 2 == 1 ? 'bg-l' : 'bg-u' }}">
                <td class="t l b">{{ $bb->kode_akun }}. {{ $bb->nama_akun }}</td>
                <td class="t l b" align="center">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td class="t l b" align="center">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td class="t l b r" align="center">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
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

        <tr style="background: rgb(150, 150, 150); font-weight: bold;">
            <td class="t l b" align="left">Jumlah 5.3.00.00. Beban Non Usaha</td>
            <td class="t l b" align="center">{{ number_format($total_sd_bulan_lalu, 2) }}</td>
            <td class="t l b" align="center">{{ number_format($total_bulan_ini, 2) }}</td>
            <td class="t l b r" align="center">{{ number_format($total_sd_bulan_ini, 2) }}</td>
        </tr>
        <tr style="background: rgb(200, 200, 200); font-weight: bold;">
            <td class="t l b" align="left">B. Laba Rugi OPERASIONAL (Kode Akun 4.2 - 5.3) </td>
            <td class="t l b" align="center">{{ number_format($saldo_bln_lalu2, 2) }}</td>
            <td class="t l b" align="center">{{ number_format($saldo_sd_bulan_ini2 - $saldo_bln_lalu2, 2) }}</td>
            <td class="t l b r" align="center">{{ number_format($saldo_sd_bulan_ini2, 2) }}</td>
        </tr>
        <tr style="background: rgb(200, 200, 200); font-weight: bold;">
            <td class="t l b" align="left">C. Laba Rugi Sebelum Taksiran Pajak (A + B) </td>
            <td class="t l b" align="center">{{ number_format($saldo_bln_lalu1 + $saldo_bln_lalu2, 2) }}</td>
            <td class="t l b" align="center">
                {{ number_format($saldo_sd_bulan_ini1 - $saldo_bln_lalu1 + ($saldo_sd_bulan_ini2 - $saldo_bln_lalu2), 2) }}
            </td>
            <td class="t l b r" align="center">{{ number_format($saldo_sd_bulan_ini1 + $saldo_sd_bulan_ini2, 2) }}
            </td>
        </tr>

        <tr style="background: rgb(150, 150, 150); font-weight: bold; text-transform: uppercase;">
            <td class="t l b r" colspan="4" height="16">5.4 Beban Pajak</td>
        </tr>
        @foreach ($ph as $ph)
            @php
                $saldo = $keuangan->komSaldoLB($ph);
            @endphp
            <tr class="{{ $loop->iteration % 2 == 1 ? 'bg-l' : 'bg-u' }}">
                <td class="t l b">{{ $ph->kode_akun }}. {{ $ph->nama_akun }}</td>
                <td class="t l b" align="center">{{ number_format($saldo['saldo_sd_bulan_lalu'], 2) }}</td>
                <td class="t l b" align="center">
                    {{ number_format($saldo['saldo_sd_bulan_ini'] - $saldo['saldo_sd_bulan_lalu'], 2) }}
                </td>
                <td class="t l b r" align="center">{{ number_format($saldo['saldo_sd_bulan_ini'], 2) }}</td>
            </tr>
        @endforeach

        <tr style="background: rgb(200, 200, 200); font-weight: bold;">
            <td class="t l b" align="left">C. Laba Rugi Setelah Taksiran Pajak (A + B) </td>
            <td class="t l b" align="center">{{ number_format($saldo_bln_lalu1 + $saldo_bln_lalu2, 2) }}</td>
            <td class="t l b" align="center">
                {{ number_format($saldo_sd_bulan_ini1 - $saldo_bln_lalu1 + ($saldo_sd_bulan_ini2 - $saldo_bln_lalu2), 2) }}
            </td>
            <td class="t l b r" align="center">
                {{ number_format($saldo_sd_bulan_ini1 + $saldo_sd_bulan_ini2, 2) }}
            </td>
        </tr>
    </tbody>
</table>

