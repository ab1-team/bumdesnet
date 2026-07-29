@include('pelaporan.layouts.style')
<title>{{ $title }} {{ $sub_judul }}</title>

<table border="0" width="100%">
    <tr>
        <td colspan="3" align="center">
            <div style="font-size: 18px;">
                <b>NERACA</b>
            </div>
            <div style="font-size: 16px;">
                <b>{{ strtoupper($sub_judul) }}</b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="3" height="10"></td>
    </tr>
</table>

<table border="0">
    <thead>
        <tr style="background: #c4c4c4; color: #000000;">
            <td width="10%">Kode</td>
            <td width="70%">Nama Akun</td>
            <td align="right" width="20%">Saldo</td>
        </tr>
        <tr>
            <td colspan="3" height="3"></td>
        </tr>
    </thead>

    @php
        $jumlah_liabilitas_equitas = 0;
    @endphp
    @foreach ($akun1 as $lev1)
        @php
            $saldo_akun = 0;
        @endphp
        <tr class="bold" style="background: rgb(110, 110, 110); color: #fff;">
            <td style="height: 28px;" colspan="3" align="center">
                {{ $lev1->kode_akun }}. {{ $lev1->nama_akun }}
            </td>
        </tr>

        @foreach ($lev1->akun2 as $lev2)
            <tr class="bold" style="background: rgb(167, 167, 167);">
                <td>{{ $lev2->kode_akun }}</td>
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
                        $bg = 'rgba(255, 255, 255)';
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

        <tr class="bold" style="background: rgb(167, 167, 167);">
            <td style="height: 28px;" colspan="2">Jumlah {{ $lev1->nama_akun }}</td>
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
            <table border="0">
                <tr class="bold" style="background: rgb(167, 167, 167);">
                    <td class="p-0" style="height: 28px;" width="80%" align="left">
                        Jumlah Liabilitas + Ekuitas
                    </td>
                    <td class="p-0" align="right" width="20%">
                        {{ $jumlah_liabilitas_equitas < 0 ? '(' . number_format(abs($jumlah_liabilitas_equitas), 2) . ')' : number_format($jumlah_liabilitas_equitas, 2) }}&nbsp;
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
