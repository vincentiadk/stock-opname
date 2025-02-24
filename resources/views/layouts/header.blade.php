@if(session('user') !== null && !request()->is('home')) 
<div class="appHeader">
    <div class="left">
        <a href="#" class="headerButton goBack">
            <ion-icon name="chevron-back-outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">{{$title}}</div>
    <div class="right"></div>
</div>
@endif
@if(session('user') !== null && request()->is('home') ) 
<div class="appHeader bg-primary text-light">
    <div class="left">
        <a href="#" class="headerButton" data-bs-toggle="modal" data-bs-target="#sidebarPanel">
            <ion-icon name="menu-outline" role="img" class="md hydrated" aria-label="menu outline"></ion-icon>
        </a>
    </div>
    <div class="pageTitle">
        <img src="{{ asset('assets/img/logo.svg') }}" alt="logo" class="logo" style="width:150px">
    </div>
    <div class="right">
        <a href="app-notifications.html" class="headerButton">
            <ion-icon class="icon md hydrated" name="notifications-outline" role="img" aria-label="notifications outline"></ion-icon>
            <span class="badge badge-danger">4</span>
        </a>
        <a href="app-settings.html" class="headerButton">
            <img src="assets/img/sample/avatar/avatar1.jpg" alt="image" class="imaged w32">
            <span class="badge badge-danger">6</span>
        </a>
    </div>
</div>
@endif