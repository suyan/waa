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
    @foreach ($roles as $role)
      <tr>
        <td>{{ $role->name }}</td>  
        <td>{{ $role->created_at }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<div class="text-center">
  {{ $roles->links() }}  
</div>
@stop