@extends('layouts.side39')

@section('body')
<table class="table table-striped">
  <thead>
    <tr>
      <th>@lang('admin.process.name')</th>
      <th>@lang('admin.process.group')</th>
      <th>@lang('admin.process.description')</th>
      <th>@lang('admin.process.start_time')</th>
      <th>@lang('admin.process.status')</th>
      <th>@lang('admin.process.control')</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($processes as $process)
      <tr>
        <td>{{ $process['name'] }}</td>
        <td>{{ $process['group'] }}</td>
        <td>{{ $process['description'] }}</td>
        <td>{{ Carbon::createFromTimeStamp($process['start']) }}</td>
        <td>{{ $process['statename'] }}</td>
        <td>
          @if ($process['state'] == 10) 
            <a class="btn btn-info btn-xs" disabled="disabled">@lang('admin.process.starting')</a>
          @elseif ($process['state'] == 20)
            <a href="{{ URL::to('admin/task/'.$process['name'].'/stop') }}" class="btn btn-danger btn-xs">@lang('admin.process.stop')</a>
          @elseif ($process['state'] == 40)
            <a class="btn btn-info btn-xs" disabled="disabled">@lang('admin.process.stoping')</a>
          @elseif ($process['state'] == 100 || $process['state'] == 200 || $process['state'] == 0)
            <a href="{{ URL::to('admin/task/'.$process['name'].'/start') }}" class="btn btn-primary btn-xs">@lang('admin.process.start')</a>
          @endif
        </td>
      </tr>
    @endforeach
  </tbody>
</table>
@stop