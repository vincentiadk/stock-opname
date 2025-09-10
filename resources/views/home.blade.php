@extends('layouts.index')

@section('content')
<div class="container py-3">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <div class="text-muted small">Selamat Datang</div>
          <h3 class="mb-0">{{ session('user')['username'] }}</h3>
        </div>
        {{-- tempat notif/avatar jika diperlukan --}}
      </div>

      @php
        $hasSetting     = isset($setting) && $setting !== null;
        $hasProject     = $hasSetting && !is_null($setting->stockopname_id);
        $hasLoc         = $hasSetting && !is_null($setting->location_id);
        $hasShelf       = $hasSetting && !is_null($setting->location_shelf_id);
        $hasRugs        = $hasSetting && !is_null($setting->location_rugs_id);
        $isLocComplete  = $hasLoc && $hasShelf && $hasRugs;
      @endphp

      <div class="mb-3">
        @if($hasProject)
          <div class="mb-2">
            <span class="fw-semibold me-2">📂 Project:</span>
            <span class="badge bg-light text-dark"><h3>{{ $setting->stockopname_name }}</h3></span>
          </div>
        @endif

        <div class="mb-2">
          <span class="fw-semibold me-2">📍 Lokasi:</span>
          @if($hasLoc)
            <span class="badge bg-info text-dark">{{ $setting->location_name }}</span>
            @if($hasShelf)
              <span class="badge bg-info text-dark">{{ $setting->location_shelf_name }}</span>
            @endif
            @if($hasRugs)
              <span class="badge bg-info text-dark">{{ $setting->location_rugs_name }}</span>
            @endif
          @else
            <span class="text-muted">Belum diatur</span>
          @endif
        </div>

        @if($isLocComplete)
          <div class="d-flex align-items-center gap-2">
            <span class="badge text-bg-success">lengkap</span>
            <a href="{{ url('/setting') }}" class="btn btn-primary btn-sm px-3 rounded-pill">Ubah lokasi</a>
          </div>
        @else
          <div class="alert alert-warning d-flex align-items-center mt-2" role="alert">
            <span class="me-2">⚠</span>
            <div>Anda belum mengatur lokasi dengan lengkap.</div>
            <a href="{{ url('/setting') }}" class="btn btn-primary btn-sm ms-auto">Atur lokasi</a>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
