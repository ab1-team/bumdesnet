@if ($laporan == 'surat_pengantar')
    <table width="100%" style="border-bottom: 1px double #000; border-width: 4px;">
        <tr>
            <td width="70">
                <img src="data:image/png;base64,{{ $logo }}" height="70" alt="{{ $nama }}">
            </td>
            <td align="center">
                <div><b>{{ strtoupper($nama) }}</b></div>
                <div>
                    <b>{{ strtoupper($alamat) }}</b>
                </div>
                <div style="font-size: 10px; color: grey;">
                    <i>{{ $nomor_usaha }}</i>
                </div>
                <div style="font-size: 10px; color: grey;">
                    <i>{{ $info }}</i>
                </div>
                <div style="font-size: 10px; color: grey;">
                    <i>{{ $email }}</i>
                </div>
            </td>
        </tr>
    </table>
@else
    <table width="100%" style="border-bottom: 1px solid grey;">
        <tr>
            <td width="30">
                <img src="data:image/png;base64,{{ $logo }}" width="40" alt="{{ $nama }}">
            </td>
            <td>
                <div style="font-size: 12px;"><b>{{ strtoupper($nama) }}</b></div>
                <div style="font-size: 12px;">
                    {{ strtoupper($alamat) }}
                </div>
            </td>
        </tr>
    </table>
    <table width="100%" style="position: relative; top: -10px;">
        <tr>
            <td>
                <span style="font-size: 8px; color: grey;">
                    <i>{{ $nomor_usaha }}</i>
                </span>
            </td>
            <td align="right">
                <span style="font-size: 8px; color: grey;">
                    <i>{{ $info }}</i>
                </span>
            </td>
        </tr>
    </table>
@endif
