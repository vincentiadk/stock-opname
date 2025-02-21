@extends('layouts.index')
@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="listview-title mt-2">Lokasi Default</div>
<ul class="listview image-listview text inset">
    <li>
        <div class="item">
            <div class="in">
                <select class="form-control select2" id="selectLocation" name="location">
                    @foreach($locations as $l)
                        <option value="{{$l['id']}}" @if($l["id"] == $setting->location_id) selected @endif> {{$l['text']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </li>
</ul>

<div class="listview-title mt-1">Nomor Rak <span class="badge badge-primary" id="editRak" data-bs-toggle="modal"
        data-bs-target="#DialogForm1">edit</span> <span class="badge badge-danger" id="hapusRak">hapus</span></div>
<ul class="listview image-listview text inset">
    <li>
        <div class="item">
            <div class="in">
                <select class="form-control select2" id="selectRak" name="rak">
                    @foreach($location_shelf as $ls)
                        <option value="{{$ls['id']}}" @if($ls["id"] == $setting->location_shelf_id) selected @endif> {{$ls['text']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </li>
    <li>
        <div class="item">
            <div class="in">
                <div class="form-group basic">
                    <div class="input-wrapper">
                        <label class="label" for="title">Tambah Rak</label>
                        <div class="input-group">
                            <input class="form-control" id="txtRak" autocomplete="off" placeholder="Nomor Rak"
                                type="text" />
                            <i class="clear-input">
                                <ion-icon name="close-circle"></ion-icon>
                            </i>
                            <span class="btn btn-primary" id="btnTambahRak">Simpan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </li>
</ul>

<div class="listview-title mt-1">Nomor Ambal <span class="badge badge-primary" id="editAmbal" data-bs-toggle="modal"
        data-bs-target="#DialogForm2">edit</span> <span class="badge badge-danger" id="hapusAmbal">hapus</span></div>
<ul class="listview image-listview text inset">
    <li>
        <div class="item">
            <div class="in">
                <select class="form-control select2" id="selectAmbal" name="ambal">
                    @foreach($location_rugs as $lr)
                        <option value="{{$lr['id']}}" @if($lr["id"] == $setting->location_rugs_id) selected @endif> {{$lr['text']}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </li>
    <li>
        <div class="item">
            <div class="in">
                <div class="form-group basic">
                    <div class="input-wrapper">
                        <label class="label" for="title">Tambah Ambal</label>
                        <div class="input-group">
                            <input class="form-control" id="txtAmbal" autocomplete="off" placeholder="Nomor Ambal"
                                type="text" />
                            <i class="clear-input">
                                <ion-icon name="close-circle"></ion-icon>
                            </i>
                            <span class="btn btn-primary" id="btnTambahAmbal">Simpan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </li>
</ul>

<!-- Dialog Form Rak-->
<div class="modal fade dialogbox" id="DialogForm1" data-bs-backdrop="static" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nomor Rak</h5>
            </div>
            <form>
                <div class="modal-body text-start mb-2">
                    <div class="form-group basic">
                        <div class="input-wrapper">
                            <label class="label" for="text1">Masukan perubahan nomor rak</label>
                            <input type="hidden" id="idRak">
                            <input type="text" class="form-control" id="txtRakEdit">
                            <i class="clear-input">
                                <ion-icon name="close-circle"></ion-icon>
                            </i>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-inline">
                        <button type="button" class="btn btn-text-secondary" data-bs-dismiss="modal">CANCEL</button>
                        <span type="button" class="btn btn-text-primary" data-bs-dismiss="modal" id="btnModifyRak">SIMPAN</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- * Dialog Form Rak-->

<!-- Dialog Form Ambal-->
<div class="modal fade dialogbox" id="DialogForm2" data-bs-backdrop="static" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nomor Ambal</h5>
            </div>
            <form>
                <div class="modal-body text-start mb-2">
                    <div class="form-group basic">
                        <div class="input-wrapper">
                            <label class="label" for="text1">Masukan perubahan nomor ambal</label>
                            <input type="text" class="form-control" id="txtAmbalEdit">
                            <input type="hidden" id="idAmbal">
                            <i class="clear-input">
                                <ion-icon name="close-circle"></ion-icon>
                            </i>
                        </div>
                    </div>
                    <input type="hidden" id="hidLocation" value="{{$setting ? $setting->id : 0 }}" />
                </div>
                <div class="modal-footer">
                    <div class="btn-inline">
                        <button type="button" class="btn btn-text-secondary" data-bs-dismiss="modal">CANCEL</button>
                        <span class="btn btn-text-primary" data-bs-dismiss="modal" id="btnModifyAmbal">SIMPAN</span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- * Dialog Form Ambal-->
@endsection
@section('script')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    var location_id = "{{ $setting->location_id}}";
    var location_shelf_id = "{{ $setting->location_shelf_id}}";
    var location_rugs_id = "{{ $setting->location_rugs_id}}";
    $('#hapusRak').on('click', function(){
        if(confirm('Anda yakin akan menghapus rak nomor ' + $('#selectRak option:selected').text() + '?')){
            //do something
        }
    });
    $('#hapusAmbal').on('click', function(){
        if(confirm('Anda yakin akan menghapus ambal ' + $('#selectAmbal option:selected').text() + '?')){
            //do something
        }
    });
    $('#editRak').on('click', function(){
        let rak_value = $('#selectRak option:selected').text();
        $('#txtRakEdit').val(rak_value);
        $('#idRak').val($('#selectRak').val());
    });
    $('#editAmbal').on('click', function(){
        let ambal_value = $('#selectAmbal option:selected').text();
        $('#txtAmbalEdit').val(ambal_value);
        $('#idAmbal').val($('#selectAmbal').val());
    });
    $('#btnModifyRak').on('click', function(){
        if($('#txtRakEdit').val().trim() == ""){
            alert('Nama rak tidak boleh kosong!');
        } else {
            $.ajax({
                type: 'POST',
                url: '{{ url("location/modify/location_shelf") }}' + '/' + $('#idRak').val(),
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                data: JSON.stringify({
                    "name" : $('#txtRakEdit').val(),
                    "location_id" : location_id
                }),
                success: function(data) {
                    $('#selectRak option[value="'+$('#idRak').val()+'"]').text($('#txtRakEdit').val());
                    $('#selectRak').select2('destroy').select2();
                    $('#txtRakEdit').val(''); 
                },
                error: function(data){
                    alert(data.responseJSON.Message);
                }
            });
        }
    });
    $('#btnModifyAmbal').on('click', function(){
        if($('#txtAmbalEdit').val().trim() == ""){
            alert('Nama ambal tidak boleh kosong!');
        } else {
            $.ajax({
                type: 'POST',
                url: '{{ url("location/modify/location_rugs") }}' + '/' + $('#idAmbal').val(),
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                data: JSON.stringify({
                    "name" : $('#txtAmbalEdit').val(),
                    "location_shelf_id" : location_shelf_id,
                    "location_id" : location_id
                }),
                success: function(data) { 
                    $('#selectAmbal option[value="'+$('#idAmbal').val()+'"]').text($('#txtAmbalEdit').val());
                    $('#selectAmbal').select2('destroy').select2();
                    $('#txtAmbalEdit').val(''); 
                },
                error: function(data){
                    alert(data.responseJSON.Message);
                }
            });
        }
    });
    $('#btnTambahRak').on('click', function(){
        if($('#selectLocation').val() == ""){
            alert('Pilih lokasi lantai terlebih dulu!');
        } else if($('#txtRak').val().trim() == ""){
            alert('Mohon isi nama rak untuk menyimpan!');
        } else {
            $.ajax({
                type: 'POST',
                url: '{{ url("location/add/location_shelf") }}',
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                data: JSON.stringify({
                    "name" : $('#txtRak').val(),
                    "location_id" : location_id
                }),
                success: function(data) { 
                    $('#txtRak').val('');
                    location_shelf_id = data["ID"];
                    getSettingShelf(data["ID"]);
                },
                error: function(data){
                    alert(data.responseJSON.Message);
                }
            });
        }
    });
    $('#btnTambahAmbal').on('click', function(){
        if($('#selectRak').val() == ""){
            alert('Pilih Rak terlebih dulu!');
        } else if($('#txtAmbal').val().trim() == ""){
            alert('Mohon isi nama ambal untuk menyimpan!');
        } else {
            $.ajax({
                type: 'POST',
                url: '{{ url("location/add/location_rugs") }}',
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                data: JSON.stringify({
                    "name" : $('#txtAmbal').val(),
                    "location_shelf_id" : $('#selectRak').val(),
                    "location_id" : $('#selectLocation').val()
                }),
                success: function(data) { 
                    $('#txtAmbal').val('');
                    location_rugs_id = data["ID"];
                    getSettingRugs(data["ID"]);
                },
                error: function(data){
                    alert(data.responseJSON.Message);
                }
            });
        }
    });

    var getSetting = () => {
        $.getJSON("{{ url('setting/location') }}", function (res) {
                data = [{
                    id: "",
                    nama: "- Pilih Lokasi -",
                    text: "- Pilih Lokasi -"
                }].concat(res);

                        //implemen data ke select provinsi
            $("#selectLocation").select2({
                dropdownAutoWidth: true,
                width: '100%',
                data: data
            });
        });
    }
    $('#selectLocation').select2().on('select2:close', function(){
        if(location_id != $('#selectLocation').val()){
            $.ajax({
                type: 'POST',
                url: '{{ url("setting/location") }}',
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                data: JSON.stringify({
                    "location_id":$('#selectLocation').val(),
                    "location_name" : $('#selectLocation option:selected').text()
                }),
                success: function(data) { 
                    location_id =  $('#selectLocation').val();
                    getSettingShelf(null); 
                },
            });
        }
    });
    var getSettingShelf = (value) => {
        $('#selectRak').empty();
        $.getJSON('{{ url("setting/location-shelf/") }}' + '/'+ $('#selectLocation').val(), function (res) {
                data = [{
                    id: "",
                    nama: "- Pilih Lokasi -",
                    text: "- Pilih Lokasi -"
                }].concat(res);
            $("#selectRak").select2({
                dropdownAutoWidth: true,
                width: '100%',
                data: data
            });
            if(value != null){
                $('#selectRak').val(value).trigger('change');
                getSettingRugs(null);
            }
        });
    }
    $('#selectRak').select2().on('select2:close', function(){
        if(location_shelf_id != $('#selectRak').val()){
            $.ajax({
                type: 'POST',
                url: '{{ url("setting/location-shelf") }}',
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                data: JSON.stringify({
                    "location_shelf_id":$(this).val(),
                    "location_shelf_name":$('#selectRak option:selected').text()
                }),
                success: function(data) { 
                    location_shelf_id =  $('#selectRak').val();
                    getSettingRugs(null);
                },
            });
        }
    });
    var getSettingRugs = (value) => {
        $('#selectAmbal').empty();
        $.getJSON('{{ url("setting/location-rugs") }}' + '/'+ $('#selectRak').val(), function (res) {
                data = [{
                    id: "",
                    nama: "- Pilih Lokasi -",
                    text: "- Pilih Lokasi -"
                }].concat(res);
            $("#selectAmbal").select2({
                dropdownAutoWidth: true,
                width: '100%',
                data: data
            });
            if(value != null){
                $('#selectAmbal').val(value).trigger('change');
            }
        });
    }
    $('#selectAmbal').select2().on('select2:close', function(){
        if(location_rugs_id != $('#selectAmbal').val()){
            $.ajax({
                type: 'POST',
                url: '{{ url("setting/location-rugs") }}',
                contentType: "application/json; charset=utf-8",
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                data: JSON.stringify({
                    "location_rugs_id":$(this).val(),
                    "location_rugs_name":$('#selectAmbal option:selected').text()
                }),
                success: function(data) { 
                    location_rugs_id = $('#selectAmbal').val();
                },
            });
        }
    });
</script>
@endsection