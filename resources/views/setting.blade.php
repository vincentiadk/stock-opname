@extends('layouts.index')
@section('content')
<div class="listview-title mt-2">Lokasi Default</div>
<ul class="listview image-listview text inset">
    <li>
        <div class="item">
            <div class="in">
                <select class="form-control">
                    <option>Lantai 4A</option>
                    <option>Lantai 5A</option>
                    <option>Lantai 6A</option>
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
                <select class="form-control" id="selectRak">
                    <option>04A-00A</option>
                    <option>04A-00B</option>
                    <option>04A-00C</option>
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
                            <button class="btn btn-primary">Simpan</button>
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
                <select class="form-control" id="selectAmbal">
                    <option>00A-0001</option>
                    <option>00A-0002</option>
                    <option>00A-0003</option>
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
                            <button class="btn btn-primary">Simpan</button>
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
                        <button type="button" class="btn btn-text-primary" data-bs-dismiss="modal">SIMPAN</button>
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
                            <i class="clear-input">
                                <ion-icon name="close-circle"></ion-icon>
                            </i>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="btn-inline">
                        <button type="button" class="btn btn-text-secondary" data-bs-dismiss="modal">CANCEL</button>
                        <button type="button" class="btn btn-text-primary" data-bs-dismiss="modal">SIMPAN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- * Dialog Form Ambal-->
@endsection
@section('script')
<script>
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
        let rak_value = $('#selectRak option:selected').val();
        $('#txtRakEdit').val(rak_value);
    });
    $('#editAmbal').on('click', function(){
        let ambal_value = $('#selectAmbal option:selected').val();
        $('#txtAmbalEdit').val(ambal_value);
    });
</script>
@endsection