@if (count($errors) > 0)
  <div class="alert alert-danger">
    <b>有错误发生：</b>
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif
