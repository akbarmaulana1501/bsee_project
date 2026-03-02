<nav class="sidebar-nav scroll-sidebar" data-simplebar="">
    <ul id="sidebarnav">
        <li class="nav-small-cap">
            <i class="ti ti-dots nav-small-cap-icon fs-4"></i>
            <span class="hide-menu">Home</span>
        </li>

        <li class="sidebar-item">
            <a class="sidebar-link {{ Request::is('admin/dashboard') ? 'active' : '' }}"
                href="{{ route('dashboard') }}">
                <span><i class="ti ti-layout-dashboard"></i></span>
                <span class="hide-menu">Dashboard</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a class="sidebar-link {{ Request::is('admin/upload-data') ? 'active' : '' }}"
                href="{{ route('upload_data') }}">
                <span><i class="ti ti-database-import"></i></span>
                <span class="hide-menu">Upload Data</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link {{ Request::is('admin/update-data') ? 'active' : '' }}"
                href="{{ route('update_data') }}">
                <span><i class="ti ti-database-export"></i></span>
                <span class="hide-menu">Update Data</span>
            </a>
        </li>
        <!-- <li class="sidebar-item">
            <a class="sidebar-link {{ Request::is('admin/tampil-etl') ? 'active' : '' }}"
                href="{{ route('tampil_etl') }}">
                <span><i class="ti ti-database-export"></i></span>
                <span class="hide-menu">ETL</span>
            </a>
        </li> -->
    </ul>
</nav>