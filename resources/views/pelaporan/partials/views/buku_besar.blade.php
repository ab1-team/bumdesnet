@php
    use App\Utils\Tanggal;
@endphp

@include('pelaporan.layouts.style')
<title>{{ $title }} {{ $sub_judul }}</title>

<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
    <tr>
        <td colspan="8" align="center">
            <div style="font-size: 18px;">
                <b>BUKU BESAR {{ strtoupper($account->nama_akun) }}</b>
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

<div style="width: 100%; text-align: right; font-size: 11px;">Kode Akun : {{ $kode_akun }}</div>
<table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px; table-layout: fixed;">
    <thead>
        <tr style="background: rgb(74, 74, 74); font-weight: bold; color: #fff;">
            <th height="20" align="center" width="4%">No</th>
            <th align="center" width="10%">Tanggal</th>
            <th align="center" width="8%">Ref ID.</th>
            <th align="center">Keterangan</th>
            <th align="center" width="13%">Debit</th>
            <th align="center" width="13%">Kredit</th>
            <th align="center" width="13%">Saldo</th>
            <th align="center" width="5%">Ins</th>
        </tr>
    </thead>

    @php
        $saldo_awal = 0;
    @endphp
    @php
        $saldo_awal = ($saldo_awal_tahun?->kredit ?? 0) - ($saldo_awal_tahun?->debit ?? 0);
        if ($account->jenis_mutasi != 'kredit') {
            $saldo_awal = ($saldo_awal_tahun->debit ?? 0) - ($saldo_awal_tahun->kredit ?? 0);
        }
    @endphp

    <tbody>
        <tr style="background: rgb(209, 209, 209);">
            <td></td>
            <td align="center">01/01/{{ $tahun }}</td>
            <td></td>
            <td>Komulatif Transaksi Awal Tahun {{ $tahun }}</td>
            <td align="right">{{ number_format($saldo_awal_tahun->debit ?? 0, 2) }}</td>
            <td align="right">{{ number_format($saldo_awal_tahun->kredit ?? 0, 2) }}</td>
            <td align="right">{{ number_format($saldo_awal ?? 0, 2) }}</td>
            <td></td>
        </tr>

        @php
            $kom_trx_bulan_lalu_debit = $saldo_bulan_lalu->debit ?? 0;
            $kom_trx_bulan_lalu_kredit = $saldo_bulan_lalu->kredit ?? 0;

            // Jika bulan adalah Januari, set nilai ke 0
            if ($bulan - 1 == 0) {
                $kom_trx_bulan_lalu_debit = 0;
                $kom_trx_bulan_lalu_kredit = 0;
            }

            // Hitung saldo bulan lalu
            $saldo_bulan_lalu = $kom_trx_bulan_lalu_kredit - $kom_trx_bulan_lalu_debit;
            if ($account->jenis_mutasi != 'kredit') {
                $saldo_bulan_lalu = $kom_trx_bulan_lalu_debit - $kom_trx_bulan_lalu_kredit;
            }

            // Komulatif bulan lalu
            $kom_bulan_lalu = ($saldo_awal ?? 0) + $saldo_bulan_lalu;
        @endphp

        <tr style="background: rgb(209, 209, 209);">
            <td></td>
            <td align="center">01/{{ $bulan }}/{{ $tahun }}</td>
            <td></td>
            <td>Komulatif Transaksi s/d Bulan Lalu</td>
            <td align="right">{{ number_format($kom_trx_bulan_lalu_debit, 2) }}</td>
            <td align="right">{{ number_format($kom_trx_bulan_lalu_kredit, 2) }}</td>
            <td align="right">{{ number_format($kom_bulan_lalu, 2) }}</td>
            <td></td>
        </tr>

        @php
            $saldo_akun = $kom_bulan_lalu;
            $total_debit = 0;
            $total_kredit = 0;

            $total_debit_sampai_dengan = 0;
            $total_kredit_sampai_dengan = 0;
            $total_komulatif_debit = $saldo_awal_tahun->debit ?? 0;
            $total_komulatif_kredit = $saldo_awal_tahun->kredit ?? 0;

            $nomor = 1;
        @endphp

        @if ($transactions && count($transactions) > 0)
            @foreach ($transactions as $transaction)
                @php
                    $debit = $transaction->total ?? 0;
                    $kredit = 0;

                    if ($transaction->rekening_kredit == $account->id) {
                        $debit = 0;
                        $kredit = $transaction->total ?? 0;
                    }

                    $saldo_transaksi = $kredit - $debit;
                    if ($account->jenis_mutasi != 'kredit') {
                        $saldo_transaksi = $debit - $kredit;
                    }

                    $saldo_akun += $saldo_transaksi;
                    $total_debit += $debit;
                    $total_kredit += $kredit;

                    $total_debit_sampai_dengan = $total_debit + $kom_trx_bulan_lalu_debit;
                    $total_kredit_sampai_dengan = $total_kredit + $kom_trx_bulan_lalu_kredit;

                    $total_komulatif_debit = ($saldo_awal_tahun->debit ?? 0) + $total_debit_sampai_dengan;
                    $total_komulatif_kredit = ($saldo_awal_tahun->kredit ?? 0) + $total_kredit_sampai_dengan;
                @endphp

                <tr class="{{ $loop->iteration % 2 == 1 ? 'row-white' : 'row-black' }}">
                    <td align="center">{{ $nomor }}</td>
                    <td align="center">{{ Tanggal::tglIndo($transaction->tgl_transaksi) }}</td>
                    <td>{{ $transaction->id }}</td>
                    <td>{{ $transaction->keterangan }}</td>
                    <td align="right">{{ number_format($debit, 2) }}</td>
                    <td align="right">{{ number_format($kredit, 2) }}</td>
                    <td align="right">{{ number_format($saldo_akun, 2) }}</td>
                    <td></td>
                </tr>

                @php
                    $nomor++;
                @endphp
            @endforeach
        @else
            <tr>
                <td colspan="8" align="center">0</td>
            </tr>
        @endif

        <tr>
            <td colspan="8" style="padding: 0 !important;">
                <table border="0" width="100%" cellspacing="0" cellpadding="0" style="table-layout: fixed;">
                    <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                        <td>Total Transaksi</td>
                        <td align="right" width="13%">{{ number_format($total_debit, 2) }}</td>
                        <td align="right" width="13%">{{ number_format($total_kredit, 2) }}</td>
                        <td align="center" rowspan="3" width="18%">{{ number_format($saldo_akun, 2) }}</td>
                    </tr>
                    <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                        <td>Total Transaksi sampai dengan</td>
                        <td align="right">{{ number_format($total_debit_sampai_dengan, 2) }}</td>
                        <td align="right">{{ number_format($total_kredit_sampai_dengan, 2) }}</td>
                    </tr>
                    <tr style="background: rgb(230, 230, 230); font-weight: bold;">
                        <td>Total Transaksi Komulatif sampai dengan Tahun</td>
                        <td align="right">{{ number_format($total_komulatif_debit, 2) }}</td>
                        <td align="right">{{ number_format($total_komulatif_kredit, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
</table>
