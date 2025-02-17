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
    if ('NDEFReader' in window) {
        const ndef = new NDEFReader();
        ndef.scan().then(() => {
            console.log("Scan started successfully.");
            ndef.onreadingerror = () => {
                console.log("Cannot read data from the NFC tag. Try another one?");
            };
            ndef.onreading = event => {
                console.log("NDEF message read.");
            };
        }).catch(error => {
            console.log(`Error! Scan failed to start: ${error}.`);
        });
        ndef.onreading = event => {
            const message = event.message;
            for (const record of message.records) {
                console.log("Record type:  " + record.recordType);
                console.log("MIME type:    " + record.mediaType);
                console.log("Record id:    " + record.id);
                switch (record.recordType) {
                case "text":
                    // TODO: Read text record with record data, lang, and encoding.
                    break;
                case "url":
                    // TODO: Read URL record with record data.
                    break;
                default:
                    // TODO: Handle other records with record data.
                }
            }
        };
    }
    hideButton();
   
    $('#btnSave').on('click', function () {
        alert('ok!');
    });
</script>
@endsection