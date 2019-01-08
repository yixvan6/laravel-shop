@extends('layouts.app')
@section('title', ($address->id ? '修改' : '新增') .'收货地址')

@section('content')
  <div class="row">
    <div class="col-md-10 offset-lg-1">
      <div class="card">
        <div class="card-header">
          <h3 class="text-center">
            {{ $address->id ? '修改' : '新增' }}收货地址
          </h3>
        </div>
        <div class="card-body">
          @include('common.error')
          <addresses-create-and-edit inline-template>
            @if ($address->id)
              <form class="form-horizontal" role="form" action="{{ route('user.addresses.update', $address->id) }}" method="POST">
              @method('put')
            @else
              <form class="form-horizontal" role="form" action="{{ route('user.addresses.store') }}" method="POST">
            @endif
              @csrf
              <!-- inline-template 代表通过内联方式引入组件 -->
              <select-district :init-value="{{ json_encode([$address->province, $address->city, $address->district]) }}" @change="onDistrictChanged" inline-template>
                <div class="form-group row">
                  <label class="col-form-label col-sm-2 text-md-right">省市区</label>
                  <div class="col-sm-3">
                    <select class="form-control" v-model="provinceId">
                      <option value="">选择省</option>
                      <option v-for="(name, id) in provinces" :value="id">@{{ name }}</option>
                    </select>
                  </div>
                  <div class="col-sm-3">
                    <select class="form-control" v-model="cityId">
                      <option value="">选择市</option>
                      <option v-for="(name, id) in cities" :value="id">@{{ name }}</option>
                    </select>
                  </div>
                  <div class="col-sm-3">
                    <select class="form-control" v-model="districtId">
                      <option value="">选择区</option>
                      <option v-for="(name, id) in districts" :value="id">@{{ name }}</option>
                    </select>
                  </div>
                </div>
              </select-district>
              {{-- 插入3个隐藏字段 --}}
              {{-- 通过 v-model 与 addresses-create-and-edit 组件里的值关联起来 --}}
              <input type="hidden" name="province" v-model="province">
              <input type="hidden" name="city" v-model="city">
              <input type="hidden" name="district" v-model="district">
              <div class="form-group row">
                <label class="col-form-label text-md-right col-sm-2">详细地址</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="address" value="{{ old('address', $address->address) }}">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label text-md-right col-sm-2">邮编</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="zip" value="{{ old('zip', $address->zip) }}">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label text-md-right col-sm-2">联系人</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="contact_name" value="{{ old('contact_name', $address->contact_name) }}">
                </div>
              </div>
              <div class="form-group row">
                <label class="col-form-label text-md-right col-sm-2">联系电话</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone', $address->contact_phone) }}">
                </div>
              </div>
              <div class="form-group row text-center">
                <div class="col-12">
                  <button class="btn btn-primary" type="submit">提交</button>
                </div>
              </div>
            </form>
          </addresses-create-and-edit>
        </div>
      </div>
    </div>
  </div>
@stop
