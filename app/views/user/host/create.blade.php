@extends('layouts.side39')

@section('body')
<h1>创建主机</h1>
<hr>
<div class="col-md-12">
  {{ Form::open(array('url' => 'host/create', 'files' => true, 'id' => 'login', 'class' => 'form-horizontal')) }}
    @if (isset($error))
      <p class="bg-danger">$error</p>    
    @endif
    <div class="form-group {{ $errors->get('hostname') ? 'has-error' : '' }}">
      <label for="hostname" class="col-md-2 control-label">@lang('host.hostname')</label>
      <div class="col-md-10">
        <input type="text" name="hostname" class="form-control" id="hostname" placeholder="hostname" value="{{ Input::old('hostname') }}">
        <small class="help-block">{{ head($errors->get('hostname')) }}</small>
      </div>
    </div>
    <div class="form-group {{ $errors->get('domain') ? 'has-error' : '' }}">
      <label for="domain" class="col-md-2 control-label">@lang('host.domain')</label>
      <div class="col-md-10">
        <input type="text" name="domain" class="form-control" id="domain" placeholder="www.baidu.com or 233.34.1.12" value="{{ Input::old('domain') }}">
        <small class="help-block">{{ head($errors->get('domain')) }}</small>
      </div>
    </div>
    <div class="form-group {{ $errors->get('description') ? 'has-error' : '' }}">
      <label for="description" class="col-md-2 control-label">@lang('host.description')</label>
      <div class="col-md-10">
        <input type="text" name="description" class="form-control" id="description" placeholder="description" value="{{ Input::old('description') }}">  
        <small class="help-block">{{ head($errors->get('description')) }}</small>
      </div>
    </div>
    <div class="form-group {{ $errors->get('uploadfile') ? 'has-error' : '' }}">
      <label for="uploadfile" class="col-md-2 control-label">@lang('host.uploadfile')</label>
      <div class="col-md-10">
        <input type="file" id="uploadfile" name="uploadfile"> 
        <small class="help-block">{{ head($errors->get('uploadfile')) }}</small>
      </div>
    </div>
    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10">
        <button type="submit" class="btn btn-default">@lang('host.submit')</button>  
      </div>
    </div>
  {{ Form::close() }}                
</div> 
@stop