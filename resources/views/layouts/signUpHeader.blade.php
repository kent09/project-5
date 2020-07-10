<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta content="" name="description">
        <meta content="" name="author">
        <title>FusedTools</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css"  >
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

        <!-- Styles -->
        <link rel="stylesheet"  href="{{url('assets/css/style_tools.css')}}" crossorigin="anonymous">
        <link rel="stylesheet"  href="{{url('assets/css/token-input.css')}}" crossorigin="anonymous">
        <link rel="stylesheet"  href="{{url('assets/css/bootstrap.min.css')}}" crossorigin="anonymous">
        <link rel="stylesheet"  href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css" crossorigin="anonymous">
    </head>
    <body id="app-layout" >
        <div class="main">
            @yield('content')
        </div>
        <!-- jQuery -->
{{--        <script href="{{url('assets/js/jquery.js')}}"></script>--}}

        <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"  crossorigin="anonymous"></script>
        <script href="{{url('assets/js/jquery.validate.min.js')}}"></script>
        <script href="{{url('assets/js/bootstrap.min.js')}}"></script>
        <script href="{{url('assets/js/jquery.tokeninput.js')}}"></script>
        <script>
            var siteUrl = "{{ url('/') }}";
        </script>
        <script src="{{ URL::to('assets/js/notty_sync.js')}}"></script>
    </body>
</html>