@extends('layouts.side39')

@section('css')
{{ HTML::style('assets/css/map.css') }}
@stop

@section('body')
<div class="row">
  <h3>@lang('host.detail')</h3>
  <ul class="nav nav-tabs">
    <li class="active"><a href="{{ URL::to('host/'.$host->id.'/info') }}">基本信息</a></li>
    <li><a href="{{ URL::to('host/'.$host->id.'/vector') }}">攻击向量</a></li>
  </ul>
  <br>
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">@lang('host.host_info')</h3>
      </div>
      <table class="table table-bordered table-hover">
        <tr>
          <td style="width:50%;">@lang('host.hostname')</td>
          <td>{{ $host->hostname }}</td>
        </tr>
        <tr>
          <td>@lang('host.domain')</td>
          <td>{{ $host->domain }}</td>
        </tr>
        <tr>
          <td>@lang('host.description')</td>
          <td>{{ $host->description }}</td>
        </tr>
        <tr>
          <td>@lang('host.create_time')</td>
          <td>{{ $host->created_at }}</td>
        </tr>
        <tr>
          <td>@lang('host.process')</td>
          <td>{{ $host->process }}%</td>
        </tr>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">@lang('host.attack_info')</h3>
      </div>
      <table class="table table-bordered table-hover">
        <tr>
          <td style="width:50%;">@lang('host.line_count')</td>
          <td>{{ $host->line_count }}</td>
        </tr>
        <tr>
          <td>@lang('host.attack_count')</td>
          <td>{{ $host->attack_count }}</td>
        </tr>
        <tr>
          <td>@lang('host.impact_count')</td>
          <td>{{ $host->impact_count }}</td>
        </tr>
        <tr>
          <td>@lang('host.start_time')</td>
          <td>{{ $host->start_time }}</td>
        </tr>
        <tr>
          <td>@lang('host.end_time')</td>
          <td>{{ $host->end_time }}</td>
        </tr>
      </table>
    </div>
  </div>
  <!-- clients攻击图 -->
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">@lang('host.info')</h3>
      </div>
      <div class="panel-body">
        <div id="chart"  style="width: 100%; height: 400px;"></div>
      </div>
    </div>    
  </div>
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">@lang('host.info')</h3>
      </div>
      <div class="panel-body">
        <div id="world-map-gdp"  style="width: 100%; height: 400px"></div>
      </div>
    </div>    
  </div>
</div>
@stop

@section('js')
{{ HTML::script('assets/js/map.js') }}
<script type="text/javascript">
$(function(){
  // 每个国家的数据
  var countryImpactCount = {{ $countryImpactCount }};
  var countryAttackCount = {{ $countryAttackCount }};
  var cityAttackData = {{ $cityAttackData }};
  var cityAttackLocation = {{ $cityAttackLocation }};
  $('#world-map-gdp').vectorMap({
    map: 'world_mill_en',
    series: {
      regions: [{
        values: countryImpactCount,
        scale: ['#C8EEFF', '#0071A4'],
        normalizeFunction: 'polynomial'
      }],
      markers: [{
        attribute: 'fill',
        scale: ['#FEE5D9', '#A50F15'],
        values: cityAttackData
      },{
        attribute: 'r',
        scale: [5, 15],
        values: cityAttackData
      }],
    },
    markerStyle: {initial: {fill: '#F8E23B',stroke: '#383f47'}},
    backgroundColor: '#383f47',
    markers: cityAttackLocation,
    onMarkerLabelShow: function(event, label, index){
      label.html(
        ''+cityAttackLocation[index].name+'<br>'+
        '攻击影响力: '+cityAttackData[index]+'<br>'+
      );
    },
    onRegionLabelShow: function(event, label, code){
      label.html(
        ''+label.html()+'<br>'+
        '攻击影响力: '+countryImpactCount[code]+'<br>'+
        '攻击总数: ' +countryAttackCount[code]
      );
    }
  });
});
</script>
{{ HTML::script('assets/js/chart.js') }}
<script>
$(function () {
  $('#chart').highcharts({
    chart: {
      plotBackgroundColor: null,
      plotBorderWidth: null,
      plotShadow: false
    },
    title: {
      text: '攻击影响力比重图'
    },
    tooltip: {
      pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    plotOptions: {
      pie: {
        allowPointSelect: true,
        cursor: 'pointer',
        dataLabels: {
          enabled: true,
          color: '#000000',
          connectorColor: '#000000',
          format: '<b>{point.name}</b>: {point.percentage:.1f} %'
        },
        showInLegend: true
      }
    },
    series: [{
      type: 'pie',
      name: '攻击影响比重',
      data: {{ $clientImpactRate }}
    }]
  });
});
</script>
@stop