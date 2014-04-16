@extends('layouts.side39')

@section('body')

@foreach($activities as $activity)
  <?php $lang = json_decode($activity->message, true); ?>
  <div class="well well-sm">
    <span class="label label-primary">{{ $activity->created_at }}</span>
    @lang($lang['name'])
  </div>
  
@endforeach

<div class="text-center">
  <?php echo $activities->links(); ?>  
</div>
@stop