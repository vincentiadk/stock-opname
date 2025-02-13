@extends('layouts.index')
@section('content')
<link rel="stylesheet" href="{{ asset('datatables.bundle.css') }}" />
<div class="form-links mt-2 mb-2">
    <div>
        Scan QR Code untuk mendapatkan data rak
    </div>
</div>
<div id="reader"></div>
<div class="section mt-2">
    <div class="section-title">Data Koleksi</div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped" id="table">
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
<script>
    const html5QrCode = new Html5Qrcode("reader");

    const qrCodeSuccessCallback = (decodedText, decodedResult) => {

        $('#barcode').val(decodedText);
        loadDataTable();
        html5QrCode.stop();

        /*return $.get("{{ url('collection/search') }}" + '?barcode=' + decodedText, function (response) {
            if (response["Status"] == "Success") {
                let data = response["Data"];
                $('#title').text(data["TITLE"]);
                $('#barcode').val(decodedText);
                $('#nodeposit').val(data['NOINDUK_DEPOSIT']);
                html5QrCode.stop();
            } else {
                html5QrCode.stop();
                alert(response["Message"]);
                qrCodeDisplay(config);
            }
        });*/
    };
    const config = { fps: 10, qrbox: { width: 200, height: 200 } };
    const qrCodeDisplay = (config) => {
        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
    }
    // If you want to prefer front camera
    //html5QrCode.start({ facingMode: "user" }, config, qrCodeSuccessCallback);

    // If you want to prefer back camera
    //html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
    qrCodeDisplay(config);
    $('#btnSave').on('click', function () {
        alert('ok!');
    });

    var loadDataTable = function(){
		//let selectParameter = $('select[name="selectParameter"] option:selected').map(function() {
		//									return $(this).val();
		//								}).get();
		//let searchValue = $('input[type="text"][name="searchValue[]"').get().map(function takeValue(input) {
		//							return input.value;
		//						});
		//let advSearch = [];
		//
		//for(var i = 0; i < selectParameter.length; i++){
		//	if(searchValue.length > 0) {
		//		advSearch.push({
		//			'param' : selectParameter[i],
		//			'value' : searchValue[i]
		//			})
		//	}
		//}
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
				/*data: {
					advSearch : advSearch,
					jenisTerbitan: $('#selectJenis').val(),
					kdtValid : $('#selectKdt').val(),
					statusKckr : $('#selectKckr').val(),
					sumber : $('#selectSumber').val(),
					jenisMedia : $('#selectJenisMedia').val(),
					penerbit : group == p_id ? p_id : $('#selectPenerbit').val() ,
				}*/
			},
		});
	};
    loadDataTable();
</script>
@endsection