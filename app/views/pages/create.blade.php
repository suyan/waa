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
              {{ Form::open(array('url' => 'user/create', 'id' => 'login', 'class' => 'form-horizontal')) }}
                @if ( $errors->get('duplicated') )
                  <p class="bg-danger">@lang('user.duplicated')</p>    
                @endif
                <div class="form-group {{ $errors->get('username') ? 'has-error' : '' }}">
                  <label for="registerUsername" class="col-md-2 control-label">@lang('user.username')</label>
                  <div class="col-md-10">
                    <input type="text" name="username" class="form-control" id="registerUsername" placeholder="Username" value="{{ Input::old('username') }}">
                    <small class="help-block">{{ head($errors->get('username')) }}</small>
                  </div>
                </div>
                <div class="form-group {{ $errors->get('email') ? 'has-error' : '' }}">
                  <label for="registerEmail" class="col-md-2 control-label">@lang('user.email')</label>
                  <div class="col-md-10">
                    <input type="email" name="email" class="form-control" id="registerEmail" placeholder="email@email.com" value="{{ Input::old('email') }}">
                    <small class="help-block">{{ head($errors->get('email')) }}</small>
                  </div>
                </div>
                <div class="form-group {{ $errors->get('password') ? 'has-error' : '' }}">
                  <label for="registerPassword" class="col-md-2 control-label">@lang('user.password')</label>
                  <div class="col-md-10">
                    <input type="password" name="password" class="form-control" id="registerPassword" placeholder="password">  
                    <small class="help-block">{{ head($errors->get('password')) }}</small>
                  </div>
                </div>
                <div class="form-group {{ $errors->get('password') ? 'has-error' : '' }}">
                  <label for="registerPassword1" class="col-md-2 control-label">@lang('user.verify_password')</label>
                  <div class="col-md-10">
                    <input type="password" name="password1" class="form-control" id="registerPassword1" placeholder="password">  
                    <small class="help-block">{{ head($errors->get('password1')) }}</small>
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-default">@lang('user.sign_up')</button>  
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