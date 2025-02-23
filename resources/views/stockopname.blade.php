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
</div>

<div class="section">
    <div class="row mt-2">
        <div class="form-group">
            <span class="btn btn-info btn-sm" id="btnNfcEnable">Gunakan NFC Reader</span>
            <span class="btn btn-success btn-sm" id="btnNfcDisable" >Gunakan Barcode Reader</span>
            <label id="lblError" class="text-danger" style="display:none"></label>
            <label id="lblSuccess" class="text-success" style="display:none"></label>
        </div>
        <div class="form-group text-center bg-white" id="formRFID" style="display:none">
            <img src="{{ asset('/assets/img/taprfid.gif')}}" width="200px"/>
        </div>
    </div>
</div>
<div id="reader"></div>
<div class="form-group">
            <h3 class="text-center">Daftar Barcode / RFID</h3>
            <textarea id="txtTags" placeholder="Barcode / RFID yang berhasil di scan akan muncul pada area berikut ini" readonly rows="5" class="form-control" style="background-color:#fff"></textarea>
            <span class="btn btn-primary btn-block btn-lg" id="btnSimpan">Simpan Tagging</span>
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
    var tags = "";
    var count = 0;
    var tags_array = [];
    var showCamera = false;
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
        location_id = '';
        location_shelf_id = '';
        location_rugs_id = '';
        collection_id = '';
    }
    
    var html5QrCode = new Html5Qrcode("reader");
    var config = { fps: 10, qrbox: { width: 350, height: 200 } };
    var qrCodeDisplay = () => {
        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
        showCamera = true;
    }
    var isiTags = (value) =>{
        if (jQuery.inArray(value, tags_array) != -1){
            //alert("ada");
        } else {
            //alert(tags_array.join('\n'));
            tags_array.push(value);
            tags += value.toString() + '\n';
            count += 1;
            if(count > 5){
                $('#txtTags').attr('rows',count);
            }
            $('#txtTags').text(tags);
        }
    }
    var qrCodeSuccessCallback = (decodedText, decodedResult) => {
        //alert(decodedText);
        $(this).uiSound({play: "success"});
        isiTags(decodedText);
    };
    $('#txtTags').on('change', function(){
        qrCodeDisplay();
    });
    var ndef = () => {
        if ('NDEFReader' in window) {
            const ndef = new NDEFReader();
            ndef.scan().then(() => {
                console.log("Scan started successfully.");
                ndef.onreadingerror = () => {
                    alert("Cannot read data from the NFC tag. Try another one?");
                };
                ndef.onreading = event => {
                    $(this).uiSound({play: "success"});
                    let sn  = event.serialNumber.toString();
                    sn = sn.replace(/:/g, '').toUpperCase();
                    isiTags(sn);
                };
            }).catch(error => {
                alert(`Error! Scan failed to start: ${error}.`);
            });
        };
    } 
    $('#btnNfcEnable').on('click', function(){
        if ('NDEFReader' in window) {
            if(showCamera) {
                html5QrCode.stop();
            }
            $('#btnNfcEnable').css('display', 'none');
            $('#btnNfcDisable').css('display', 'block');
            $('#formRFID').css('display', 'block');
            $('#txtTags').text('');
            ndef();
        } else {
            setErrorMessage('Fitur NFC tidak didukung pada browser atau perangkat mobile Anda');
        }
    });
    $('#btnNfcDisable').on('click', function(){
        qrCodeDisplay();
        $('#btnNfcDisable').css('display', 'none');
        $('#btnNfcEnable').css('display', 'block');
        $('#formRFID').css('display', 'none');
        $('#txtTags').text('');
        tags = "";
        count = 0;
    });
    $('#btnSimpan').on('click', function () {
        $.ajax({
            type: 'POST',
            url: '{{ url("stock-opname/save") }}',
            contentType: "application/json; charset=utf-8",
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: JSON.stringify({
                "location_shelf_id":location_shelf_id,
                "location_rugs_id":location_rugs_id,
                "location_id":location_id,
            }),
            success: function(data) {
                setSuccessMessage('Data koleksi berhasil di simpan!');
            },
            error: function(data){  
                setErrorMessage('Gagal menyimpan! ' + data.responseJSON.Message);
            }
        });
    });

</script>
@endsection
