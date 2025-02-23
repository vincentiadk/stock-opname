@if(session('user') !== null) 
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