@extends('pelaporan.layouts.base')

@section('content')

<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <tr>
        <td colspan="3" align="center">
            <div style="font-size: 18px;">
                <b>NERACA SALDO</b>
            </div>
            <div style="font-size: 16px;">
                <b>{{ strtoupper($sub_judul) }}</b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="3" height="5"></td>
    </tr>
</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <thead>
        <tr style="background: rgb(74, 74, 74); font-weight: bold; color: #fff;">
            <th rowspan="2" class="t l b" width="40%" height="30">&nbsp;Rekening</th>
            <th colspan="2" class="t l b" width="20%">Neraca Saldo</th>
            <th colspan="2" class="t l b" width="20%">Laba Rugi</th>
            <th colspan="2" class="t l b r" width="20%">Neraca</th>
        </tr>
        <tr style="background: rgb(74, 74, 74); font-weight: bold; color: #fff;">
            <th class="t l b" width="10%">Debit</th>
            <th class="t l b" width="10%">Kredit</th>
            <th class="t l b" width="10%">Debit</th>
            <th class="t l b" width="10%">Kredit</th>
            <th class="t l b" width="10%">Debit</th>
            <th class="t l b r" width="10%">Kredit</th>
        </tr>
    </thead>

    <tbody>
        @php
            $jumlah_saldo_debit = 0;
            $jumlah_saldo_kredit = 0;
            $jumlah_saldo_laba_rugi_debit = 0;
            $jumlah_saldo_laba_rugi_kredit = 0;
            $jumlah_saldo_neraca_debit = 0;
            $jumlah_saldo_neraca_kredit = 0;
            $saldo_pendapatan = 0;
            $saldo_beban = 0;
        @endphp

        @foreach ($accounts as $rek)
            @php
                $debit = 0;
                $kredit = 0;
                foreach ($rek->amount as $amount) {
                    $debit += $amount->debit;
                    $kredit += $amount->kredit;
                }

                $saldo_debit = 0;
                $saldo_kredit = $kredit - $debit;
                if ($rek->jenis_mutasi != 'kredit') {
                    $saldo_debit = $debit - $kredit;
                    $saldo_kredit = 0;
                }

                $saldo_neraca_debit = 0;
                $saldo_neraca_kredit = 0;
                if ($rek->lev1 <= '3') {
                    $saldo_neraca_debit = $saldo_debit;
                    $saldo_neraca_kredit = $saldo_kredit;
                }

                $saldo_laba_rugi_debit = 0;
                $saldo_laba_rugi_kredit = 0;
                if ($rek->lev1 >= '4') {
                    $saldo_laba_rugi_debit = $saldo_debit;
                    $saldo_laba_rugi_kredit = $saldo_kredit;
                }

                if ($rek->lev1 == '4') {
                    $saldo_pendapatan += $rek->jenis_mutasi == 'kredit' ? $saldo_kredit : $saldo_debit;
                }

                if ($rek->lev1 == '5') {
                    $saldo_beban += $rek->jenis_mutasi == 'kredit' ? $saldo_kredit : $saldo_debit;
                }

                $jumlah_saldo_debit += $saldo_debit;
                $jumlah_saldo_kredit += $saldo_kredit;
                $jumlah_saldo_laba_rugi_debit += $saldo_laba_rugi_debit;
                $jumlah_saldo_laba_rugi_kredit += $saldo_laba_rugi_kredit;
                $jumlah_saldo_neraca_debit += $saldo_neraca_debit;
                $jumlah_saldo_neraca_kredit += $saldo_neraca_kredit;

                $bg = 'rgb(230, 230, 230)';
                if ($loop->iteration % 2 == 0) {
                    $bg = 'rgb(255, 255, 255)';
                }
            @endphp
            <tr style="background-color: {{ $bg }};">
                <td class="t l b" align="left" height="15">
                    {{ trim($rek->kode_akun . '. ' . $rek->nama_akun) }}
                </td>
                <td class="t l b" align="right">
                    {{ $saldo_debit < 0 ? '(' . number_format($saldo_debit, 2) . ')' : number_format($saldo_debit, 2) }}
                </td>
                <td class="t l b" align="right">
                    {{ $saldo_kredit < 0 ? '(' . number_format($saldo_kredit, 2) . ')' : number_format($saldo_kredit, 2) }}
                </td>
                <td class="t l b" align="right">
                    {{ $saldo_laba_rugi_debit < 0
                        ? '(' . number_format($saldo_laba_rugi_debit, 2) . ')'
                        : number_format($saldo_laba_rugi_debit, 2) }}
                </td>
                <td class="t l b" align="right">
                    {{ $saldo_laba_rugi_kredit < 0
                        ? '(' . number_format($saldo_laba_rugi_kredit, 2) . ')'
                        : number_format($saldo_laba_rugi_kredit, 2) }}
                </td>
                <td class="t l b" align="right">
                    {{ $saldo_neraca_debit < 0
                        ? '(' . number_format($saldo_neraca_debit, 2) . ')'
                        : number_format($saldo_neraca_debit, 2) }}
                </td>
                <td class="t l b r" align="right">
                    {{ $saldo_neraca_kredit < 0
                        ? '(' . number_format($saldo_neraca_kredit, 2) . ')'
                        : number_format($saldo_neraca_kredit, 2) }}
                </td>
            </tr>
        @endforeach

        @php
            $surplus_defisit = $saldo_pendapatan - $saldo_beban;
        @endphp

        <tr style="background: rgb(110, 110, 110); color: #fff; font-weight: bold;">
            <td class="t l b" align="center" height="20">Surplus/Defisit</td>
            <td class="t l b"></td>
            <td class="t l b"></td>
            <td class="t l b" align="right">
                {{ $surplus_defisit < 0 ? '(' . number_format($surplus_defisit, 2) . ')' : number_format($surplus_defisit, 2) }}
            </td>
            <td class="t l b"></td>
            <td class="t l b"></td>
            <td class="t l b r" align="right">
                {{ $surplus_defisit < 0 ? '(' . number_format($surplus_defisit, 2) . ')' : number_format($surplus_defisit, 2) }}
            </td>
        </tr>
        <tr style="background: rgb(167, 167, 167); font-weight: bold;">
            <td class="t l b" align="center" height="20">Jumlah</td>
            <td class="t l b" align="right">
                {{ $jumlah_saldo_debit < 0
                    ? '(' . number_format($jumlah_saldo_debit, 2) . ')'
                    : number_format($jumlah_saldo_debit, 2) }}
            </td>
            <td class="t l b" align="right">
                {{ $jumlah_saldo_kredit < 0
                    ? '(' . number_format($jumlah_saldo_kredit, 2) . ')'
                    : number_format($jumlah_saldo_kredit, 2) }}
            </td>
            <td class="t l b" align="right">
                {{ $jumlah_saldo_laba_rugi_debit + $surplus_defisit < 0
                    ? '(' . number_format($jumlah_saldo_laba_rugi_debit + $surplus_defisit, 2) . ')'
                    : number_format($jumlah_saldo_laba_rugi_debit + $surplus_defisit, 2) }}
            </td>
            <td class="t l b" align="right">
                {{ $jumlah_saldo_laba_rugi_kredit < 0
                    ? '(' . number_format($jumlah_saldo_laba_rugi_kredit, 2) . ')'
                    : number_format($jumlah_saldo_laba_rugi_kredit, 2) }}
            </td>
            <td class="t l b" align="right">
                {{ $jumlah_saldo_neraca_debit < 0
                    ? '(' . number_format($jumlah_saldo_neraca_debit, 2) . ')'
                    : number_format($jumlah_saldo_neraca_debit, 2) }}
            </td>
            <td class="t l b r" align="right">
                {{ $jumlah_saldo_neraca_kredit + $surplus_defisit < 0
                    ? '(' . number_format($jumlah_saldo_neraca_kredit + $surplus_defisit, 2) . ')'
                    : number_format($jumlah_saldo_neraca_kredit + $surplus_defisit, 2) }}
            </td>
        </tr>
    </tbody>
</table>
@endsection
