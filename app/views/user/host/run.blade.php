@extends('layouts.side39')

@section('body')

<h2>@lang('host.run')</h2>
<hr>

<div class="alert alert-info">
  <h3>@lang('host.confirm_run')</h3>
  {{ Form::open(array('url'=>'host/'.$host.'/run')) }}
    <button type="submit" class="btn btn-primary">@lang('host.yes')</button>
    <a href="{{ URL::to('host/host') }}" class="btn btn-default">@lang('host.cancel')</a>
  {{ Form::close() }}
</div>
  
@stop