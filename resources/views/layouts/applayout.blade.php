<!DOCTYPE html>
<html lang="en" ng-app="app">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="/assets/images/favicon.ico"/>
    <meta content="" name="description">
    <meta content="" name="author">
    @hasSection('title')
    <title>@yield('title') - Fusedsuite</title>
    @else
    <title>FusedTools</title>
    @endif
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">
    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-notify/0.2.0/css/bootstrap-notify.min.css" crossorigin="anonymous">
    <link rel="stylesheet"  href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ url('assets/css/style.css')}}" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ url('assets/css/style_account.css')}}" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ url('assets/css/style_app.css')}}" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ url('assets/css/token-input.css')}}" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ url('assets/css/sidebar_tools.css')}}" crossorigin="anonymous">
    <script>
      var siteUrl = "{{ url('/') }}";
    </script>
    @yield('page-css')
  </head>
  <body id="app-layout" class="appaccount" style="">
    @include('layouts.headers.appheader')
    <div class="wrapper">

      <div id="content">
        
        @if ($errors->any() && !$errors->has('password'))
        <div class="alert alert-danger">
          <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        @endif
        <div class="content">
          @yield('content')
        </div>
      </div>
    </div>

    <div class="mt-5 pt-5 pb-5 subfooter">
      <div class="col copyright">
        <p class=""><small class="text-white-50">© Fusedsuite | Privacy Policy. Term & Conditions.</small></p>
      </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.7.0/jquery.validate.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"  crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-tokeninput/1.6.0/jquery.tokeninput.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.1/js/select2.min.js"></script>
    <script src="{{ URL::to('assets/js/sidebar.js') }}"></script>
    <script src="{{ URL::to('assets/js/init.js') }}"></script>
    @if (Auth::user())
    <script src="{{ URL::to('assets/js/notty_sync.js') }}"></script>
    @endif
    <!-- jQuery -->
    @yield('script')
  </body>
</html>