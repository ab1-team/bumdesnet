@extends('pelaporan.layouts.base')

@section('content')

<table border="0" style="font-size: 12px; line-height: 1.5;">
    <tr>
        <td width="5%">Nomor</td>
        <td width="50%">: ______________________</td>
        <td width="45%" align="right">{{ $nama_desa }}, {{ $tgl }}</td>
    </tr>
    <tr>
        <td>Lampiran</td>
        <td>: 1 Bendel</td>
    </tr>
    <tr>
        <td>Perihal</td>
        <td>: Laporan Keuangan</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        {{-- <td style="padding-left: 8px;">
            &nbsp; <u>Sampai Dengan {{ $sub_judul }}</u>
        </td> --}}
    </tr>
    <tr>
        <td colspan="3" height="15"></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="2" align="left" style="padding-left: 8px;">
            <div><b>Kepada Yth.</b></div>
            <div><b>Kepala Desa {{ $nama_desa }}</b></div>
            <div><b></b></div>
        </td>
    </tr>
    <tr>
        <td colspan="3" height="15"></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td colspan="2" style="padding-left: 8px; text-align: justify;">
            <div>Dengan Hormat,</div>
            <div>
                Bersama ini kami sampaikan Laporan Keuangan {{ $nama }} sampai dengan
                {{ $sub_judul }} sebagai berikut:
                <ol>
                    <li>Laporan Neraca</li>
                    <li>Laporan Rugi/Laba</li>
                    <li>Laporan Arus Kas</li>
                    <li>Laporan Perubahan Modal</li>
                    <li>Catatan Atas Laporan Keuangan (CALK)</li>
                </ol>
            </div>
            <div>
                Demikian laporan kami sampaikan, atas perhatiannya kami ucapkan terima kasih.
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="3" height="15"></td>
    </tr>
    <tr>
        <td colspan="2"></td>
        <td align="center">
            <div>{{ $nama }}</div>
            <div>{{ $jabatan->nama_jabatan ?? '-' }}</div>
            <br>
            <br>
            <br>
            <br>
            <div>
                <b>{{ $direktur->nama ?? '-' }}</b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <div>
                Tembusan :
                <ol>
                    <li>Arsip</li>
                </ol>
            </div>
        </td>
    </tr>
</table>
@endsection
