@extends('layouts.side39')

@section('body')
<br>
<p>
  <a type="button" class="btn btn-primary" href="{{ URL::to('home/host/create')}}">
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
      <tr id="host_{{ $host->id }}">
        <td class="hide host_id">{{ $host->id }}</td>
        <td>{{ $host->hostname }}</td>  
        <td>{{ $host->domain }}</td>
        <td>{{ $host->description }}</td>
        <td id="log_{{ $host->id }}">{{ $host->log }}</td>
        <td id="process_{{ $host->id }}">{{ $host->process }}%</td>

        <td class="status_{{ $host->id }}_0 {{ $host->status == 0 ?: 'hide' }}">@lang('host.not_run')</td> 
        <td class="status_{{ $host->id }}_0 {{ $host->status == 0 ?: 'hide' }}">
          <a href="{{ URL::to('home/host/'.$host->id.'/run') }}" class="btn btn-primary btn-xs">@lang('host.start')</a>
          <a href="{{ URL::to('home/host/'.$host->id.'/delete') }}" class="btn btn-danger btn-xs">@lang('host.delete')</a>
        </td>
        <td class="status_{{ $host->id }}_1 {{ $host->status == 1 ?: 'hide' }}">@lang('host.waiting')</td> 
        <td class="status_{{ $host->id }}_1 {{ $host->status == 1 ?: 'hide' }}">
          <a href="" class="btn btn-default btn-xs" disabled="disabled">@lang('host.waiting')</a>
          <a href="" class="btn btn-danger btn-xs" disabled="disabled">@lang('host.delete')</a>
        </td>
        <td class="status_{{ $host->id }}_2 {{ $host->status == 2 ?: 'hide' }}">@lang('host.running')</td> 
        <td class="status_{{ $host->id }}_2 {{ $host->status == 2 ?: 'hide' }}">
          <a href="" class="btn btn-default btn-xs" disabled="disabled">@lang('host.running')</a>
          <a href="" class="btn btn-danger btn-xs" disabled="disabled">@lang('host.delete')</a>
        </td>
        <td class="status_{{ $host->id }}_3 {{ $host->status == 3 ?: 'hide' }}">@lang('host.have_run')</td> 
        <td class="status_{{ $host->id }}_3 {{ $host->status == 3 ?: 'hide' }}">
          <a href="{{ URL::to('home/host/'.$host->id.'/info') }}" class="btn btn-default btn-xs">@lang('host.info')</a>
          <a href="{{ URL::to('home/host/'.$host->id.'/delete') }}" class="btn btn-danger btn-xs">@lang('host.delete')</a>
        </td>
        <td class="status_{{ $host->id }}_4 {{ $host->status == 4 ?: 'hide' }}">@lang('host.run_error')</td> 
        <td class="status_{{ $host->id }}_4 {{ $host->status == 4 ?: 'hide' }}">
          <a href="{{ URL::to('home/host/'.$host->id.'/run') }}" class="btn btn-primary btn-xs">@lang('host.restart')</a>
          <a href="{{ URL::to('home/host/'.$host->id.'/delete') }}" class="btn btn-danger btn-xs">@lang('host.delete')</a>
        </td>

      </tr>
    @endforeach
  </tbody>
</table>

<div class="text-center">
  <?php echo $hosts->links(); ?>  
</div>
@stop

@section('js')
<script>
  function getHost(){
    if($('.host_id').length == 0) return 0;
    
    url = '{{ URL::to('home/host') }}';
    ids = '';
    $('.host_id').each(function(index){ ids += $(this).text() + ','; });
    ids = '/'+ids+ '/host';
    url += ids;

    window.setTimeout(function(){
      $.get(url, function(data){
        if(data.code == 0) 
          return 0;

        for (var i = 0; i < data.hosts.length; i++) {
          $('#process_'+data.hosts[i].id).html(data.hosts[i].process+'%'); //调整进度
          $('#log_'+data.hosts[i].id).html(data.hosts[i].log); //调整日志
          $('td[class^=status_'+data.hosts[i].id+']').addClass('hide');
          $('.status_'+data.hosts[i].id+'_'+data.hosts[i].status).removeClass('hide');
        }

        getHost();
      });
    }, 5000);
  }

  $(function(){
    getHost();
  });
</script>
@stop