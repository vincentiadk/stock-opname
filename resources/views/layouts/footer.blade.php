@if(session('user') !== null) 
<div class="appBottomMenu">
        <a href="/home" class="item  {{ request()->is('/') ? 'active' : '' }} ">
            <div class="col">
                <ion-icon name="home-outline"></ion-icon>
                <strong>Beranda</strong>
            </div>
        </a>
        <a href="/tagging" class="item  {{ request()->is('/') ? 'active' : '' }} ">
            <div class="col">
                <ion-icon name="scan-outline"></ion-icon>
                <strong>Tagging</strong>
            </div>
        </a>
        <a href="/stock-opname" class="item  {{ request()->is('/stock-opname') ? 'active' : '' }} ">
            <div class="col">
                <ion-icon name="library-outline"></ion-icon>
                <strong>Stock Opname</strong>
            </div>
        </a>
        <a href="/rak" class="item {{ request()->is('/rak') ? 'active' : '' }}">
            <div class="col">
                <ion-icon name="document-text-outline"></ion-icon>
                <strong>Rak</strong>
            </div>
        </a>
        <a href="/report" class="item {{ request()->is('/report') ? 'active' : '' }}">
            <div class="col">
                <ion-icon name="apps-outline"></ion-icon>
                <strong>Report</strong>
            </div>
        </a>
        <a href="/setting" class="item {{ request()->is('/setting') ? 'active' : '' }}">
            <div class="col">
                <ion-icon name="settings-outline"></ion-icon>
                <strong>Settings</strong>
            </div>
        </a>
        <a href="/logout" class="item">
            <div class="col">
                <ion-icon name="log-out"></ion-icon>
                <strong>Logout</strong>
            </div>
        </a>
        
</div>
@endif