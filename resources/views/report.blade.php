@extends('layouts.index')
@section('content')
<link rel="stylesheet" href="{{ asset('datatables.bundle.css') }}" />
<div class="section">
    <div class="section-title">Pilih Periode</div>
    <ul class="listview image-listview text inset mb-2">
    <li>
        <div class="item">
            <div class="in">
                <select class="form-control" id="periode">
                    <option>Harian</option>
                    <option>Bulanan</option>
                    <option>Tahunan</option>
                </select>
            </div>
        </div>
    </li>
</ul>
    <div class="card">
        <div class="table">
            <table class="table table-striped" id="table">
                <thead>
                    <tr>
                        <th scope="col">Periode</th>
                        <th scope="col">Jumlah</th>
                    </tr>
                </thead>
                <tfooter>
                    <tr>
                        <th scope="col">Total</th>
                        <th scope="col"></th>
                    </tr>
                </tfooter>
            </table>
        </div>

    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('datatables.bundle.js') }}"></script>
<script>
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
				url: '{{ url("report/datatable") }}',
				data: {
					periode: $('#periode').val()
				}
			},
		});
	};
    $('#periode').on('change', function(){
        loadDataTable();
    });
    loadDataTable();
</script>
@endsection