@php
    if ($type == 'excel') {
        $nama_file = ucwords(str_replace('_', ' ', $laporan)) . ' (' . ucwords($tgl) . ')';

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $nama_file . '.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
    }
@endphp

<!DOCTYPE html>
<html lang="en" translate="no">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ ucwords(str_replace('_', ' ', $laporan)) }} ({{ ucwords($tgl) }})</title>
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
            box-sizing: border-box;
        }

        /* Margins handled by CompatPdf @page inject (Snappy mm → DomPDF). */
        body {
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        table {
            word-wrap: break-word;
            border-collapse: collapse;
            width: 100%;
        }

        ul,
        ol {
            margin-left: -10px;
            page-break-inside: auto !important;
        }

        header.pdf-header {
            position: fixed;
            top: -18mm;
            left: 0;
            right: 0;
            height: 16mm;
        }

        table tr th,
        table tr td,
        table tr td table.p tr td {
            padding: 2px 4px !important;
        }

        table tr td table tr td {
            padding: 0 !important;
        }

        table.p0 tr th,
        table.p0 tr td {
            padding: 0px !important;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        tr.bold td {
            font-weight: bold;
        }

        .row-white {
            background-color: #ffffff;
            color: #000;
        }

        .row-black {
            background-color: #e0e0e0;
            color: #000;
        }

        .break,
        .page-break {
            page-break-after: always;
        }

        li {
            text-align: justify;
        }

        .l {
            border-left: 1px solid #000;
        }

        .t {
            border-top: 1px solid #000;
        }

        .r {
            border-right: 1px solid #000;
        }

        .b {
            border-bottom: 1px solid #000;
        }
    </style>
</head>

<body>
    <header class="pdf-header">
        @if ($laporan == 'surat_pengantar')
            <table width="100%" style="border-bottom: 1px double #000; border-width: 4px;">
                <tr>
                    <td width="70">
                        @if ($logo)
                            <img src="{{ $logo }}" height="70" alt="{{ $nama }}">
                        @endif
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
                        @if ($logo)
                            <img src="{{ $logo }}" width="40" alt="{{ $nama }}">
                        @endif
                    </td>
                    <td>
                        <div style="font-size: 12px;"><b>{{ strtoupper($nama) }}</b></div>
                        <div style="font-size: 12px;">
                            {{ strtoupper($alamat) }}
                        </div>
                    </td>
                </tr>
            </table>
            <table width="100%">
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
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>
