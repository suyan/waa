@extends('layouts.side39')

@section('body')
<table class="table table-striped">
  <thead>
    <tr>
      <th>@lang('admin.name')</th>
      <th>@lang('admin.created_at')</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($permissions as $permission)
      <tr>
        <td>{{ $permission->name }}</td>  
        <td>{{ $permission->created_at }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<div class="text-center">
  {{ $permissions->links() }}  
</div>
@stop