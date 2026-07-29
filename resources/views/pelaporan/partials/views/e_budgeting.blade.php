@extends('pelaporan.layouts.base')

@php
    use App\Utils\Tanggal;
@endphp
@section('content')
<table border="0" width="100%">
    <tr>
        <td colspan="3" align="center">
            <div style="font-size: 18px;">
                <b>LAPORAN PENGGUNAAN DANA (E-BUDGETING)</b>
            </div>
            <div style="font-size: 16px;">
                <b style="text-transform: uppercase;">Triwulan Tahun Anggaran {{ $thn }}</b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="3" height="5"></td>
    </tr>
</table>


<table width="100%">
    <thead>
        <tr style="background: rgb(74, 74, 74); font-weight: bold; font-size: 12px; color: #fff; border: 1px solid black;">
            <th rowspan="2" width="20%" class="t l b">Rekening</th>
            <th rowspan="2" width="10%" class="t l b">Komulatif Bulan Lalu</th>
            @foreach ($bulan_tampil as $bt)
                <th colspan="2" class="t l b" width="16%" height="16">
                    {{ Tanggal::namaBulan(date('Y') . '-' . $bt . '-01') }}
                </th>
            @endforeach
            <th rowspan="2" width="10%" class="t l b r">Total</th>
        </tr>
        <tr style="background: rgb(74, 74, 74); font-weight: bold; font-size: 12px; color: #fff; border: 1px solid black;">
            <th width="6%" class="t l b">Rencana</th>
            <th width="6%" class="t l b">Realisasi</th>
            <th width="6%" class="t l b">Rencana</th>
            <th width="6%" class="t l b">Realisasi</th>
            <th width="6%" class="t l b">Rencana</th>
            <th width="6%" class="t l b r">Realisasi</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($e_budgeting as $eb)
            @if ($eb['is_header'])
                <tr style="background: rgb(200, 200, 200); font-weight: bold; border: 1px solid black;">
                    <td colspan="9" align="left" class="t l b r">
                        <b>{{ $eb['nama'] }}</b>
                    </td>
                </tr>
            @else
                <tr class="t l b">
                    <td class="t l b">{{ $eb['nama'] }}</td>
                    <td align="right" class="t l b">
                        {{ number_format($eb['komulatif'], 2) }}</td>
                    <td align="right" class="t l b">
                        {{ number_format($eb['rencana1'], 2) }}</td>
                    <td align="right" class="t l b">
                        {{ number_format($eb['realisasi1'], 2) }}</td>
                    <td align="right" class="t l b">
                        {{ number_format($eb['rencana2'], 2) }}</td>
                    <td align="right" class="t l b">
                        {{ number_format($eb['realisasi2'], 2) }}</td>
                    <td align="right" class="t l b">
                        {{ number_format($eb['rencana3'], 2) }}</td>
                    <td align="right" class="t l b">
                        {{ number_format($eb['realisasi3'], 2) }}</td>
                    <td align="right" class="t l b r">
                        {{ number_format($eb['total'], 2) }}
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>
@endsection
