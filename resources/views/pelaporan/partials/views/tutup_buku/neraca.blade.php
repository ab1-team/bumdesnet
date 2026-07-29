@include('pelaporan.layouts.style')
<title>{{ $title }} {{ $sub_judul }}</title>

<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <tr>
        <td colspan="3" align="center">
            <div style="font-size: 18px;">
                <b>NERACA TUTUP BUKU</b>
            </div>
            <div style="font-size: 16px;">
                <b>{{ strtoupper($sub_judul) }}</b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="3" height="3"></td>
    </tr>
    <tr style="background: #000; color: #fff;">
        <td width="10%">&nbsp;Kode</td>
        <td width="70%">&nbsp;Nama Akun</td>
        <td align="right" width="20%">&nbsp;Saldo</td>
    </tr>
    <tr>
        <td colspan="3" height="1"></td>
    </tr>
</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    @php
        $jumlah_liabilitas_equitas = 0;
    @endphp
    @foreach ($akun1 as $lev1)
        @php
            $saldo_akun = 0;
        @endphp
        <tr style="background: rgb(74, 74, 74); color: #fff; font-weight: bold;">
            <td height="20" colspan="3" align="center">
                <b>{{ $lev1->kode_akun }}. {{ $lev1->nama_akun }}</b>
            </td>
        </tr>

        @foreach ($lev1->akun2 as $lev2)
            <tr style="background: rgb(167, 167, 167); font-weight: bold;">
                <td>{{ $lev2->kode_akun }}.</td>
                <td colspan="2">{{ $lev2->nama_akun }}</td>
            </tr>

            @foreach ($lev2->akun3 as $lev3)
                @php
                    $sum_saldo = 0;
                    foreach ($lev3->accounts as $account) {
                        $saldo_debit = 0;
                        $saldo_kredit = 0;
                        foreach ($account->amount as $amount) {
                            $saldo_debit += $amount->debit;
                            $saldo_kredit += $amount->kredit;
                        }

                        $saldo = $saldo_kredit - $saldo_debit;
                        if ($lev1->lev1 == '1') {
                            $saldo = $saldo_debit - $saldo_kredit;
                        }

                        if ($account->kode_akun == '3.2.02.01') {
                            $saldo = $surplus;
                        }

                        $sum_saldo += $saldo;
                    }

                    $saldo_akun += $sum_saldo;
                    if ($lev1->lev1 > 1) {
                        $jumlah_liabilitas_equitas += $sum_saldo;
                    }
                    $bg = 'rgb(230, 230, 230)';
                    if ($loop->iteration % 2 == 0) {
                        $bg = 'rgb(255, 255, 255)';
                    }
                @endphp
                <tr style="background-color: {{ $bg }}">
                    <td>{{ $lev3->kode_akun }}</td>
                    <td>{{ $lev3->nama_akun }}</td>
                    <td align="right">
                        {{ $sum_saldo < 0 ? '(' . number_format(abs($sum_saldo), 2) . ')' : number_format($sum_saldo, 2) }}
                    </td>
                </tr>
            @endforeach
        @endforeach

        <tr style="background: rgb(167, 167, 167); font-weight: bold;">
            <td height="20" colspan="2">Jumlah {{ $lev1->nama_akun }}</td>
            <td align="right">
                {{ $saldo_akun < 0 ? '(' . number_format(abs($saldo_akun), 2) . ')' : number_format($saldo_akun, 2) }}
            </td>
        </tr>

        <tr>
            <td colspan="3" height="3"></td>
        </tr>
    @endforeach

    <tr>
        <td colspan="3" style="padding: 0px !important;">
            <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
                <tr style="background: rgb(167, 167, 167); font-weight: bold;">
                    <td class="p-0" height="20" width="80%" align="left">
                        &nbsp;Jumlah Liabilitas + Ekuitas
                    </td>
                    <td class="p-0" align="right" width="20%">
                        {{ $jumlah_liabilitas_equitas < 0 ? '(' . number_format(abs($jumlah_liabilitas_equitas), 2) . ')' : number_format($jumlah_liabilitas_equitas, 2) }}&nbsp;
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
