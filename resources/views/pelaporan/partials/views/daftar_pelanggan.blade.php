@extends('pelaporan.layouts.base')

@php
    use App\Utils\Tanggal;
@endphp
@section('content')
@foreach ($caters as $cater)
    @php
        $filterInstalasi = $cater->installations->filter(fn($ins) => $ins->usage->count() > 0);
        if ($filterInstalasi->isEmpty()) {
            continue;
        }
    @endphp

    @if ($loop->iteration > 1)
        <div class="break"></div>
    @endif

    <table border="0" width="100%">
        <tr>
            <td colspan="3" align="center">
                <div style="font-size: 18px; font-weight: bold;">
                    {{ strtoupper('Daftar Pelanggan ') }}
                </div>
                <div style="font-size: 16px; font-weight: bold;">{{ strtoupper($sub_judul) }}</div>
            </td>
        </tr>
        <tr>
            <td colspan="3" height="10"></td>
        </tr>
    </table>
    <table border="0" width="100%" style="margin-top: 0; font-size: 12px;">
        <tr>
            <td style="text-align: right; font-weight: bold;">
                Marketing : {{ $cater->nama ?? '-' }}
            </td>
        </tr>
    </table>
    <table border="0" width="100%">
        <thead>
            <tr style="background: rgb(74, 74, 74); font-weight: bold; color: #fff;">
                <th class="t l b" width="3%" height="30">No</th>
                <th class="t l b" width="12%">No.Induk</th>
                <th class="t l b" width="8%">Tgl Pasang</th>
                <th class="t l b" width="20%">Nama</th>
                <th class="t l b" width="14%">Nik</th>
                <th class="t l b" width="25%">Alamat</th>
                <th class="t l b" width="10%">No. Telp</th>
                <th class="t l b r" width="8%">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $data_desa = [];
                $section = '';
            @endphp
            @forelse ($filterInstalasi as $ins)
                @if (!in_array($ins->desa, $data_desa))
                    <tr class="bold">
                        <td class="t l b r" colspan="8" height="25">
                            Desa {{ $ins->village->nama }} Dusun {{ $ins->village->dusun }}
                        </td>
                    </tr>

                    @php
                        $data_desa[] = $ins->desa;
                        $section = $ins->desa;

                        $nomor = 1;
                    @endphp
                @endif

                <tr>
                    <td class="t l b" align="center">{{ $nomor++ }}</td>
                    <td class="t l b" align="center">
                        {{ $ins->kode_instalasi }}.{{ substr($ins->package->kelas, 0, 1) }}
                    </td>
                    <td class="t l b" align="center">{{ Tanggal::tglIndo($ins->pasang) }}</td>
                    <td class="t l b">{{ $ins->customer->nama }}</td>
                    <td class="t l b" align="center">{{ $ins->customer->nik }}</td>
                    <td class="t l b">{{ $ins->customer->alamat }}</td>
                    <td class="t l b" align="center">{{ $ins->customer->hp }}</td>
                    <td class="t l b r" align="center">
                        @if ($ins->status == 'R' || $ins->status == '0')
                            Permohonan
                        @elseif ($ins->status == 'I')
                            Pasang
                        @elseif ($ins->status == 'A')
                            Aktif
                        @elseif ($ins->status == 'B')
                            Blokir
                        @elseif ($ins->status == 'C')
                            Cabut
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" align="center" class="t l b r">
                        Tidak ada data pelanggan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endforeach
@endsection
