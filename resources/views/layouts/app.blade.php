<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistem BSEE</title>
  <link rel="shortcut icon" type="image/png" href="{{asset('template/assets/images/logos/BSEE.jpg')}}" />
  @include('layouts.style')
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
    <aside class="left-sidebar">
      <!-- Sidebar scroll-->
      <div>
        <div class="brand-logo d-flex justify-content-between align-items-center">
          <div class="w-100 mt-3 text-center">
            <a href="{{route('dashboard')}}" class="logo-img">
              <img src="{{ asset('template/assets/images/logos/BSEE.jpg') }}" width="90" alt="" />
            </a>
          </div>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer ms-auto" id="sidebarCollapse">
            <i class="ti ti-x fs-8"></i>
          </div>
        </div>

        <!-- Sidebar navigation-->
        @include('layouts.sidebar')
        <!-- End Sidebar navigation -->
      </div>
      <!-- End Sidebar scroll-->
    </aside>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      @include('layouts.header')
      <!--  Header End -->
      <div class="container-fluid">
        <!--  Row 1 -->
        @yield('content')

        <!-- <div class="py-6 px-6 text-center">
          <p class="mb-0 fs-4">Design and Developed by <a href="https://adminmart.com/" target="_blank" class="pe-1 text-primary text-decoration-underline">AdminMart.com</a> Distributed by <a href="https://themewagon.com">ThemeWagon</a></p>
        </div> -->
      </div>
    </div>
  </div>
  @include('layouts.script')
</body>

</html>