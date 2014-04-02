@extends('layouts.side39')

@section('body')
  <div class="row">
    <h3>@lang('host.detail')</h3>
    <ul class="nav nav-tabs">
      <li><a href="{{ URL::to('admin/host/'.$hostId.'/info') }}">基本信息</a></li>
      <li class="active"><a>攻击向量</a></li>
    </ul>
    <br>
    <div class="col-md-12">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>@lang('admin.vector.client')</th>
            <th>@lang('admin.vector.impact')</th>
            <th>@lang('admin.vector.tags')</th>
            <th>@lang('admin.vector.quantification')</th>
            <th>@lang('admin.vector.status')</th>
            <th>@lang('admin.vector.request')</th>
            <th>@lang('admin.vector.bytes')</th>
            <th>@lang('admin.vector.city')</th>
            <th>@lang('admin.vector.date')</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($vectors as $vector)
            <tr class="{{ $vector->impact > 10 ? 'danger' : ''}}">
              <td>{{ $vector->client }}</td>  
              <td>{{ $vector->impact }}</td>
              <td>{{ $vector->tags }}</td>
              <td>{{ $vector->quantification }}</td>
              <td>{{ $vector->status }}</td>
              <td width="20%"><input style="width:100%" type="text" disabled value="{{{ $vector->request }}}"></td>
              <td>{{ $vector->bytes }}</td>
              <td>{{ $vector->remote_city }}</td>
              <td>{{ $vector->date }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>

      <div class="text-center">
        <?php echo $vectors->links(); ?>  
      </div>
    </div>
  </div>
@stop

@section('js')
<script>
  $(function(){
    $('.request').tooltip();
  });
</script>
@stop