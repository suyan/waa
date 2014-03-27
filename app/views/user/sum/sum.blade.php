@extends('layouts.default')

@section('body')
<div class="container">
  <div class="row">
    <div class="col-md-3">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="text-center"><i class="fa fa-files-o"></i> Sites</h2>
        </div>
        <div class="panel-body">
          <h2 class="text-center">{{ $site }}</h2>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="text-center"><i class="fa fa-exchange"></i> Logs</h2>
        </div>
        <div class="panel-body">
          <h2 class="text-center">{{ $log }}</h2>
        </div>
      </div>
    </div> 
    <div class="col-md-3">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="text-center"><i class="fa fa-exclamation-circle"></i> Attacks</h2>
        </div>
        <div class="panel-body">
          <h2 class="text-center">{{ $attack }}</h2>
        </div>
      </div>
    </div> 
    <div class="col-md-3">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="text-center"><i class="fa fa-bug"></i> Impacts</h2>
        </div>
        <div class="panel-body">
          <h2 class="text-center">{{ $impact }}</h2>
        </div>
      </div>
    </div>
  </div>    
</div>
@stop