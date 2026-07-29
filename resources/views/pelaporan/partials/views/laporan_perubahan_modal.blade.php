@include('pelaporan.layouts.style')
<title>{{ $title }} {{ $sub_judul }}</title>
@php
    $nomor = 1;
@endphp

<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <tr>
        <td colspan="3" align="center">
            <div style="font-size: 18px;">
                <b>LAPORAN PERUBAHAN MODAL</b>
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
            <th height="15" align="center" width="5%">No</th>
            <th align="center" width="55%">Rekening Modal</th>
            <th align="center" width="20%">&nbsp;</th>
        </tr>
    </thead>
    @php
        $total_saldo = 0;
    @endphp
    @foreach ($accounts as $rek)
        @php
            $saldo_debit = 0;
            $saldo_kredit = 0;
            foreach ($rek->amount as $amount) {
                $saldo_debit += $amount->debit;
                $saldo_kredit += $amount->kredit;
            }

            $saldo = $saldo_kredit - $saldo_debit;
            $total_saldo += $saldo;
        @endphp
        <tr>
            <td height="15" class="t l b" align="center">{{ $nomor++ }}</td>
            <td class="t l b">{{ $rek->nama_akun }}</td>
            <td class="t l b r" align="right">
                {{ $saldo < 0 ? '(' . number_format(abs($saldo), 2) . ')' : number_format($saldo, 2) }}
            </td>
        </tr>
    @endforeach
    <tr style="background: rgb(167, 167, 167); font-weight: bold;">
        <td class="t l b" colspan="2" height="20">Total Saldo</td>
        <td class="t l b r" align="right">
            {{ $total_saldo < 0 ? '(' . number_format(abs($total_saldo), 2) . ')' : number_format($total_saldo, 2) }}
        </td>
    </tr>
</table>
