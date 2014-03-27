@extends('layouts.side39')

@section('body')
<br>
<p>
  <a type="button" class="btn btn-primary" href="{{ URL::to('host/create')}}">
    @lang('host.create')
  </a>
</p>
<hr>

<table class="table table-striped">
  <thead>
    <tr>
      <th>@lang('host.hostname')</th>
      <th>@lang('host.domain')</th>
      <th>@lang('host.description')</th>
      <th>@lang('host.log')</th>
      <th>@lang('host.process')</th>
      <th>@lang('host.status')</th>
      <th>@lang('host.control')</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($hosts as $host)
      <tr>
        <td>{{ $host->hostname }}</td>  
        <td>{{ $host->domain }}</td>
        <td>{{ $host->description }}</td>
        <td>{{ $host->log }}</td>
        <td>{{ $host->process }}%</td>
        @if ($host->status == 0)
          <td>@lang('host.not_run')</td> 
          <td>
            <a href="{{ URL::to('host/'.$host->id.'/run') }}" class="btn btn-primary btn-xs">@lang('host.start')</a>
            <a href="{{ URL::to('host/'.$host->id.'/delete') }}" class="btn btn-danger btn-xs">@lang('host.delete')</a>
          </td>
        @elseif ($host->status == 1)
          <td>@lang('host.waiting')</td> 
          <td>
            <a href="" class="btn btn-default btn-xs" disabled="disabled">@lang('host.waiting')</a>
            <a href="" class="btn btn-danger btn-xs" disabled="disabled">@lang('host.delete')</a>
          </td>
        @elseif ($host->status == 2)
          <td>@lang('host.running')</td> 
          <td>
            <a href="" class="btn btn-default btn-xs" disabled="disabled">@lang('host.running')</a>
            <a href="" class="btn btn-danger btn-xs" disabled="disabled">@lang('host.delete')</a>
          </td>
        @elseif ($host->status == 3)
          <td>@lang('host.have_run')</td> 
          <td>
            <a href="{{ URL::to('host/'.$host->id.'/info') }}" class="btn btn-default btn-xs">@lang('host.info')</a>
            <a href="{{ URL::to('host/'.$host->id.'/delete') }}" class="btn btn-danger btn-xs">@lang('host.delete')</a>
          </td>
        @endif
      </tr>
    @endforeach
  </tbody>
</table>

<div class="text-center">
  <?php echo $hosts->links(); ?>  
</div>


@stop