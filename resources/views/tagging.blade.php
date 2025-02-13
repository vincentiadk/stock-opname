@extends('layouts.index')
@section('content')
<div class="form-links mt-2 mb-2">
    <div>
        Lokasi : <a href="{{ url('/setting') }}">Lantai 4A, Rak A02, Ambal 01<label id="lblLokasi"></label> </a>
        <a href="{{ url('/setting') }}" class="btn btn-primary btn-sm">Ganti lokasi</a>
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

                <div class="form-group basic">
                    <div class="input-wrapper">
                        <label class="label" for="title">Judul</label>
                        <textarea class="form-control" id="title" autocomplete="off" rows="6"
                            placeholder="Judul Koleksi"></textarea>
                        <i class="clear-input">
                            <ion-icon name="close-circle"></ion-icon>
                        </i>
                    </div>
                </div>
                <div class="form-group basic">
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
            <a href="#" class="btn btn-primary btn-block btn-lg" id="btnSimpan" >Simpan</a> <br/>
            <a href="#" class="btn btn-warning btn-block btn-lg" id="btnMetadata" >Metadata tidak sama</a>
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
    }
    const html5QrCode = new Html5Qrcode("reader");

    const qrCodeSuccessCallback = (decodedText, decodedResult) => {
        $('#barcode').val(decodedText);
        return $.get("{{ url('collection/search') }}" + '?barcode=' + decodedText, function (response) {
            if (response["Status"] == "Success") {
                let data = response["Data"];
                let title = data["TITLE"].split('/')[0];
                $('#title').text(title);
                $('#barcode').val(decodedText);
                $('#nodeposit').val(data['NOINDUK_DEPOSIT']);
                html5QrCode.stop();
                $('#btnSimpan').css('display', 'block');
                $('#btnMetadata').css('display', 'block');
            } else {
                html5QrCode.stop();
                alert(response["Message"]);
                qrCodeDisplay(config);
                hideButton();
            }
        });
    };
    const config = { fps: 10, qrbox: { width: 350, height: 200 } };
    const qrCodeDisplay = (config) => {
        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
    }
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