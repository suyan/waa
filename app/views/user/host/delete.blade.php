@extends('layouts.side39')

@section('body')

<h2>@lang('host.delete')</h2>
<hr>

<div class="alert alert-danger">
  <h3>@lang('host.confirm_delete')</h3>
  {{ Form::open(array('url'=>'host/'.$host.'/delete')) }}
    <button type="submit" class="btn btn-danger">@lang('host.yes')</button>
    <a href="{{ URL::to('host/host') }}" class="btn btn-default">@lang('host.cancel')</a>
  {{ Form::close() }}
  
</div>
  
@stop