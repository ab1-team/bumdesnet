@extends('pelaporan.layouts.base')

@section('content')

<table border="0" width="100%" cellspacing="0" cellpadding="0">
    <tr>
        <td colspan="3" align="center">
            <div style="font-size: 18px;">
                <b>CATATAN ATAS LAPORAN KEUANGAN</b>
            </div>
            <div style="font-size: 18px; text-transform: uppercase;">
                <b>{{ $nama }}</b>
            </div>
            <div style="font-size: 16px;">
                <b>{{ strtoupper($sub_judul) }}</b>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="3" height="5"></td>
    </tr>
</table>

<ol style="list-style: upper-alpha; font-size: 12px;">
    <li>
        <div style="text-transform: uppercase;">Gambaran Umum</div>
        <div style="text-align: justify">
            {{ $nama }} adalah Badan Usaha yang didirikan dari transformasi UPK PNPM-MPd
            dengan
            kegiatan usaha Dana Bergulir Masyarakat (DBM) melalui produk usahanya SPP dan UEP. Dalam
            perkembangannya sebagian dari laba DBM UPK PNPM-MPd kemudian sebelum
            ditetapkannya PP 11 tahun 2021 telah digunakan untuk membentuk unit usaha Perdagangan dan
            Produksi*.
        </div>
        <p style="text-align: justify">
            Bumdesma Lkd setelah didirikan sesuai ketentuan PP 11 tahun 2021 dilaksanakan transformasi
            sesuai Permendesa
            PDTT Nomor 15 tahun 2021 yang meliputi pengalihan aset, pengalihan kelembagaan, pengalihan
            personil, dan
            pengalihan kegiatan usaha. Modal awal Pendirian Bumdesma Lkd sesuai dengan ketentuan tersebut
            adalah berasal
            dari keseluruhan pengalihan keseluruhan aset DBM Eks PNPM MPd (Permendesa PDTT 15 tahun 2021
            Pasal 5) yang
            dicatat sebagai Ekuitas Bumdesma Lkd ditambah dengan Penyertaan Modal Desa. Yang kemudian
            didalam laporan
            posisi keuangan ekuitas yang berasal dari Aset DBM Eks PNPM Mpd disebut Modal Masyarakat Desa
            (Permendesa
            PDTT 15 tahun 2021 Pasal 6).
        </p>
        <p style="text-align: justify">
            Sesuai dengan ketentuan UU Cipta Kerja No 11 Tahun 2020 bahwa Menetapkan status Badan hukum BUM
            Desa pada
            ketentuan Pasal 117 "bahwa Badan Usaha Milik Desa yang selanjutnya disebut BUM Desa adalah Badan
            hukum yang
            didirikan oleh desa dan atau bersama desa-desa guna mengelola usaha, memanfaatkan aset,
            mengembangkan
            investasi dan produktivitas, menyediakan jasa pelayanan, dan atau jenis usaha lainnya untuk
            sebesar-besarnya
            kesejahteraan masyarakat desa." Status inilah yang menjadi dasar hukum pelaksanaan usaha
            didirikan dengan
            kegiatan Usaha Utama DBM.
        </p>
        <p style="text-align: justify">
            {{ $nama }} didirikan di {{ $alamat }} berdasarkan PERATURAN BERSAMA
            KEPALA DESA
            NOMOR {{ $peraturan_desa ?? ($nomor_usaha ?? '-') }} dan mendapatkan Sertifikat Badan Hukum dari Menteri Hukum dan Hak
            Asasi Manusia
            No. {{ $nomor_usaha }} . {{ $nama }}
            menjalankan usaha
            pinjaman Dana Bergulir Masyarakat yang masuk dalam kategori usaha mikrofinance dan berdomisili
            di {{ $alamat }}
            dengan perangkat organisasi sebagai berikut:
        </p>

        <table border="1" width="100%" cellspacing="0" cellpadding="0" style="font-size: 12px;">
            <tr>
                <td width="35%"></td>
                <td width="2%">:</td>
                <td>
                    ......................................
                </td>
            </tr>

            <tr>
                <td></td>
                <td>:</td>
                <td>
                </td>
            </tr>
            <tr>
                <td> </td>
                <td>:</td>
                <td>
                </td>
            </tr>
            <tr>
                <td> </td>
                <td>:</td>
                <td>
                </td>
            </tr>
        </table>
    </li>
    <li style="margin-top: 12px;">
        <div style="text-transform: uppercase;">
            Ikhtisar Kebijakan Akutansi
        </div>
        <ol>
            <li>
                Pernyataan Kepatuhan
                <ol style="list-style: lower-alpha;">
                    <li>
                        Laporan keuangan disusun menggunakan Standar Akuntansi Keuangan
                        Perusahaan Jasa Keuangan
                    </li>
                    <li>Dasar Penyusunan Kepmendesa 136 Tahun 2022</li>
                    <li>
                        Dasar penyusunan laporan keuangan adalah biaya historis dan
                        menggunakan asumsi dasar akrual. Mata uang penyajian yang digunakan untuk menyusun laporan
                        keuangan ini adalah Rupiah.
                    </li>
                </ol>
            </li>
            <li>
                Piutang Usaha
                <ol style="list-style: lower-alpha;">
                    <li>
                        Piutang usaha disajikan sebesar jumlah saldo pinjaman dikurangi
                        dengan cadangan kerugian pinjaman
                    </li>
                </ol>
            </li>
            <li>
                Aset Tetap (berwujud dan tidak berwujud)
                <ol style="list-style: lower-alpha">
                    <li>
                        Aset tetap dicatat sebesar biaya perolehannya jika aset
                        tersebut dimiliki secara hukum oleh Bumdesma Lkd Aset
                        tetap disusutkan menggunakan metode garis lurus tanpa nilai residu.
                    </li>
                </ol>
            </li>
            <li>
                Pengakuan Pendapatan dan Beban
                <ol style="list-style: lower-alpha;">
                    <li>
                        Jasa piutang kelompok dan lembaga lain yang sudah memasuki
                        jatuh tempo pembayaran diakui sebagai pendapatan meskipun tidak diterbitkan kuitansi sebagai
                        bukti pembayaran jasa piutang. Sedangkan denda keterlambatan pembayaran/pinalti diakui
                        sebagai pendapatan pada saat diterbitkan kuitansi pembayaran.
                    </li>
                    <li>
                        Adapun kewajiban bayar atas kebutuhan operasional, pemasaran
                        maupun non operasional pada suatu periode operasi tertentu sebagai akibat
                        telah menikmati manfaat/menerima fasilitas, maka hal tersebut sudah wajib diakui
                        sebagai beban meskipun belum diterbitkan kuitansi pembayaran.
                    </li>
                </ol>
            </li>
            <li>
                Pajak Penghasilan
                <ol style="list-style: lower-alpha;">
                    <li>
                        Pajak Penghasilan mengikuti ketentuan perpajakan yang berlaku di Indonesia
                    </li>
                </ol>
            </li>
        </ol>
    </li>
    <li style="margin-top: 12px;">
        <div style="text-transform: uppercase;">
            Informasi Tambahan Laporan Keuangan
        </div>
        <table border="0">
            <thead>
                <tr style="background: #000; color: #fff;">
                    <td width="10%">&nbsp;Kode</td>
                    <td width="70%">&nbsp;Nama Akun</td>
                    <td align="right" width="20%">&nbsp;Saldo</td>
                </tr>
                <tr>
                    <td colspan="3" height="3"></td>
                </tr>
            </thead>

            @php
                $jumlah_liabilitas_equitas = 0;
            @endphp
            
            @foreach ($akun1 as $lev1)
                @php
                    $saldo_akun = 0;
                @endphp
                
                {{-- Level 1: rgb(74, 74, 74) --}}
                <tr style="background: rgb(74, 74, 74); color: #fff; font-weight: bold;">
                    <td height="20" colspan="3" align="center">
                        {{ $lev1->kode_akun }}. {{ $lev1->nama_akun }}
                    </td>
                </tr>

                @foreach ($lev1->akun2 as $lev2)
                    {{-- Level 2: rgb(167, 167, 167) --}}
                    <tr class="bold" style="background: rgb(167, 167, 167);">
                        <td>{{ $lev2->kode_akun }}</td>
                        <td colspan="2">{{ $lev2->nama_akun }}</td>
                    </tr>

                    @foreach ($lev2->akun3 as $lev3)
                        @php
                            $sum_saldo = 0;
                            
                            // Hitung total saldo untuk level 3 ini
                            foreach ($lev3->accounts as $account) {
                                $saldo_debit = 0;
                                $saldo_kredit = 0;
                                
                                foreach ($account->amount as $amount) {
                                    $saldo_debit += $amount->debit;
                                    $saldo_kredit += $amount->kredit;
                                }

                                $saldo = $saldo_kredit - $saldo_debit;
                                if ($lev1->lev1 == '1') {
                                    $saldo = $saldo_debit - $saldo_kredit;
                                }

                                if ($account->kode_akun == '3.2.02.01') {
                                    $saldo = $surplus;
                                }
                                
                                $sum_saldo += $saldo;
                            }
                            
                            // Tambahkan ke total level 1
                            $saldo_akun += $sum_saldo;
                            if ($lev1->lev1 > 1) {
                                $jumlah_liabilitas_equitas += $sum_saldo;
                            }
                        @endphp
                        
                        {{-- Level 3: rgb(210, 210, 210) --}}
                        <tr class="bold" style="background: rgb(210, 210, 210);">
                            <td>{{ $lev3->kode_akun }}</td>
                            <td>{{ $lev3->nama_akun }}</td>
                            <td align="right">
                                {{ $sum_saldo < 0 ? '(' . number_format(abs($sum_saldo), 2) . ')' : number_format($sum_saldo, 2) }}
                            </td>
                        </tr>

                        @foreach ($lev3->accounts as $account)
                            @php
                                $saldo_debit = 0;
                                $saldo_kredit = 0;
                                
                                foreach ($account->amount as $amount) {
                                    $saldo_debit += $amount->debit;
                                    $saldo_kredit += $amount->kredit;
                                }

                                $saldo = $saldo_kredit - $saldo_debit;
                                if ($lev1->lev1 == '1') {
                                    $saldo = $saldo_debit - $saldo_kredit;
                                }

                                if ($account->kode_akun == '3.2.02.01') {
                                    $saldo = $surplus;
                                }
                            @endphp
                            
                            {{-- Level Account: rgb(255, 255, 255) --}}
                            <tr style="background: rgb(255, 255, 255);">
                                <td>{{ $account->kode_akun }}</td>
                                <td>{{ $account->nama_akun }}</td>
                                <td align="right">
                                    {{ $saldo < 0 ? '(' . number_format(abs($saldo), 2) . ')' : number_format($saldo, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach

                {{-- Jumlah Level 1: rgb(167, 167, 167) --}}
                <tr class="bold" style="background: rgb(167, 167, 167);">
                    <td style="height: 28px;" colspan="2">Jumlah {{ $lev1->nama_akun }}</td>
                    <td align="right">
                        {{ $saldo_akun < 0 ? '(' . number_format(abs($saldo_akun), 2) . ')' : number_format($saldo_akun, 2) }}
                    </td>
                </tr>

                <tr>
                    <td colspan="3" height="3"></td>
                </tr>
            @endforeach

            <tr>
                <td colspan="3" style="padding: 0px !important;">
                    <table border="0">
                        <tr class="bold" style="background: rgb(167, 167, 167);">
                            <td class="p-0" style="height: 28px;" width="80%" align="left">
                                Jumlah Liabilitas + Ekuitas
                            </td>
                            <td class="p-0" align="right" width="20%">
                                {{ $jumlah_liabilitas_equitas < 0 ? '(' . number_format(abs($jumlah_liabilitas_equitas), 2) . ')' : number_format($jumlah_liabilitas_equitas, 2) }}&nbsp;
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        @if (!empty($selisih_neraca) && abs($selisih_neraca) > 0)
        <div style="color: #f44335">
            Ada selisih antara Jumlah Aset dan Jumlah Liabilitas + Ekuitas sebesar
            <b>{{ number_format($selisih_neraca, 2) }}</b>
        </div>
        @endif
    </li>
    <li style="margin-top: 12px;">
        <div style="text-transform: uppercase;">
            Pembagian Laba Usaha
        </div>
        <ol>
            <li>
                Pembagian atas laba usaha dibagi menjadi Laba dibagikan dan laba ditahan sesuai dengan ketentuan pada
                Permendesa PDTT nomor 15 tahun 2021 yaitu:
                <ol style="list-style: lower-latin;">
                    <li>
                        Hasil usaha yang dibagikan paling sedikit terdiri atas: bagian milik bersama masyarakat Desa;
                        dan bagian Desa;
                    </li>
                    <li>
                        Besaran masing-masing bagian dihitung berdasarkan persentase penyertaan modal dan dituangkan
                        dalamanggaran dasar.
                    </li>
                    <li>
                        <div>Alokasi laba Bagian Desa;</div>
                        <ul>
                            <li style="list-style: none; margin-left: -20px;">
                                <table cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td class="b" colspan="3" align="center">Desa</td>
                                        <td class="b" align="center">s/d Tahun </td>
                                        <td class="b" align="center">Tahun </td>
                                        <td class="b" align="center">s/d Tahun </td>
                                    </tr>
                                    <tr>
                                        <td> 1 .</td>
                                        <td></td>
                                        <td>:</td>
                                        <td width="70" align="right">
                                        </td>
                                        <td width="70" align="right">
                                        </td>
                                        <td width="70" align="right">

                                        </td>
                                    </tr>
                                </table>
                            </li>
                        </ul>
                    </li>
                    <li>
                        Bagian milik bersama masyarakat Desa digunakan untuk:
                        <ol>
                            <li>
                                Kegiatan sosial kemasyarakatan dan bantuan rumah tangga miskin
                                <ul style="list-style: lower-alpha">
                                    <li>
                                        Pembagian laba s/d Tahun Rp.
                                    </li>
                                    <li>
                                        Ditambah dari pembagian laba Tahun Rp.
                                    </li>
                                </ul>
                            </li>
                            <li>
                                Pengembangan kapasitas kelompok simpan pinjam perempuan/usaha ekonomi produktif
                                <ul style="list-style: lower-alpha">
                                    <li>
                                        Pembagian laba s/d Tahun Rp.
                                    </li>
                                    <li>
                                        Ditambah dari pembagian laba Tahun Rp.
                                    </li>
                                </ul>
                            </li>
                            <li>
                                Pelatihan masyarakat, dan kelompok pemanfaat umum
                                <ul style="list-style: lower-alpha">
                                    <li>
                                        Pembagian laba s/d Tahun Rp.
                                    </li>
                                    <li>
                                        Ditambah dari pembagian laba Tahun Rp.
                                    </li>
                                </ul>
                            </li>
                        </ol>
                    </li>
                </ol>
            </li>
            <li>
                <div>Laba Ditahan Dari Laba Tahun </div>
                <ol style="list-style: lower-latin;">
                    <li>
                        Laba Ditahan untuk Penambahan Modal Kegiatan DBM Rp.
                    </li>
                    <li>
                        Laba Ditahan untuk Penambahan Investasi Usaha Rp.
                    </li>
                    <li>
                        Laba Ditahan untuk Pendirian Unit Usaha Rp.
                    </li>
                </ol>
            </li>
        </ol>
    </li>

    <li style="margin-top: 12px;">
        <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;">
            <tr>
                <td align="justify">
                    <div style="text-transform: uppercase;">
                        Penutup
                    </div>
                    <div style="text-align: justify">
                        Laporan Keuangan {{ $nama }} ini disajikan dengan berpedoman pada Keputusan
                        Kementerian Desa Nomor 136/2022 Tentang Panduan Penyusunan Pelaporan Bumdes. Dimana yang
                        dimaksud Bumdes yang dimaksud dalam Keputusan Kementerian Desa adalah meliputi Bumdes, Bumdesma
                        dan Bumdesma Lkd. Catatan atas Laporan Keuangan (CaLK) ini merupakan bagian tidak terpisahkan
                        dari Laporan Keuangan Badan Usaha Milik Desa Bersama {{ $nama }} untuk
                        Laporan Operasi {{ $nama_tgl ?? ($sub_judul ?? '') }}. Selanjutnya Catatan atas Laporan Keuangan ini diharapkan
                        untuk dapat berguna bagi pihak-pihak yang berkepentingan (stakeholders) serta memenuhi
                        prinsip-prinsip transparansi, akuntabilitas, pertanggungjawaban, independensi, dan fairness
                        dalam pengelolaan keuangan {{ $nama }} .
                    </div>

                    <table border="0" width="100%" cellspacing="0" cellpadding="0" style="font-size: 11px;"
                        class="p">
                    </table>
                </td>
            </tr>
        </table>
    </li>
</ol>
@endsection
