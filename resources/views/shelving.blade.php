@extends('layouts.index')
@section('content')
<!-- Bootstrap Icons (CDN) -->
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    /* Sorot pesan */
#lblSuccess, #lblError { padding: .5rem .75rem; border-radius: .5rem; }
.flash-success { animation: highlightGreen 1.2s ease; }
.flash-error   { animation: highlightRed 1.2s ease; }
@keyframes highlightGreen { 0% { background:#d1fae5 } 100% { background: transparent } }
@keyframes highlightRed   { 0% { background:#fee2e2 } 100% { background: transparent } }
/* Spinner ala Bootstrap (polyfill) */
@keyframes _spin{to{transform:rotate(360deg)}}
.spinner-border{
  display:inline-block;
  width:1rem; height:1rem;
  vertical-align:-.125em;
  border:.15em solid currentColor;
  border-right-color:transparent;
  border-radius:50%;
  animation:_spin .75s linear infinite;
}
.spinner-border-sm{ width:.75rem; height:.75rem; border-width:.12em; }

/* Utility margin kanan 0.5rem (Bootstrap me-2) */
.me-2{ margin-right:.5rem; }

/* (opsional) state disabled biar keliatan */
.btn[disabled], .btn.disabled{ opacity:.65; pointer-events:none; }
</style>
<div class="section mt-2 mb-2">
    <div class="row">
        <div class="col-md-12">

            {{-- Nama Project --}}
            @if($setting && $setting->stockopname_id)
                <h3 class="mb-0">Nama Project :
                    <span class="text-primary">{{ $setting->stockopname_name }}</span>
                </h3>
            @endif

            {{-- Lokasi --}}
            <h3 class="mb-1">Lokasi :

            @if($setting && $setting->location_id)
                <span class="text-success">
                    {{ $setting->location_name }}
                    @if($setting->location_shelf_id)
                        , {{ $setting->location_shelf_name }}
                    @endif
                    @if($setting->location_rugs_id)
                        , {{ $setting->location_rugs_name }}
                    @else
                        <div class="alert alert-warning mt-2 mb-0">
                            Anda belum mengatur lokasi dengan lengkap.
                            <a href="{{ url('/setting') }}" class="btn btn-sm btn-primary ms-2">Atur lokasi</a>
                        </div>
                    @endif
                </span>
            @else
                <div class="alert alert-warning mt-2 mb-0">
                    Anda belum mengatur lokasi.
                    <a href="{{ url('/setting') }}" class="btn btn-sm btn-primary ms-2">Atur lokasi</a>
                </div>
            @endif
            </h3>
        </div>
    </div>
</div>

<!--div class="section">
    <div class="row mt-0">
        <div class="form-group mb-1">
            <span class="btn btn-info btn-sm" id="btnNfcEnable">Gunakan NFC Reader</span>
            <span class="btn btn-success btn-sm" id="btnNfcDisable" style="display:none">Gunakan Barcode Reader</span>
            <label id="lblMsg" class="text-white d-block mt-1" style="display:none"></label>
        </div>
        <div class="form-group text-center bg-white" id="formRFID" style="display:none">
            <img src="{{ asset('/assets/img/taprfid.gif')}}" width="200px"/>
        </div>
    </div>
</div-->
<div class="section">
  <div class="row mt-0">
    <div class="form-group mb-1 d-flex align-items-center gap-2">
      <div class="btn-group" role="group" aria-label="Mode pemindaian">
        <button type="button" class="btn btn-outline-primary btn-sm active" id="btnModeQR">QR</button>
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnModeBAR">Barcode</button>
        <button type="button" class="btn btn-outline-success btn-sm" id="btnModeNFC">NFC</button>
      </div>
      <label id="lblMsg" class="d-block mt-1" style="display:none"></label>
    </div>

    <div class="form-group text-center bg-white" id="formRFID" style="display:none">
      <img src="{{ asset('/assets/img/taprfid.gif')}}" width="200px"/>
    </div>
  </div>
</div>

{{-- Reader tetap di luar card --}}
<div id="reader"></div>

<div class="section mt-2">
    <div class="form-group">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="mb-0">Daftar Barcode / RFID</h3>
            <span class="badge bg-secondary" id="badgeCount">0 item</span>
        </div>

        {{-- INPUT MANUAL --}}
        <div class="input-group mb-2">
            <input type="text" id="txtManual" class="form-control" placeholder="Ketik Item ID manual…">
            <button type="button" class="btn btn-outline-primary" id="btnAddManual">Tambahkan</button>
        </div>
        <small class="text-muted d-block mb-2">Tekan <kbd>Enter</kbd> untuk menambahkan cepat.</small>

        <textarea id="txtTags" placeholder="Barcode / RFID yang berhasil di-scan akan muncul di sini" readonly rows="5" class="form-control" style="background-color:#fff"></textarea>

        <div class="d-grid gap-2 d-md-flex mt-3">
            <span class="btn btn-primary btn-lg" id="btnSimpan">Simpan Data</span>
            <span class="btn btn-outline-danger btn-lg" id="btnHapus">Hapus Data</span>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('html5-qrcode.js') }}"></script>
<script src="{{ asset('assets/js/jquery.ui.sound.js')}}"></script>

<script>
    var setlocation_id = "{{$setting->location_id ? $setting->location_id :''}}";
    var setlocation_shelf_id = "{{$setting->location_shelf_id ? $setting->location_shelf_id : ''}}"; 
    var setlocation_rugs_id = "{{$setting->location_rugs_id ? $setting->location_rugs_id :  ''}}";
    var stockopname_id = "{{$setting->stockopname_id ? $setting->stockopname_id :''}}";

    var tags = "";
    var count = 0;
    var tags_array = [];
    var jenis ="BARQR";
    var MODE = "BAR";
    

    const $badge = $('#badgeCount');

    var html5QrCode = new Html5Qrcode("reader");
    // format presets
    const FORMATS_QR  = [ Html5QrcodeSupportedFormats.QR_CODE ];
    const FORMATS_BAR = [
    Html5QrcodeSupportedFormats.CODE_128,
    Html5QrcodeSupportedFormats.EAN_13,
    Html5QrcodeSupportedFormats.CODE_39,
    Html5QrcodeSupportedFormats.UPC_A
    ];

    // config dinamis sesuai mode
    function buildConfig() {
        const base = { fps: 12, formatsToSupport: [], qrbox: null, aspectRatio: 1.7778 };
        if (MODE === "QR") {
            startQRSharp();
            base.formatsToSupport = FORMATS_QR;
            base.qrbox = (w,h)=> {
            const s = Math.floor(Math.min(w,h)*0.85);
            return { width: s, height: s }; // persegi untuk QR
            };
            
        } else if (MODE === "BAR") {
            base.formatsToSupport = FORMATS_BAR;
            base.qrbox = (w,h)=> {
            const minEdge = Math.min(w,h);
            const wide = Math.floor(minEdge * 0.85);
            return { width: wide, height: Math.floor(wide * 0.45) }; // lebar-pendek untuk 1D
            };
        }
        return base;
    }
    var showCamera = false;
    async function pickBackCameraId() {
        const cams = await Html5Qrcode.getCameras();
        if (!cams?.length) throw new Error('Tidak ada kamera');
        const back = cams.find(c => /back|rear|environment/i.test(c.label));
        return (back || cams[0]).id;
    }

    async function startQRSharp() {
        const cameraId = await pickBackCameraId();

        // Start seperti biasa (pakai config kamu)
        await html5QrCode.start(cameraId, buildConfig(), qrCodeSuccessCallback);

        // Naikkan kualitas (Safari iOS biasanya support sebagian besar ini)
        try {
            await html5QrCode.applyVideoConstraints({
            width:     { ideal: 1920 },   // target 1080p
            height:    { ideal: 1080 },
            frameRate: { ideal: 30, max: 30 },
            advanced: [{ focusMode: "continuous" }]
            });
        } catch(_) { /* fine kalau tidak didukung */ }
    }
    async function stopCameraIfAny() {
        if (showCamera) {
            try { await html5QrCode.stop(); } catch(_) {}
            try { await html5QrCode.clear(); } catch(_) {}
            showCamera = false;
        }
    }
    async function startCamera() {
        // mulai scan kamera (pakai facingMode; kalau mau pakai cameraId bisa diupgrade)
        const cfg = buildConfig();
        await html5QrCode.start({ facingMode: "environment" }, cfg, qrCodeSuccessCallback);
        showCamera = true;
    }
    var qrCodeDisplay = () => {
        html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback);
        showCamera = true;
    }

    const refreshTextarea = () => {
        $('#txtTags').text(tags);
        if (count > 5) $('#txtTags').attr('rows', count);
        $badge.text(count + ' item');
    };

    function addTagValue(value) {
        const v = String(value || '').trim();
        if (!v) return false;
        if (jQuery.inArray(v, tags_array) !== -1) return false; // hindari duplikat
        tags_array.push(v);
        tags += v + '\n';
        count += 1;
        refreshTextarea();
        return true;
    }

    var isiTags = (value) => { addTagValue(value); };

    var qrCodeSuccessCallback = (decodedText, decodedResult) => {
        $(this).uiSound({play: "success"});
        isiTags(decodedText);
    };

    // INPUT MANUAL handlers
    function addManual() {
        const ok = addTagValue($('#txtManual').val());
        if (!ok) { setErrorMessage('Item ID kosong atau sudah ada di daftar.'); return; }
        $('#txtManual').val('');
        setSuccessMessage('Item ID ditambahkan.');
    }
    $('#btnAddManual').on('click', addManual);
    $('#txtManual').on('keypress', function(e){ if(e.which === 13){ e.preventDefault(); addManual(); } });

    // NFC
    function startNfc() {
        if (!('NDEFReader' in window)) {
            setErrorMessage('Fitur NFC tidak didukung pada perangkat Anda.');
            return;
        }
        const ndef = new NDEFReader();
        ndef.scan().then(() => {
            ndef.onreadingerror = () => setErrorMessage('Tidak bisa membaca NFC. Coba lagi.');
            ndef.onreading = event => {
            $(this).uiSound({play: "success"});
            let sn  = event.serialNumber.toString();
            let reversedSerial = sn.split(":").reverse().join("").toUpperCase();
            addTagValue(reversedSerial);
            setSuccessMessage('NFC terbaca.');
            };
        }).catch(err => setErrorMessage('Scan NFC gagal: ' + err));
    }
    async function setMode(newMode) {
        if (typeof _busy !== 'undefined' && _busy) return; // hormati busy
        _resetMsg();
        // UI state
        $('#btnModeQR').toggleClass('active', newMode === 'QR');
        $('#btnModeBAR').toggleClass('active', newMode === 'BAR');
        $('#btnModeNFC').toggleClass('active', newMode === 'NFC');

        MODE = newMode;

        if (MODE === 'NFC') {
            jenis = 'RFID';
            $('#formRFID').show();
            // matikan kamera dulu
            await stopCameraIfAny();
            // mulai NFC
            startNfc();
        } else {
            jenis = 'BARQR';
            $('#formRFID').hide();
            // restart kamera dengan config sesuai mode
            await stopCameraIfAny();
            try {
            await startCamera();
            } catch(e) {
            setErrorMessage('Kamera tidak bisa diakses: ' + (e?.message || e));
            }
        }
    }
    // tombol mode
    $('#btnModeQR').on('click', ()=> setMode('QR'));
    $('#btnModeBAR').on('click', ()=> setMode('BAR'));
    $('#btnModeNFC').on('click', ()=> setMode('NFC'));

    // --- helper loading tombol ---
const $btnSimpan = $('#btnSimpan');
const $btnHapus  = $('#btnHapus');

function setBtnLoading(on, text = 'Menyimpan...') {
  if (on) {
    $btnSimpan
      .prop('disabled', true)
      .html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + text);
    $btnHapus.prop('disabled', true);
  } else {
    $btnSimpan.prop('disabled', false).text('Simpan Data');
    $btnHapus.prop('disabled', false);
  }
}

// ==== IKON (Bootstrap Icons) ====
const ICON_OK  = '<i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>';
const ICON_ERR = '<i class="bi bi-x-circle-fill me-2" aria-hidden="true"></i>';

// ==== ELEM & TIMER ====
const $msg = $('#lblMsg');
let _flashTimer = null, _hideTimer = null;

// ==== RESET HELPER ====
function _resetMsg() {
  if (_flashTimer) { clearTimeout(_flashTimer); _flashTimer = null; }
  if (_hideTimer)  { clearTimeout(_hideTimer);  _hideTimer  = null; }

  // pastikan terlihat & siap dipakai
  $msg
    .stop(true, true)
    .show()
    .attr({'role':'alert','aria-live':'polite'})
    // bersihkan kelas yang berpotensi bentrok
    .removeClass('text-white alert alert-success alert-danger flash-success flash-error')
    .html('');
}

// ==== SCROLL HELPER ====
function _scrollTo($el) {
  const y = $el.offset()?.top || 0;
  window.scrollTo({ top: y - 80, behavior: 'smooth' });
}

// ==== PESAN ERROR ====
function setErrorMessage(text, { scroll = true, autoHideMs = 0 } = {}) {
  _resetMsg();
  $msg
    .addClass('alert alert-danger flash-error')
    .html(ICON_ERR + (text || 'Terjadi kesalahan.'));
  if (scroll) _scrollTo($msg);

  // hapus efek flash saja (styling alert tetap)
  _flashTimer = setTimeout(() => $msg.removeClass('flash-error'), 1200);

  // auto-hide jika diminta
  if (autoHideMs && autoHideMs > 0) {
    _hideTimer = setTimeout(() => $msg.fadeOut(200), autoHideMs);
  }
}

// ==== PESAN SUKSES ====
function setSuccessMessage(text, { scroll = true, autoHideMs = 2500 } = {}) {
  _resetMsg();
  $msg
    .addClass('alert alert-success flash-success')
    .html(ICON_OK + (text || 'Berhasil.'));
  if (scroll) _scrollTo($msg);

  _flashTimer = setTimeout(() => $msg.removeClass('flash-success'), 1200);

  if (autoHideMs && autoHideMs > 0) {
    _hideTimer = setTimeout(() => $msg.fadeOut(200), autoHideMs);
  }
}
const $btnNfcEn  = $('#btnNfcEnable');
const $btnNfcDis = $('#btnNfcDisable');
const $btnAddMan = $('#btnAddManual');
const $txtManual = $('#txtManual');
let _busy = false;
// kunci/lepaskan UI (handle <button> dan <span class="btn">)
function setUIBusy(on) {
    _busy = on;
  // elemen yang native bisa disabled
  $btnSimpan.prop('disabled', on);
  $btnHapus.prop('disabled', on);
  $btnAddMan.prop('disabled', on);
  $txtManual.prop('disabled', on);

  // elemen <span class="btn ..."> yang tidak punya properti disabled
  [$btnNfcEn, $btnNfcDis].forEach($el => {
    if (on) $el.addClass('disabled').attr('aria-disabled', 'true');
    else    $el.removeClass('disabled').removeAttr('aria-disabled');
  });
}
// --- handler SIMPAN (pakai loading + sorot pesan) ---
$('#btnSimpan').on('click', function () {
  if (!tags_array.length) {
    setErrorMessage('Belum ada data untuk disimpan.');
    return;
  }
  setUIBusy(true); 
  setBtnLoading(true);

  $.ajax({
    type: 'POST',
    url: '{{ url("shelving/save") }}',
    contentType: "application/json; charset=utf-8",
    dataType: 'json',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
    data: JSON.stringify({
      "stockopnameid" : stockopname_id,
      "location_shelf_id": setlocation_shelf_id,
      "location_rugs_id":  setlocation_rugs_id,
      "location_id":       setlocation_id,
      "listdata":          tags_array,
      "jenis":             jenis,
    }),
    success: function() {
      setBtnLoading(false);
      setUIBusy(false);
      setSuccessMessage('Data koleksi berhasil disimpan!');
      // reset daftar
      tags = ""; tags_array = []; count = 0;
      $('#txtTags').attr('rows',5).text('');
      $('#badgeCount').text('0 item');
    },
    error: function(xhr){
      setBtnLoading(false);
      setUIBusy(false);
      const msg = xhr?.responseJSON?.Message || 'Terjadi kesalahan';
      setErrorMessage('Gagal menyimpan! ' + msg);
    }
  });
});
$('#txtManual').on('keydown', e => { if (_busy) e.preventDefault(); });
// --- handler HAPUS (sedikit rapi) ---
$('#btnHapus').on('click', function(){
  if(!tags_array.length) {
    setErrorMessage('Tidak ada data untuk dihapus.');
    return;
  }
  if(!confirm('Hapus semua data yang sudah dipindai?')) return;
  tags = ""; tags_array = []; count = 0;
  $('#txtTags').attr('rows',5).text('');
  $('#badgeCount').text('0 item');
  setSuccessMessage('Daftar berhasil dikosongkan.');
  setUIBusy(false);
});

    // Start default barcode scanner
    //qrCodeDisplay();
    setMode(MODE);
</script>
@endsection
