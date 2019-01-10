@extends('layouts.app')
@section('title', '购物车')

@section('content')
<div class="row">
<div class="col-lg-10 offset-lg-1">
<div class="card">
  <div class="card-header">我的购物车</div>
  <div class="card-body">
    <table class="table table-striped">
      <thead>
      <tr>
        <th><input type="checkbox" id="select-all" checked></th>
        <th>商品信息</th>
        <th>单价</th>
        <th>数量</th>
        <th>操作</th>
      </tr>
      </thead>
      <tbody class="product_list">
      @foreach($cartItems as $item)
        <tr data-id="{{ $item->productSku->id }}">
          <td>
            <input type="checkbox" name="select" value="{{ $item->productSku->id }}" {{ $item->productSku->product->on_sale ? 'checked' : 'disabled' }}>
          </td>
          <td class="product_info">
            <div class="preview">
              <a target="_blank" href="{{ route('products.show', $item->productSku->product_id) }}">
                <img src="{{ $item->productSku->product->image_url }}">
              </a>
            </div>
            <div @if(! $item->productSku->product->on_sale) class="not_on_sale" @endif>
              <span class="product_title">
                <a target="_blank" href="{{ route('products.show', $item->productSku->product_id) }}">{{ $item->productSku->product->title }}</a>
              </span>
              <span class="sku_title">{{ $item->productSku->title }}</span>
              @if (! $item->productSku->product->on_sale)
                <span class="warning">该商品已下架</span>
              @endif
            </div>
          </td>
          <td><span class="price">￥{{ $item->productSku->price }}</span></td>
          <td>
            <input type="text" class="form-control form-control-sm amount" @if(! $item->productSku->product->on_sale) disabled @endif name="amount" value="{{ $item->amount }}">
          </td>
          <td>
            <button class="btn btn-sm btn-danger btn-remove">移除</button>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
    {{-- 地址与备注 --}}
    <div>
      <form class="form-horizontal" role="form" id="order-form">
        <div class="form-group row">
          <label class="col-form-label col-sm-3 text-md-right">选择收货地址</label>
          <div class="col-sm-9 col-md-7">
            <select class="form-control" name="address">
              @foreach($addresses as $address)
                <option value="{{ $address->id }}">{{ $address->full_address }} {{ $address->contact_name }} {{ $address->contact_phone }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-form-label col-sm-3 text-md-right">备注</label>
          <div class="col-sm-9 col-md-7">
            <textarea name="remark" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="form-group">
          <div class="offset-sm-3 col-sm-3">
            <button type="button" class="btn btn-primary btn-create-order">提交订单</button>
          </div>
        </div>
      </form>
    </div>
    {{-- 地址与备注结束 --}}
  </div>
</div>
</div>
</div>
@stop
@section('scripts')
<script>
  $(document).ready(function () {
    /* 移除 按钮事件*/
    $('.btn-remove').click(function () {
      // $(this) 可以获取到当前点击的 移除 按钮的 jQuery 对象
      // closest() 方法可以获取到匹配选择器的第一个祖先元素，在这里就是当前点击的 移除 按钮之上的 <tr> 标签
      var id = $(this).closest('tr').data('id');
      swal({
        title: '确认要移除该商品？',
        icon: 'warning',
        buttons: ['取消', '确定'],
        dangerMode: true,
      }).then(function (willDelete) {
        if (! willDelete) {
          return;
        }
        axios.delete('/cart/' + id).then(function () {
          location.reload();
        })
      });
    });
    /* 全选/取消全选 单选框的变更事件 */
    $('#select-all').change(function () {
      // 获取单选框的选中状态
      // prop() 方法可以知道标签中是否包含某个属性，当单选框被勾选时，对应的标签会新增一个 checked 属性
      var checked = $(this).prop('checked');
      // 获取所有 name=select 并且 不带有 disabled 的勾选框
      $('input[name=select][type=checkbox]:not([disabled])').each(function () {
        $(this).prop('checked', checked);
      });
    });
    /* 提交订单 按钮事件 */
    $('.btn-create-order').click(function () {
      // 构建请求参数
      // 先是地址 id 和 备注
      var req = {
        address_id: $('#order-form').find('select[name=address]').val(),
        remark: $('#order-form').find('textarea[name=remark]').val(),
        items: [],
      };
      // 遍历所有带 data-id 属性的 tr 标签，也就是购物车中的每个商品
      $('table tr[data-id]').each(function () {
        // 获取当前行的单选框
        var $checkbox = $(this).find('input[name=select][type=checkbox]');
        // 如果单选框没有选中或被禁用则跳过
        if ($checkbox.prop('disabled') || !$checkbox.prop('checked')) {
          return;
        }
        // 获取当前行的数量输入框
        var $input = $(this).find('input[name=amount]');
        // 如果数量为 0 或者不是数字，则跳过
        if ($input.val() === 0 || isNaN($input.val())) {
          return;
        }
        // 把 sku 的 id 和数量存入请求数组中
        req.items.push({
          sku_id: $(this).data('id'),
          amount: $input.val(),
        })
      });
      // 发送 ajax 请求
      axios.post('{{ route('orders.store') }}', req)
        .then(function (response) {
          swal('订单提交成功', '', 'success');
        }, function (error) {
          if (error.response.status === 422) {
            var html = '<div>';
            _.each(error.response.data.errors, function (errors) {
              _.each(errors, function (error) {
                html += error+'<br>';
              })
            });
            html += '</div>';
            swal({content: $(html)[0], icon: 'error'})
          } else {
            swal('系统错误', '', 'error');
          }
        });
    });
  });
</script>
@stop
