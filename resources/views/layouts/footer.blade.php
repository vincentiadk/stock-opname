<div class="appBottomMenu">
        <a href="/" class="item  {{ request()->is('/') ? 'active' : '' }} ">
            <div class="col">
                <ion-icon name="pie-chart-outline"></ion-icon>
                <strong>Tagging</strong>
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