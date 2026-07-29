@extends('pelaporan.layouts.base')

@php
    use App\Utils\Tanggal;
@endphp
@section('content')
<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <tr>
        <td colspan="8" align="center">
            <div style="font-size: 18px;"><b>JURNAL TRANSAKSI</b></div>
            <div style="font-size: 16px;"><b>{{ strtoupper($sub_judul) }}</b></div>
        </td>
    </tr>
    <tr>
        <td colspan="8" height="5"></td>
    </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <thead>
        <tr style="background: rgb(74, 74, 74); font-weight: bold; color: #fff;">
            <th height="15" align="center" width="4%">No</th>
            <th align="center" width="10%">Tanggal</th>
            <th align="center" width="8%">Ref ID.</th>
            <th align="center" width="8%">Kd. Rek</th>
            <th align="center" width="35%">Keterangan</th>
            <th align="center" width="15%">Debit</th>
            <th align="center" width="15%">Kredit</th>
            <th align="center" width="5%">Ins</th>
        </tr>
    </thead>

    <tbody>
        @php
            $totalDebit = 0;
            $totalKredit = 0;
        @endphp
        @foreach ($transactions as $transaction)
            @php
                $rowClass = $transaction['nomor'] % 2 == 0 ? 'row-black' : 'row-white';
            @endphp
            <tr class="{{ $rowClass }}">
                <td height="15" align="center">{{ $transaction['nomor'] }}.</td>
                <td align="center">{{ Tanggal::tglIndo($transaction['tgl_transaksi']) }}</td>
                <td>{{ $transaction['id'] }}</td>

                <td align="center">{{ $transaction['kode_akun'] }}</td>
                <td>{{ $transaction['nama_akun'] }}</td>
                <td align="right">{{ number_format($transaction['jumlah'], 2, ',', '.') }}</td>
                <td align="right">{{ number_format(0, 2, ',', '.') }}</td>

                <td>{{ $transaction['ins'] }}</td>
            </tr>

            @foreach ($transaction['trx_kredit'] as $trx_kredit)
                <tr class="{{ $rowClass }}">
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>

                    <td align="center">{{ $trx_kredit['kode_akun'] }}</td>
                    <td>{{ $trx_kredit['nama_akun'] }}</td>
                    <td align="right">{{ number_format(0, 2, ',', '.') }}</td>
                    <td align="right">{{ number_format($trx_kredit['jumlah'], 2, ',', '.') }}</td>

                    <td>&nbsp;</td>
                </tr>
            @endforeach

            @php
                $totalDebit += $transaction['jumlah'];
                $totalKredit += $transaction['jumlah'];
            @endphp
        @endforeach

        <tr style="background: rgb(74, 74, 74); color: #fff; font-weight: bold;">
            <td height="16" colspan="5" align="center">Total</td>
            <td align="right">{{ number_format($totalDebit, 2, ',', '.') }}</td>
            <td align="right">{{ number_format($totalKredit, 2, ',', '.') }}</td>
            <td></td>
        </tr>
    </tbody>
</table>
@endsection
