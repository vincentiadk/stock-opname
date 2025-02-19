@extends('layouts.index')
@section('content')
<div class="form-links mt-2 mb-2">
    <div>
        @if($setting)
        Lokasi : <a href="{{ url('/setting') }}">{{ $setting->location_name }}, {{ $setting->location_shelf_name }}, {{ $setting->location_rugs_name }}<label id="lblLokasi"></label> </a>
        <a href="{{ url('/setting') }}" class="btn btn-primary btn-sm">Ganti lokasi</a>
        @else
        Anda belum mengatur lokasi. <a href="{{ url('/setting') }}" class="btn btn-primary btn-sm">Atur lokasi</a>
        @endif
    </div>
</div>
<div id="reader"></div>
<div class="section mb-5 p-2">
    <form action="/save">
        <div class="card">
            <div class="card-body pb-1">
                <div class="form-group basic">
                    <div class="input-wrapper">
                        <label class="label" for="barcode">Item ID / Nomor Barcode</label>
                        <input type="text" class="form-control" id="barcode" placeholder="Cari Barcode">
                        <i class="clear-input">
                            <ion-icon name="close-circle"></ion-icon>
                        </i>
                    </div>
                </div>

                <div class="form-group basic title">
                    <div class="input-wrapper">
                        <label class="label" for="title">Judul</label>
                        <textarea class="form-control" id="title" autocomplete="off" rows="6"
                            placeholder="Judul Koleksi"></textarea>
                        <i class="clear-input">
                            <ion-icon name="close-circle"></ion-icon>
                        </i>
                    </div>
                </div>
                <div class="form-group basic nodeposit">
                    <div class="input-wrapper">
                        <label class="label" for="nodeposit">Nomor Deposit</label>
                        <input type="text" class="form-control" id="nodeposit" autocomplete="off"
                            placeholder="Nomor Deposit"></textarea>
                        <i class="clear-input">
                            <ion-icon name="close-circle"></ion-icon>
                        </i>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-button-group  transparent" style="position:relative" >
            <a href="#" class="btn btn-primary btn-block btn-lg" id="btnSimpan">Simpan</a>
            <a href="#" class="btn btn-warning btn-block btn-lg" id="btnMetadata">Masalah</a>
            <a href="#" class="btn btn-danger btn-block btn-lg" id="btnLepas">Lepas Lokasi</a> 
            <a href="#" class="btn btn-warning btn-block btn-lg" id="btnNotFound" >Simpan data tidak ditemukan</a>
        </div>
    </form>
</div>
@endsection

@section('script')
<script src="{{ asset('html5-qrcode.js') }}"></script>
<script>
   
    const hideButton = ()=>{
        $('#btnSimpan').css('display', 'none');
        $('#btnMetadata').css('display', 'none');
        $('#btnNotFound').css('display', 'none');
        $('#btnLepas').css('display', 'none');
    }
    const html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 350, height: 200 } };
    const qrCodeDisplay = (config) => {
        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
    }
    const qrCodeSuccessCallback = (decodedText, decodedResult) => {
        $('#barcode').val(decodedText);
        return $.get("{{ url('tagging/search') }}" + '?barcode=' + decodedText, function (response) {
            if (response["Status"] == "Success") {
                let data = response["Data"];
                let title = data["TITLE"].split('/')[0];
                $('#title').text(title);
                $('#barcode').val(decodedText);
                $('#nodeposit').val(data['NOINDUK_DEPOSIT']);
                html5QrCode.stop();
                $('#btnSimpan').css('display', 'block');
                $('#btnLepas').css('display', 'block');
                $('#btnMetadata').css('display', 'block');
            } else {
                $('.title').css('display', 'none');
                $('.nodeposit').css('display', 'none');
                html5QrCode.stop();
                alert(response["Message"]);
                $('#btnNotFound').css('display', 'block');
                qrCodeDisplay(config);
                hideButton();
            }
        });
    };
    
    hideButton();
    // If you want to prefer front camera
    //html5QrCode.start({ facingMode: "user" }, config, qrCodeSuccessCallback);

    // If you want to prefer back camera
    //html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
    qrCodeDisplay(config);
    $('#btnSave').on('click', function () {
        alert('ok!');
    });
</script>
@endsection