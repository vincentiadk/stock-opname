@extends('layouts.index')
@section('content')
<div class="section mt-2 text-center">
    <h1>Log in INLIS</h1>
    <h4>Fill the form to log in with username INLIS Enterprise</h4>
</div>
<div class="section mb-5 p-2">
    <form action="{{ url('/login') }}" method="POST">
        @csrf
        <div class="card">
            <div class="card-body pb-1">
                <div class="form-group basic">
                    <div class="input-wrapper">
                        <label class="label" for="email1">Username</label>
                        <input type="text" class="form-control" id="username" placeholder="Your username" name="username">
                        <i class="clear-input">
                            <ion-icon name="close-circle"></ion-icon>
                        </i>
                    </div>
                </div>

                <div class="form-group basic">
                    <div class="input-wrapper">
                        <label class="label" for="password">Password</label>
                        <input type="password" class="form-control" id="password" autocomplete="off" name="password"
                            placeholder="Your password">
                        <i class="clear-input">
                            <ion-icon name="close-circle"></ion-icon>
                        </i>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-button-group  transparent" style="position:relative">
            <button type="submit" class="btn btn-primary btn-block btn-lg">Log in</button>
        </div>
    </form>
</div>
@endsection