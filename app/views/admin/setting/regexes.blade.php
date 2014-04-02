@extends('layouts.side39')

@section('body')
<table class="table table-striped">
  <thead>
    <tr>
      <th>@lang('admin.regex.id')</th>
      <th>@lang('admin.regex.regex')</th>
      <th>@lang('admin.regex.description')</th>
      <th>@lang('admin.regex.tags')</th>
      <th>@lang('admin.regex.impact')</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($settings as $setting)
      <tr>
        <td>{{ $setting['id'] }}</td>  
        <td width="50%"><input style="width:100%" type="text" disabled value="{{{ $setting['rule'] }}}"></td>
        <td>{{ $setting['description'] }}</td>
        <td>
          @if (is_string($setting['tags']['tag']))
            {{ $setting['tags']['tag'] }}
          @elseif (is_array($setting['tags']['tag']))
            {{ implode(',', $setting['tags']['tag']) }}
          @endif
        </td>
        <td>{{ $setting['impact'] }}</td>
      </tr>
    @endforeach
  </tbody>
</table>
@stop