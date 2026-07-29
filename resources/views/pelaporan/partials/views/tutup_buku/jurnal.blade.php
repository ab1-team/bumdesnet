@extends('pelaporan.layouts.base')

@php
    use App\Utils\Tanggal;
@endphp
@section('content')
<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <tr>
        <td colspan="8" align="center">
            <div style="font-size: 18px;">
                <b>JURNAL TUTUP BUKU</b>
            </div>
            <div style="font-size: 16px;">
                <b>{{ strtoupper($sub_judul) }}</b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="8" height="5"></td>
    </tr>
</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
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
        @foreach ($transactions as $transaction)
            @php
                $rowClass = $loop->iteration % 2 == 0 ? 'row-black' : 'row-white';
            @endphp
            <tr class="{{ $rowClass }}">
                <td height="15" rowspan="2" align="center">{{ $loop->iteration }}.</td>
                <td rowspan="2" align="center">{{ Tanggal::tglIndo($transaction->tgl_transaksi) }}</td>
                <td rowspan="2" align="left">{{ $transaction->id }}</td>
                <td align="center">{{ optional($transaction->acc_debit)->kode_akun }}</td>
                <td align="left">{{ optional($transaction->acc_debit)->nama_akun }}</td>
                <td align="right">{{ number_format($transaction->total, 2, ',', '.') }}</td>
                <td align="right">0</td>
                <td rowspan="2" align="center">&nbsp;</td>
            </tr>
            <tr class="{{ $rowClass }}">
                <td align="center">{{ optional($transaction->acc_kredit)->kode_akun }}</td>
                <td align="left">{{ optional($transaction->acc_kredit)->nama_akun }}</td>
                <td align="right">0</td>
                <td align="right">{{ number_format($transaction->total, 2, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection
