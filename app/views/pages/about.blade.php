@extends('layouts.default')

@section('css')
<style>
  body{ padding-top: 50px; }
</style>
@stop

@section('body')
<div class="jumbotron text-center">
  <div class="container">
    <h1>Web Application Analyzor</h1>
    <p>一个容易使用的Web应用检测工具，可以精确分析来自世界各地的攻击请求，帮助网站检测安全漏洞，评估安全风险。</p>
    <p><a href="{{ URL::to('user/login') }}" class="btn btn-primary btn-lg" role="button">@lang('home.sign_in') <i class="fa fa-arrow-circle-o-right"></i></a></p>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-4">
      <h2>容易使用</h2>
      <p>用户只需上传服务器日志文件，然后点击开始分析按钮，分析程序将在后台自动运行。分析完成后会生成非常直观的统计数据及统计图表，尽可能简单的让用户了解自己应用的安全情况。</p>
      <!-- <p><a class="btn btn-default" role="button">View details &raquo;</a></p> -->
    </div>
    <div class="col-md-4">
      <h2>安全检测</h2>
      <p>本分析程序不仅利用了传统IDS对于请求内容的分析方法，而且融合了地理信息分析、机器学习以及攻击重现等高级方案，尽可能提高攻击报告的准确性，降低误报几率。</p>
      <!-- <p><a class="btn btn-default" role="button">View details &raquo;</a></p> -->
   </div>
    <div class="col-md-4">
      <h2>风险评估</h2>
      <p>在分析完成后，根据请求中攻击的成功与否，以及攻击手段和攻击来源的危险程度，我们对应用整体安全风险程度进行了评估，对被攻击位置进行提示，帮助应用提升自身安全性。</p>
      <!-- <p><a class="btn btn-default" role="button">View details &raquo;</a></p> -->
    </div>
  </div>
</div>
@stop