@extends('layouts.index')

@section('content')
<style>
    /* tweak ringan agar lebih enak dilihat di mobile */
    .page-wrap { max-width: 900px; margin: 0 auto; }
    .card-soft { border: 0; border-radius: 1rem; box-shadow: 0 6px 18px rgba(16,24,40,.06); }
    .muted { color: #6b7280; } /* gray-500 */
    .btn-pill { border-radius: 999px; }
    .btn-toggle { padding: .5rem .9rem; font-weight: 600; }
    #reader { border-radius: 1rem; overflow: hidden; }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; }
    .help { font-size: .875rem; color:#6b7280; }
</style>

<div class="section mt-2 mb-4 page-wrap">
    <div class="card card-soft">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex align-items-center mb-3">
                <a href="javascript:history.back()" class="me-2 text-decoration-none">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <h5 class="m-0">Penjajaran Koleksi</h5>
            </div>

            {{-- Info Project & Lokasi --}}
            @if($setting)
                @if(! is_null($setting->stockopname_id))
                    <div class="fw-semibold fs-6">Nama Project : <span class="text-primary">{{ $setting->stockopname_name }}</span></div>
                @endif

                <div class="fw-semibold fs-6">
                    Lokasi :
                    @if(! is_null($setting->location_id))
                        <span class="text-primary">{{ $setting->location_name }}</span>
                        @if(! is_null($setting->location_shelf_id))
                            , <span class="text-primary">{{ $setting->location_shelf_name }}</span>
                        @endif
                        @if(! is_null($setting->location_rugs_id))
                            , <span class="text-primary">{{ $setting->location_rugs_name }}</span>
                        @else
                            <div class="alert alert-warning py-2 px-3 mt-2 mb-0">
                                Anda belum mengatur lokasi dengan lengkap.
                                <a href="{{ url('/setting') }}" class="btn btn-sm btn-primary ms-2">Atur lokasi</a>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning py-2 px-3 mt-2 mb-0">
                            Anda belum mengatur lokasi.
                            <a href="{{ url('/setting') }}" class="btn btn-sm btn-primary ms-2">Atur lokasi</a>
                        </div>
                    @endif
                </div>
            @else
                <div class="alert alert-warning py-2 px-3 mt-2 mb-0">
                    Anda belum mengatur lokasi.
                    <a href="{{ url('/setting') }}" class="btn btn-sm btn-primary ms-2">Atur lokasi</a>
                </div>
            @endif

            {{-- Alerts --}}
            <div class="mt-3">
                <div id="lblError" class="alert alert-danger d-none mb-2"></div>
                <div id="lblSuccess" class="alert alert-success d-none mb-2"></div>
            </div>

            {{-- Toggle Scanner --}}
            <div class="d-flex gap-2 mt-3">
                <button type="button" id="btnNfcEnable"  class="btn btn-success btn-pill btn-toggle">Gunakan NFC Reader</button>
                <button type="button" id="btnNfcDisable" class="btn btn-outline-success btn-pill btn-toggle d-none">Gunakan Barcode Reader</button>
            </div>

            {{-- Kamera / Reader --}}
            <div class="mt-3">
                <div id="formRFID" class="text-center bg-white py-3 rounded d-none">
                    <img src="{{ asset('/assets/img/taprfid.gif')}}" width="160" alt="Tap RFID"/>
                    <div class="help mt-2">Tempelkan kartu/tag ke perangkat yang mendukung NFC</div>
                </div>

                {{-- kotak kamera proporsional --}}
                <div class="mt-2" id="cameraBox">
                    <div id="reader"></div>
                </div>
            </div>

            {{-- Input Manual --}}
            <div class="mt-4">
                <label for="txtManual" class="form-label fw-semibold">
                    Input Manual Item ID <span class="muted">(jika barcode/NFC tidak terbaca)</span>
                </label>
                <div class="input-group">
                    <input type="text" id="txtManual" class="form-control" placeholder="Ketik Item ID lalu Enter atau klik Tambahkan">
                    <button type="button" id="btnAddManual" class="btn btn-primary">Tambahkan</button>
                </div>
                <div class="help mt-1">Tekan <kbd>Enter</kbd> untuk menambahkan cepat.</div>
            </div>

            {{-- Daftar Tags --}}
            <div class="mt-4">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-2">Daftar Barcode / RFID</h6>
                    <span class="badge text-secondary" id="badgeCount">0 item</span>
                </div>
                <textarea id="txtTags" readonly rows="5" class="form-control mono"
                    placeholder="Barcode / RFID yang berhasil di-scan akan muncul di sini"></textarea>
            </div>

            {{-- Simpan --}}
            <div class="mt-4 d-grid gap-2 d-md-flex">
                <button type="button" id="btnSimpan" class="btn btn-primary btn-lg">
                    Simpan Data
                </button>
                <button type="button" id="btnHapus" class="btn btn-outline-danger btn-lg">
                    Hapus Data
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('html5-qrcode.js') }}"></script>
<script src="{{ asset('assets/js/jquery.ui.sound.js')}}"></script>
<script>
    // ==== SETTINGS DARI SERVER ====
    var setlocation_id       = "{{$setting->location_id ? $setting->location_id : ''}}";
    var setlocation_shelf_id = "{{$setting->location_shelf_id ? $setting->location_shelf_id : ''}}";
    var setlocation_rugs_id  = "{{$setting->location_rugs_id ? $setting->location_rugs_id : ''}}";
    var stockopname_id       = "{{$setting->stockopname_id ? $setting->stockopname_id : ''}}";

    // ==== STATE LOKAL ====
    var tags_array = [];
    var tags = '';
    var count = 0;
    var jenis = "BARQR";
    var showCamera = false;

    const $err = $('#lblError'), $ok = $('#lblSuccess'), $badge = $('#badgeCount');

    const refreshTextarea = () => {
        $('#txtTags').text(tags);
        if (count > 5) $('#txtTags').attr('rows', count);
        $badge.text(count + ' item');
        updateButtonStates();
    };

    // ==== QR/Barcode ====
    var html5QrCode = new Html5Qrcode("reader");
    var config = { fps: 10, qrbox: { width: 350, height: 200 } };

    const qrCodeSuccessCallback = (decodedText) => {
        $(this).uiSound({ play: "success" });
        isiTags(decodedText);
    };

    const qrCodeDisplay = () => {
        try {
            html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
            showCamera = true;
            $('#cameraBox').removeClass('d-none');
        } catch (e) {
            showError('Kamera tidak bisa diakses: ' + (e?.message || e));
        }
    };
    const stopCameraIfAny = async () => {
        if (showCamera) {
            try { await html5QrCode.stop(); } catch(_) {}
            showCamera = false;
        }
        $('#cameraBox').addClass('d-none');
    };

    // ==== TAMBAH TAG ====
    const isiTags = (raw) => {
        const v = String(raw || '').trim();
        if (!v) return;
        if (jQuery.inArray(v, tags_array) !== -1) return; // cegah duplikat

        tags_array.push(v);
        tags += v + '\n';
        count += 1;
        refreshTextarea();
    };

    // ==== NFC ====
    const startNdef = () => {
        if (!('NDEFReader' in window)) {
            showError('Fitur NFC tidak didukung pada perangkat Anda.');
            return;
        }
        const ndef = new NDEFReader();
        ndef.scan().then(() => {
            ndef.onreadingerror = () => showError('Gagal membaca NFC. Coba ulangi.');
            ndef.onreading = (event) => {
                $(this).uiSound({ play: "success" });
                let sn  = event.serialNumber.toString();
                let reversedSerial = sn.split(":").reverse().join("").toUpperCase();
                isiTags(reversedSerial);
            };
        }).catch(err => showError('Gagal mulai scan NFC: ' + err));
    };

    // ==== TOGGLE MODE ====
    $('#btnNfcEnable').on('click', async function(){
        jenis = "RFID";
        await stopCameraIfAny();
        $('#btnNfcEnable').addClass('d-none');
        $('#btnNfcDisable').removeClass('d-none');
        $('#formRFID').removeClass('d-none');
        // reset daftar (opsional)
        //tags_array = []; tags=''; count=0; refreshTextarea();
        startNdef();
    });

    $('#btnNfcDisable').on('click', function(){
        jenis = "BARQR";
        qrCodeDisplay();
        $('#btnNfcDisable').addClass('d-none');
        $('#btnNfcEnable').removeClass('d-none');
        $('#formRFID').addClass('d-none');
        
    });

    // ==== INPUT MANUAL ====
    const addManual = () => {
        const v = $('#txtManual').val().trim();
        if (!v) return showError('Item ID kosong. Silakan isi dulu.');
        isiTags(v);
        $('#txtManual').val('');
        showSuccess('Item ID ditambahkan.');
    };
    $('#btnAddManual').on('click', addManual);
    $('#txtManual').on('keypress', function(e){ if (e.which === 13) { e.preventDefault(); addManual(); } });

    // ===== Button Loading Helper =====
    const $btnSimpan = $('#btnSimpan');
    const $btnHapus  = $('#btnHapus');
    let _btnSimpanHTML = $btnSimpan.html();
    let _btnHapusHTML  = $btnHapus.html();

    const setBtnLoading = ($btn, on, textLoading) => {
        if (on) {
            $btn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + (textLoading || 'Memproses...')
            );
        } else {
            if ($btn.is($btnSimpan)) $btn.html(_btnSimpanHTML);
            else if ($btn.is($btnHapus)) $btn.html(_btnHapusHTML);
            $btn.prop('disabled', false);
        }
    };

    // ===== Alert Helpers (auto hide opsional) =====
    const showSuccess = (msg, autoHideMs = 2500) => {
        $('#lblError').addClass('d-none').text('');
        $('#lblSuccess').removeClass('d-none').text(msg);
        // scroll ke alert biar terlihat
        const y = $('#lblSuccess').offset()?.top || 0;
        window.scrollTo({ top: y - 80, behavior: 'smooth' });
        if (autoHideMs) setTimeout(() => $('#lblSuccess').addClass('d-none'), autoHideMs);
    };

    const showError = (msg) => {
        $('#lblSuccess').addClass('d-none').text('');
        $('#lblError').removeClass('d-none').text(msg);
        const y = $('#lblError').offset()?.top || 0;
        window.scrollTo({ top: y - 80, behavior: 'smooth' });
    };
    // ==== SIMPAN ====
    $('#btnSimpan').on('click', function () {
        if (!tags_array.length) return showError('Belum ada data untuk disimpan.');
        setBtnLoading($btnSimpan, true);
        $.ajax({
            type: 'POST',
            url: '{{ url("stock-opname/save") }}',
            contentType: "application/json; charset=utf-8",
            dataType: 'json',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            data: JSON.stringify({
                "stockopnameid":     "{{ $setting->stockopname_id ?? '' }}",
                "location_shelf_id": "{{ $setting->location_shelf_id ?? '' }}",
                "location_rugs_id":  "{{ $setting->location_rugs_id ?? '' }}",
                "location_id":       "{{ $setting->location_id ?? '' }}",
                "listdata":          tags_array,
                "jenis":             jenis,
            }),
            success: function() {
                setBtnLoading($btnSimpan, false);      
                $('#txtTags').attr('rows', 5).text('');
                tags = ''; tags_array = []; count = 0; refreshTextarea();
                showSuccess('Data koleksi berhasil disimpan!'); 
            },
            error: function(xhr){
                setBtnLoading($btnSimpan, false);
                const msg = xhr?.responseJSON?.Message || 'Terjadi kesalahan';
                showError('Gagal menyimpan! ' + msg);
            }
        });
    });

    //==== HAPUS DATA ======
    $('#btnHapus').on('click', async function () {
        if (!tags_array.length) return showError('Tidak ada data untuk dihapus.');
        if (!confirm('Hapus semua data yang sudah dipindai dari daftar?')) return;
        tags = '';
        tags_array = [];
        count = 0;
        $('#txtTags').attr('rows', 5).text('');
        refreshTextarea();
        showSuccess('Daftar berhasil dikosongkan.');
    });

    const updateButtonStates = () => {
        const hasData = tags_array.length > 0;
        $btnSimpan.prop('disabled', !hasData);
        $btnHapus.prop('disabled', !hasData);
    };
    
    // panggil sekali saat init
    updateButtonStates();
    // start default: barcode/QR
    qrCodeDisplay();
</script>
@endsection

