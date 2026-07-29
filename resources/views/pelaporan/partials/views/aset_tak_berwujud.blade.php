@extends('pelaporan.layouts.base')

@php
    use App\Utils\Tanggal;
    use App\Utils\Inventaris;
@endphp
@section('content')
@foreach ($accounts as $acc)
    @if ($loop->iteration > 1)
        <div style="page-break-after: always"></div>
    @endif

    @include('pelaporan.partials.views.inventory.atb', [
        'Inventory' => $acc->inventory,
        'nama' => $acc->nama_akun,
    ])
@endforeach
@endsection
