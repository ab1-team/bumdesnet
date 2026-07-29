@extends('pelaporan.layouts.base')

@section('content')

<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <tr>
        <td colspan="5" align="center">
            <div style="font-size: 18px;">
                <b>DAFTAR PERSEDIAAN</b>
            </div>
            <div style="font-size: 16px;">
                <b>{{ strtoupper($sub_judul) }}</b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="5" height="5"></td>
    </tr>
</table>

<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <thead>
        <tr style="background: rgb(74, 74, 74); font-weight: bold; color: #fff;">
            <th height="15" align="center" width="5%">No</th>
            <th align="center" width="55%">Nama Barang</th>
            <th align="center" width="10%">Stok</th>
            <th align="center" width="10%">Satuan</th>
            <th align="center" width="20%">Harga</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($products as $product)
            <tr class="{{ $loop->iteration % 2 == 1 ? 'row-white' : 'row-black' }}">
                <td height="15">{{ $loop->iteration }}.</td>
                <td>{{ $product->name }}</td>
                <td align="center">{{ $product->stok }}</td>
                <td align="center">{{ $product->unit->name ?? '-' }}</td>
                <td align="right">
                    Rp. {{ number_format($product->harga_jual ?? 0) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" align="center">Tidak ada data</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection
