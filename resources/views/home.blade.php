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
                <a
                    href="#"
                    class="button"
                    data-bs-toggle="modal"
                    data-bs-target="#depositActionSheet"
                >
                    <ion-icon
                        name="add-outline"
                        role="img"
                        class="md hydrated"
                        aria-label="add outline"
                    ></ion-icon>
                </a>
            </div>
        </div>
        <!-- * Balance -->
        <!-- Wallet Footer -->
        <div class="wallet-footer">
            <div class="item">
                <a
                    href="#"
                    data-bs-toggle="modal"
                    data-bs-target="#withdrawActionSheet"
                >
                    <div class="icon-wrapper bg-danger">
                        <ion-icon
                            name="arrow-down-outline"
                            role="img"
                            class="md hydrated"
                            aria-label="arrow down outline"
                        ></ion-icon>
                    </div>
                    <strong>Withdraw</strong>
                </a>
            </div>
            <div class="item">
                <a
                    href="#"
                    data-bs-toggle="modal"
                    data-bs-target="#sendActionSheet"
                >
                    <div class="icon-wrapper">
                        <ion-icon
                            name="arrow-forward-outline"
                            role="img"
                            class="md hydrated"
                            aria-label="arrow forward outline"
                        ></ion-icon>
                    </div>
                    <strong>Send</strong>
                </a>
            </div>
            <div class="item">
                <a href="app-cards.html">
                    <div class="icon-wrapper bg-success">
                        <ion-icon
                            name="card-outline"
                            role="img"
                            class="md hydrated"
                            aria-label="card outline"
                        ></ion-icon>
                    </div>
                    <strong>Cards</strong>
                </a>
            </div>
            <div class="item">
                <a
                    href="#"
                    data-bs-toggle="modal"
                    data-bs-target="#exchangeActionSheet"
                >
                    <div class="icon-wrapper bg-warning">
                        <ion-icon
                            name="swap-vertical"
                            role="img"
                            class="md hydrated"
                            aria-label="swap vertical"
                        ></ion-icon>
                    </div>
                    <strong>Exchange</strong>
                </a>
            </div>
        </div>
        <!-- * Wallet Footer -->
    </div>
</div>
@endsection
