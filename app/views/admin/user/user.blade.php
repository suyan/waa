@extends('layouts.side39')

@section('body')
<table class="table table-striped">
  <thead>
    <tr>
      <th>@lang('user.username')</th>
      <th>@lang('user.email')</th>
      <th>@lang('user.roles')</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($users as $user)
      <tr>
        <td>{{ $user->username }}</td>  
        <td>{{ $user->email }}</td>
        <td>
          @foreach($user->roles as $role)
            {{ $role->name }},
          @endforeach
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

<div class="text-center">
  {{ $users->links() }}  
</div>
@stop