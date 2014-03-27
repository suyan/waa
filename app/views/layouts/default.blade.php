<!doctype html>
<html>
<head>
    @include('includes.head')
    @yield('css')
</head>
<body>
<div id="wrap">
  @section('header')
    @include('includes.header')
  @show

  <!-- Begin page content -->
  @section('body')
    <div class="container"></div>
  @show
</div>

<div id="footer">
  @section('footer')
    @include('includes.footer')
  @show
</div>
{{ HTML::script('assets/js/app.js') }}
@yield('js')
</body>
</html>
