@extends('layouts.default')

@section('body')
<div class="container">
  <div class="row">
    <div class="col-md-6 col-md-offset-3">
      <div class="row">
        <div class="col-md-12">
          <div class="panel panel-default">
            <div class="panel-heading">@lang('user.sign_in')</div>
            <div class="panel-body">
              {{ Form::open(array('url' => 'user/login', 'id' => 'login', 'class' => 'form-horizontal')) }}
                @if ( Session::get('error') )
                  <p class="bg-danger">{{ Session::get('error') }}</p>    
                @endif
                <div class="form-group {{ $errors->first('email') ? 'has-error' : '' }}">
                  <label for="loginEmail" class="col-md-2 control-label">@lang('user.email')</label>
                  <div class="col-md-10">
                    <input type="email" name="email" class="form-control" id="loginEmail" placeholder="email@email.com" value="{{ Input::old('email') }}">
                    @if ($errors->first('email'))
                      <small class="help-block">{{ $errors->first('email') }}</small>  
                    @endif                    
                  </div>
                </div>
                <div class="form-group {{ $errors->first('password') ? 'has-error' : '' }}">
                  <label for="loginPassword" class="col-md-2 control-label">@lang('user.password')</label>
                  <div class="col-md-10">
                    <input type="password" name="password" class="form-control" id="loginPassword" placeholder="password">  
                    @if ($errors->first('password'))
                      <small class="help-block">{{ $errors->first('password') }}</small>
                    @endif
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                      <label>
                        <input type="checkbox" name="remember" value="1">@lang('user.remember_me')
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-default">@lang('user.sign_in')</button>  
                  </div>
                </div>
              {{ Form::close() }}                
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@stop