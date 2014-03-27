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
  <div class="container">
    <div class="row">
      <div class="col-md-3">
        @section('lside')
          <hr>
          <div class="list-group">
            @foreach ($leftNav as $nav)
              <a href="{{ URL::to($nav['url']) }}" class="list-group-item {{ $nav['class'] }}">@lang($nav['name'])</a>
            @endforeach
          </div>
          <!-- <ul class="nav nav-pills nav-stacked">
            @foreach ($leftNav as $nav)
              <li class="{{ $nav['class'] }}">
                <a href="{{ URL::to($nav['url']) }}">@lang($nav['name'])</a>
              </li>
            @endforeach
          </ul> -->
        @show
      </div>
      <div class="col-md-9">
        @if (Session::get('error'))
          <div class="alert alert-danger fade in">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            {{ Session::get('error') }}
          </div>
        @endif
        @yield('body')
      </div>
    </div>
  </div>
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
