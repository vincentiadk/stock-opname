@extends('layouts.index')
@section('content')
<div class="section mt-2 mb-2">
    <div class="row">
        <div class="col-md-12">
            @if($setting)
                @if(! is_null($setting->stockopname_id))
                    <h3> Nama Project : {{ $setting->stockopname_name }} </h3>
                @endif
                <h3>Lokasi : 
                    @if(! is_null($setting->location_id))
                        {{ $setting->location_name }}
                        @if(! is_null($setting->location_shelf_id))
                            , {{ $setting->location_shelf_name }}
                        @endif
                        @if(! is_null($setting->location_rugs_id))
                                , {{ $setting->location_rugs_name }}</h3>
                        @else
                            </h3>
                            <h4>Anda belum mengatur lokasi dengan lengkap. <a href="{{ url('/setting') }}" class="btn btn-primary btn-sm">Atur lokasi</a></h4>
                        @endif
                    @else
                    <h4>Anda belum mengatur lokasi. <a href="{{ url('/setting') }}" class="btn btn-primary btn-sm">Atur lokasi</a></h4>
                    @endif
                @else
                <h4>Anda belum mengatur lokasi. <a href="{{ url('/setting') }}" class="btn btn-primary btn-sm">Atur lokasi</a></h4>
                @endif
                
        </div>
    </div>
    <div class="row mt-2">
        <div class="form-group" id="formBarcode">
            <div class="input-group">
                <input type="number" class="form-control" id="txtCariBarcode" placeholder="Cari Barcode"/>
                <div class="btn-group" role="group">
                <span class="btn btn-danger" id="hideCamera" style="border-radius:0px !important">
                    <ion-icon name="eye-off" role="img" class="md hydrated" aria-label="Hide Camera"></ion-icon>
                </span>
                <span style="display:none"  id="showCamera" class="btn btn-success">
                    <ion-icon name="camera" role="img" class="md hydrated" aria-label="Show Camera"></ion-icon>
                </span>
                <span class="btn btn-primary" id="btnCariBarcode">Cari</span>
                </div>
            </div>
        </div>
        <label id="lblError" class="text-danger" style="display:none"></label>
        <label id="lblSuccess" class="text-success" style="display:none"></label>
        <div class="form-group">
            <span class="btn btn-warning btn-block" id="btnNotFound">Simpan koleksi tidak ditemukan</span>
            <span class="btn btn-info btn-sm" id="btnNfcEnable">Gunakan NFC Reader</span>
            <span class="btn btn-success btn-sm" id="btnNfcDisable" style="display:none">Gunakan Barcode Reader</span>
        </div>
        <div class="form-group text-center bg-white" id="formRFID" style="display:none">
            <img src="{{ asset('/assets/img/taprfid.gif')}}" width="200px"/>
        </div>
    </div>
</div>


<div id="reader"></div>

<div class="section mb-5 p-2">
    <form action="/save">
        <div class="card" style="display:none" id="collections">
            <div class="card-body pb-1">
                <table class="table table-striped" >
                    <tr><td><span id="lblType">Nomor Barcode</span></td><td>:</td><td><span id="barcode"></span></td></tr>
                    <tr id="rfid_sn" style="display:none"><td>Serial Number</td><td>:</td><td><span id="sn"></span></td></tr>
                    <tr><td>Judul</td><td>:</td><td><span id="title"></span></td></tr>
                    <tr><td>Nomor Induk</td><td>:</td><td><span id="nodeposit"></span></td></tr>
                    <tr><td>Lokasi saat ini</td><td>:</td><td><span id="location"></span></td></tr>
                </table>
            </div>
        </div>
        <div class="form-button-group  transparent" style="position:relative" >
            <span class="btn btn-primary btn-block" id="btnSimpan">Simpan Lokasi</span>
            <span class="btn btn-warning btn-block" id="btnMetadata">Masalah Metadata</span>
            <span class="btn btn-danger btn-block" id="btnLepas">Hapus Lokasi</span>
        </div>
    </form>
</div>
@endsection

@section('script')
<script src="{{ asset('html5-qrcode.js') }}"></script>
<script src="{{ asset('assets/js/jquery.ui.sound.js')}}"></script>
<script>
    var setlocation_id = "{{$setting->location_id ? $setting->location_id :''}}";
    var setlocation_shelf_id = "{{$setting->location_shelf_id ? $setting->location_shelf_id : ''}}"; 
    var setlocation_rugs_id = "{{$setting->location_rugs_id ? $setting->location_rugs_id :  ''}}";
    var collection_id = "";
    var location_id, location_shelf_id, location_rugs_id = "";
    var showCamera= false;
    const setErrorMessage = (text) =>{
        $('#lblSuccess').css('display', 'none');
        $('#lblSuccess').html('');
        $('#lblError').css('display', 'block');
        $('#lblError').html(text);
    };
    const setSuccessMessage = (text)=>{
        $('#lblSuccess').css('display', 'block');
        $('#lblSuccess').html(text);
        $('#lblError').css('display', 'none');
        $('#lblError').html('');
    }
    const clearAllInput = ()=>{
        $('#lblError').css('display', 'none');
        $('#lblError').text('');
        $('#title').text('');
        $('#barcode').text('');
        $('#nodeposit').text('');
        $('#location').text('');
        $('#txtCariBarcode').val('');
        location_id = '';
        location_shelf_id = '';
        location_rugs_id = '';
        collection_id = '';
        $('#collections').css('display', 'none');
    }
    const hideButton = ()=> {
        $('#btnSimpan').css('display', 'none');
        $('#btnMetadata').css('display', 'none');
        $('#btnNotFound').css('display', 'none');
        $('#btnLepas').css('display', 'none');
    }
    const html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 350, height: 200 } };
    const qrCodeDisplay = (config) => {
        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
        showCamera = true;
    }
    const qrCodeSuccessCallback = (decodedText, decodedResult) => {
        $('#barcode').val(decodedText);
        searchBarcode(decodedText);
    };
    hideButton();
    const searchBarcode = (value, toolsType = 'barcode')=>{
        $('#lblSuccess').css('display', 'none');
        if(value.trim() == ""){
            hideButton();
            clearAllInput();
            setErrorMessage("Nomor barcode kosong, silakan gunakan kamera Anda untuk melakukan tagging / mencari koleksi.");
            if($('#hideCamera').css('display') == 'block') {
                html5QrCode.stop();
                qrCodeDisplay(config);
            }
        } else {
            return $.get("{{ url('tagging/search') }}" + '?value=' + value + '&type=' +toolsType, function (response) {
                if (response["Status"] == "Success") {
                    let data = response["Data"];
                    let title = data["TITLE"].split('/')[0];
                    $(this).uiSound({play: "success"});
                    if(toolsType == 'barcode') {
                        $('#barcode').text(value);
                        $('#rfid_sn').css('display', 'none');
                        $('#lblType').text('Nomor Barcode');
                    } else {
                        $('#rfid_sn').removeAttr('style');
                        $('#lblType').text('RFID');
                        $('#barcode').text(data["RFID"]);
                        $('#sn').text(data["SERIAL_NUMBER"]);
                    }
                    let noinduk = data['NOINDUK_DEPOSIT'] == ""? data['NOINDUK'] : "";
                    $('#nodeposit').text(noinduk);
                    $('#location').text(data['LOCATION_NAME'] + " " +data['LOCATION_SHELF_NAME'] + " " + data['LOCATION_RUGS_NAME']);
                    if(data['PROBLEM'] != ""){
                        title += "<br/><p style='color:red'>Problem : " + data['PROBLEM'] +"</p>";
                    }
                    $('#title').html(title);
                    location_id = data['LOCATION_ID'];
                    location_shelf_id = data['LOCATION_SHELF_ID'];
                    location_rugs_id = data['LOCATION_RUGS_ID'];
                    collection_id = data['COLID'];
                    $('#collections').css('display', 'block');
                    $('#collections').attr('tabindex', -1).focus();
                    if(setlocation_id!="" && setlocation_rugs_id != "" && setlocation_shelf_id != ""){
                        $('#btnSimpan').css('display', 'block');
                        if(location_id != "") {
                            $('#btnLepas').css('display', 'block');
                        }
                        if(data['PROBLEM'] != "metadata") {
                            $('#btnMetadata').css('display', 'block');
                        }
                        $('#btnNotFound').css('display', 'none');
                        if(toolsType == "barcode"){
                            setSuccessMessage(toolsType.toUpperCase() + ' ' + value + ' ditemukan!');
                        } else {
                            setSuccessMessage(toolsType.toUpperCase() + ' ' + data['RFID'] + ' ditemukan!');
                        }
                        if(showCamera) {
                            html5QrCode.stop();
                            qrCodeDisplay(config);
                        }
                    } else {
                        setErrorMessage("Anda tidak dapat melakukan tagging! Mohon lengkapi pengaturan lokasi.");
                        if(showCamera) {
                            html5QrCode.stop();
                            qrCodeDisplay(config);
                        }
                    }
                } else {
                    $(this).uiSound({play: "danger-alarm"});
                    if(setlocation_id !="" && setlocation_rugs_id != "" && setlocation_shelf_id != ""){
                        $('#collections').css('display', 'none');
                        $('#btnSimpan').css('display', 'none');
                        $('#btnLepas').css('display', 'none');
                        $('#btnMetadata').css('display', 'none');
                        $('#btnNotFound').css('display', 'block');
                        $('#barcode').text(value);
                        setErrorMessage(response["Message"]);
                        if(showCamera) {
                            html5QrCode.stop();
                            qrCodeDisplay(config);
                        }
                    } else {
                        clearAllInput();
                        setErrorMessage(response["Message"] + " <br/>Anda tidak dapat melakukan tagging! Mohon lengkapi pengaturan lokasi.");
                        if(showCamera) {
                            html5QrCode.stop();
                            qrCodeDisplay(config);
                        }
                        hideButton();
                    }
                }
            });
        }
    }
    
    $('#btnNfcEnable').on('click', function(){
        hideButton(); clearAllInput();
        if ('NDEFReader' in window) {
            if(showCamera) {
                html5QrCode.stop();
            }
            showCamera  = false;
            $('#showCamera').css('display','none');
            $('#hideCamera').css('display','none');
            $('#btnNfcEnable').css('display', 'none');
            $('#btnNfcDisable').css('display', 'block');
            $('#formRFID').css('display', 'block');
            $('#formBarcode').css('display', 'none');
            ndef();
        } else {
            setErrorMessage('Fitur NFC tidak didukung pada browser atau perangkat mobile Anda');
        }
    });
    $('#btnNfcDisable').on('click', function(){
        hideButton(); clearAllInput();
        $('#showCamera').css('display','none');
        $('#hideCamera').css('display','block');
        $('#btnNfcEnable').css('display', 'block');
        $('#btnNfcDisable').css('display', 'none');
        $('#formRFID').css('display', 'none');
        $('#txtRfidTags').text('');
        $('#formBarcode').css('display', 'block');
        if(!showCamera) {
            qrCodeDisplay(config);
        }
    });
    $('#btnCariBarcode').on('click', function(){
        let search = $('#txtCariBarcode').val();
        searchBarcode(search);
    });
    $('#txtCariBarcode').on('blur', function(){
        let search = $('#txtCariBarcode').val();
        searchBarcode(search);
    });
    qrCodeDisplay(config);
    $('#btnSimpan').on('click', function () {
        $.ajax({
            type: 'POST',
            url: '{{ url("tagging/save") }}',
            contentType: "application/json; charset=utf-8",
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: JSON.stringify({
                "location_shelf_id":location_shelf_id,
                "location_rugs_id":location_rugs_id,
                "location_id":location_id,
                "id": collection_id,
                'barcode': $('#barcode').text()
            }),
            success: function(data) {
                clearAllInput();
                hideButton();
                if($('#hideCamera').css('display') == 'block') {
                    html5QrCode.stop();
                    qrCodeDisplay(config);
                }
                $('#txtCariBarcode').attr('tabindex', -1).focus();
                $('#txtCariBarcode').val('');
                setSuccessMessage('Data koleksi berhasil di simpan!');
            },
            error: function(data){  
                clearAllInput();
                hideButton();
                if($('#hideCamera').css('display') == 'block') {
                    html5QrCode.stop();
                    qrCodeDisplay(config);
                }
                setErrorMessage('Gagal menyimpan! ' + data.responseJSON.Message);
            }
        });
    });
    $('#btnMetadata').on('click', function () {
        $.ajax({
            type: 'POST',
            url: '{{ url("tagging/save-masalah") }}',
            contentType: "application/json; charset=utf-8",
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: JSON.stringify({
                "location_shelf_id":location_shelf_id,
                "location_rugs_id":location_rugs_id,
                "location_id":location_id,
                "id": collection_id,
                'barcode': $('#barcode').text()
            }),
            success: function(data) {
                clearAllInput();
                hideButton();
                if($('#hideCamera').css('display') == 'block') {
                    html5QrCode.stop();
                    qrCodeDisplay(config);
                }
                $('#txtCariBarcode').attr('tabindex', -1).focus();
                $('#txtCariBarcode').val('');
                setSuccessMessage('Data koleksi bermasalah berhasil di simpan!');
            },
            error: function(data){  
                clearAllInput();
                hideButton();
                if($('#hideCamera').css('display') == 'block') {
                    html5QrCode.stop();
                    qrCodeDisplay(config);
                }
                setErrorMessage('Gagal menyimpan! ' + data.responseJSON.Message);
            }
        });
    });
    $('#btnNotFound').on('click', function () {
        $.ajax({
            type: 'POST',
            url: '{{ url("tagging/save-not-found") }}',
            contentType: "application/json; charset=utf-8",
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: JSON.stringify({
                "location_shelf_id":location_shelf_id,
                "location_rugs_id":location_rugs_id,
                "location_id":location_id,
                'barcode': $('#barcode').text()
            }),
            success: function(data) {
                clearAllInput();
                hideButton();
                if($('#hideCamera').css('display') == 'block') {
                    html5QrCode.stop();
                    qrCodeDisplay(config);
                }
                $('#txtCariBarcode').attr('tabindex', -1).focus();
                $('#txtCariBarcode').val('');
                setSuccessMessage('Data koleksi tidak ditemukan berhasil di simpan!');
            },
            error: function(data){  
                clearAllInput();
                hideButton();
                if($('#hideCamera').css('display') == 'block') {
                    html5QrCode.stop();
                    qrCodeDisplay(config);
                }
                setErrorMessage('Gagal menyimpan! ' + data.responseJSON.Message);
            }
        });
    });
    $('#btnLepas').on('click', function () {
        $.ajax({
            type: 'POST',
            url: '{{ url("tagging/save-lepas-tagging") }}',
            contentType: "application/json; charset=utf-8",
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: JSON.stringify({
                "location_shelf_id":location_shelf_id,
                "location_rugs_id":location_rugs_id,
                "location_id":location_id,
                "id": collection_id,
                'barcode': $('#barcode').text()
            }),
            success: function(data) {
                clearAllInput();
                hideButton();
                if($('#hideCamera').css('display') == 'block') {
                    html5QrCode.stop();
                    qrCodeDisplay(config);
                }
                $('#txtCariBarcode').attr('tabindex', -1).focus();
                $('#txtCariBarcode').val('');
                setSuccessMessage('Data lokasi berhasil dihapus dari koleksi!');
            },
            error: function(data){  
                clearAllInput();
                hideButton();
                if($('#hideCamera').css('display') == 'block') {
                    html5QrCode.stop();
                    qrCodeDisplay(config);
                }
                setErrorMessage('Gagal menyimpan! ' + data.responseJSON.Message);
            }
        });
    });
    $('#hideCamera').on('click', function() {
        $('#hideCamera').css('display', 'none');
        html5QrCode.stop();
        showCamera = false;
        $('#showCamera').css('display', 'block');
    });
    $('#showCamera').on('click', function() {
        $('#hideCamera').css('display', 'block');
        showCamera = true;
        qrCodeDisplay(config);
        $('#showCamera').css('display', 'none');
    });
    const ndef = () => {
        if ('NDEFReader' in window) {
            const ndef = new NDEFReader();
            ndef.scan().then(() => {
                console.log("Scan started successfully.");
                ndef.onreadingerror = () => {
                    alert("Cannot read data from the NFC tag. Try another one?");
                };
                ndef.onreading = event => {
                    let sn  = event.serialNumber.toString();
                    let reversedSerial = sn
                                        .split(":")     
                                        .reverse()      
                                        .join("") 
                                        .toUpperCase();
                    searchBarcode(reversedSerial,'rfid');
                };
            }).catch(error => {
                alert(`Error! Scan failed to start: ${error}.`);
            });
        };
    }
</script>
@endsection
