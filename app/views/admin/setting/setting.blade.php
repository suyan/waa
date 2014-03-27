@extends('layouts.side39')

@section('body')
<table class="table table-striped">
  <thead>
    <tr>
      <th>@lang('admin.name')</th>
      <th>@lang('admin.display_name')</th>
      <th>@lang('admin.description')</th>
      <th>@lang('admin.value')</th>
      <th>@lang('admin.created_at')</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($settings as $setting)
      <tr>
        <td>{{ $setting->name }}</td>  
        <td>{{ $setting->display_name }}</td>
        <td>{{ $setting->description }}</td>
        <td>{{ $setting->value }}</td>
        <td>{{ $setting->created_at }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<div class="text-center">
  {{ $settings->links() }}  
</div>
@stop