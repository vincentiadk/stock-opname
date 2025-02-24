@extends('layouts.index')
@section('content')
<link rel="stylesheet" href="{{ asset('datatables.bundle.css') }}" />
<div class="form-links mt-2 mb-2">
    <div>
        Scan QR Code untuk mendapatkan data koleksi <span class="btn btn-primary" id="scanQR" style="display:none">Scan ulang</span>
    </div>
</div>
<div id="reader"></div>
<input type="hidden" id="qrcode" />
<div class="section mt-2">
    <div class="section-title">Data Koleksi</div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped" id="table" style="font-size:7pt">
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Tahun Terbit</th>
                        <th scope="col">Item ID</th>
                        <th scope="col" class="text-end">Nomor Deposit</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('datatables.bundle.js') }}"></script>
<script src="{{ asset('html5-qrcode.js') }}"></script>
<script src="{{ asset('assets/js/jquery.ui.sound.js')}}"></script>
<script>
    const html5QrCode = new Html5Qrcode("reader");

    const qrCodeSuccessCallback = (decodedText, decodedResult) => {
        $(this).uiSound({play: "success"});
        $('#qrcode').val(decodedText);
        loadDataTable();
        html5QrCode.stop();
        $('#scanQR').css('display', 'block');
    };
    $('#scanQR').on('click', function(){
        qrCodeDisplay(config);
    });
    const config = { fps: 10, qrbox: { width: 200, height: 200 } };
    const qrCodeDisplay = (config) => {
        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
    }
    qrCodeDisplay(config);
    var loadDataTable = function(){
		t = new DataTable('#table', {
			scrollX: true,
			processing: true,
			"searching": true,
			filter: false,
			serverSide: true,
			destroy: true,
			order: [[7, 'desc']],
			lengthMenu: [
				[10, 25, 50, 100, 500, -1],
				[10, 25, 50, 100, 500, 'All']
			],
			ajax: {
				url: '{{ url("rak/datatable") }}',
				data: {
					qrcode: $('#qrcode').val()

				}
			},
		});
	};
</script>
@endsection