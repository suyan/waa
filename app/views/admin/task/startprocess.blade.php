@extends('layouts.side39')

@section('body')

<h2>@lang('admin.process.start')</h2>
<hr>

<div class="alert alert-info">
  <h3>@lang('admin.process.confirm_start')</h3>
  {{ Form::open(array('url'=>'admin/task/'.$process.'/start')) }}
    <button type="submit" class="btn btn-primary">@lang('host.yes')</button>
    <a href="{{ URL::to('admin/task/process') }}" class="btn btn-default">@lang('host.cancel')</a>
  {{ Form::close() }}
  
</div>    

@stop