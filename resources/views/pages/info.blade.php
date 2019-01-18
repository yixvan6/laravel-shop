@extends('layouts.app')
@section('title', '提示')

@section('content')
<div class="card">
    <div class="card-header">{{ isset($error) ? '错误' : '提示信息' }}</div>
    <div class="card-body text-center">
        <h3 class="mb-3 text-{{ isset($error) ? 'danger' : 'primary' }}">{{ $error ?? $info }}</h3>
        <a class="btn btn-primary" href="{{ route('root') }}">返回首页</a>
    </div>
</div>
@stop
