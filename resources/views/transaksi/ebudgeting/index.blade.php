@extends('Layout.base')
@section('content')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>{{ $title ?? 'x' }}</h3>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">DataTable</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
    <div>&nbsp;</div>
    <div class="container-fluid">
        <form action="/transactions/anggaran" method="post" id="FromAnggaran">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-body ps-3 pe-3 pb-3 pt-3 p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="position-relative mb-3">
                                        <label for="tahun">Tahun</label>
                                        <input class="form-control" type="number" autocomplete="off" name="tahun"
                                            id="tahun" value="{{ date('Y') }}">
                                        <small class="text-danger" id="msg_tahun"></small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative mb-3">
                                        <label for="bulan">Bulan</label>
                                        <input class="form-control" type="number" autocomplete="off" name="bulan"
                                            id="bulan" value="{{ date('m') }}">
                                        <small class="text-danger" id="msg_bulan"></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button class="btn btn-primary" type="button" id="anggaran"
                                    style="float: right; margin-left: 10px;">
                                    <span class="text">Tentukan Rencana Anggaran</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div id="FormEbudgeting"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $("#nominal").maskMoney({
            allowNegative: true
        })

        $(document).on('click', '#anggaran', function(e) {
            e.preventDefault();
            var form = $('#FromAnggaran');
            Swal.fire({
                title: 'Memuat data...',
                html: `
            <img src="data:image/svg+xml,%3c!--%20By%20Sam%20Herbert%20(@sherb),%20for%20everyone.%20More%20@%20http://goo.gl/7AJzbL%20--%3e%3csvg%20width='55'%20height='80'%20viewBox='0%200%2055%2080'%20xmlns='http://www.w3.org/2000/svg'%20fill='%235d79d3'%3e%3cg%20transform='matrix(1%200%200%20-1%200%2080)'%3e%3crect%20width='10'%20height='20'%20rx='3'%3e%3canimate%20attributeName='height'%20begin='0s'%20dur='4.3s'%20values='20;45;57;80;64;32;66;45;64;23;66;13;64;56;34;34;2;23;76;79;20'%20calcMode='linear'%20repeatCount='indefinite'%20/%3e%3c/rect%3e%3crect%20x='15'%20width='10'%20height='80'%20rx='3'%3e%3canimate%20attributeName='height'%20begin='0s'%20dur='2s'%20values='80;55;33;5;75;23;73;33;12;14;60;80'%20calcMode='linear'%20repeatCount='indefinite'%20/%3e%3c/rect%3e%3crect%20x='30'%20width='10'%20height='50'%20rx='3'%3e%3canimate%20attributeName='height'%20begin='0s'%20dur='1.4s'%20values='50;34;78;23;56;23;34;76;80;54;21;50'%20calcMode='linear'%20repeatCount='indefinite'%20/%3e%3c/rect%3e%3crect%20x='45'%20width='10'%20height='30'%20rx='3'%3e%3canimate%20attributeName='height'%20begin='0s'%20dur='2s'%20values='30;45;13;80;56;72;45;76;34;23;67;30'%20calcMode='linear'%20repeatCount='indefinite'%20/%3e%3c/rect%3e%3c/g%3e%3c/svg%3e"
            style="width: 50px;" alt="loading">
        `,
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
            });

            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    Swal.close(); // Tutup loading saat sukses
                    if (result.success) {
                        $('#FormEbudgeting').html(result.view);
                    } else {
                        Swal.fire('Gagal!', 'Data gagal dimuat.', 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error!', 'Terjadi kesalahan server.', 'error');
                }
            });
        });


        $(document).on('click', '#SimpanAnggaran', function(e) {
            e.preventDefault();

            var form = $('#FormRencanaAnggaran');
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        toastMixin.fire({
                            title: result.msg
                        });
                        setTimeout(() => window.location.reload(), 3000);
                    }
                }
            });
        });
    </script>
@endsection
