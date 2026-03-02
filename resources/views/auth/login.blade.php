<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link rel="icon" href="{{asset('template/assets/images/logos/BSEE.jpg')}}">
  @include('layouts/style')
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <div
      class="position-relative overflow-hidden radial-gradient min-vh-100 d-flex align-items-center justify-content-center">
      <div class="d-flex align-items-center justify-content-center w-100">
        <div class="row justify-content-center w-100">
          <div class="col-md-8 col-lg-6 col-xxl-3">
            <div class="card mb-0">
              <div class="card-body">
                <a href="#" class="text-nowrap logo-img text-center d-block py-3 w-100">
                  <img src="{{asset('template/assets/images/logos/BSEE.jpg')}}" width="90" alt="">
                </a>
                
                @if(session('error'))
                <script>
                  Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Username atau Password salah!',
                    showConfirmButton: true
                  });
                </script>
                @endif
                @if(session('success'))
                <script>
                  Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Login berhasil!',
                    showConfirmButton: true
                  });
                </script>
                @endif
                <h4 class="text-center"><strong>Login BSEE</strong></h4>
                <form action="{{route('login.process')}}" method="POST">
                  @csrf
                  @method('POST')
                  <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" aria-describedby="emailHelp">
                  </div>
                  <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password">
                  </div>
                  <!-- <div class="d-flex align-items-center justify-content-between mb-4">
                    <a class="text-primary fw-bold" href="./index.html">Forgot Password ?</a>
                  </div> -->
                  <button class="btn btn-primary w-100 py-8 fs-4 mb-4 rounded-2">Sign In</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @include('layouts/script')
</body>

</html>