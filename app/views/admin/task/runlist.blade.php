@extends('layouts.side39')

@section('body')
<table class="table table-striped">
  <thead>
    <tr>
      <th>@lang('host.hostname')</th>
      <th>@lang('host.domain')</th>
      <th>@lang('host.description')</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($hosts as $host)
      <tr>
        <td>{{ $host->hostname }}</td>  
        <td>{{ $host->domain }}</td>
        <td>{{ $host->description }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<div class="text-center">
  <?php echo $hosts->links(); ?>  
</div>
@stop