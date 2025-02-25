@extends('layouts.index') @section('content')
<div class="section wallet-card-section pt-1">
    <div class="wallet-card">
        <!-- Balance -->
        <div class="balance">
            <div class="left">
                <span class="title">Selamat Datang</span>
                <h1 class="total">{{session('user')['username'] }}</h1>
            </div>
            <div class="right">
                
            </div>
        </div>
        <!-- * Balance -->
        <!-- Wallet Footer -->
        <div class="wallet-footer">
            <div class="form-group">
            @if($setting)
                @if(! is_null($setting->stockopname_id))
                <label class="mb-1"><strong>Nama Project : </strong></label><label class="bg-secondary"> {{ $setting->stockopname_name }}</label>
                @endif
                <label><strong>Lokasi :</strong> </label>
                @if(! is_null($setting->location_id))
                    <label class="bg-info">{{ $setting->location_name }} </label>
                    @if(! is_null($setting->location_shelf_id))
                    <label class="bg-info">{{ $setting->location_shelf_name }}</label>
                    @endif
                    @if(! is_null($setting->location_rugs_id))
                    <label class="bg-info">{{ $setting->location_rugs_name }} </label><a href="{{ url('/setting') }}" class="btn btn-primary btn-sm">Ubah lokasi</a>
                    @else
                
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
        <!-- * Wallet Footer -->
    </div>
</div>
@endsection
