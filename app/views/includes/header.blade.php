<div class="navbar navbar-default navbar-inverse navbar-fixed-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" 
              data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      @if(Auth::guest())
        <a class="navbar-brand" href="{{ URL::to('about') }}">@lang('home.logo')</a>
      @else
        @if(Auth::user()->hasRole('admin'))
          <a class="navbar-brand" href="{{ URL::to('admin') }}">@lang('home.logo')</a>
        @else
          <a class="navbar-brand" href="{{ URL::to('/') }}">@lang('home.logo')</a>
        @endif
      @endif
    </div>
    <div class="collapse navbar-collapse">
      <ul class="nav navbar-nav">
        @foreach ($topNav as $nav)  
          <li class="{{ $nav['class'] }}"><a href="{{ URL::to($nav['url']) }}">@lang($nav['name'])</a></li>
        @endforeach
      </ul>
      <ul class="nav navbar-nav navbar-right">
        @if (Auth::check())
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">{{ Auth::user()->username }} <b class="caret"></b></a>
            <ul class="dropdown-menu">
              @if(Auth::user()->hasRole('admin'))
                <li><a href="{{ URL::to('/') }}">@lang('home.user_home')</a></li>
                <li><a href="{{ URL::to('admin') }}">@lang('home.admin_home')</a></li>
              @endif
              <li class="divider"></li>
              <li><a href="{{ URL::to('user/logout') }}">@lang('user.logout')</a></li>
            </ul>
          </li>
        @else
          <li><a href="{{ URL::to('user/create') }}">@lang('user.sign_up')</a></li>
          <li><a href="{{ URL::to('user/login') }}">@lang('user.sign_in')</a></li>
        @endif
      </ul>
    </div>
  </div>
</div>