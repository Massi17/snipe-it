@extends('layouts/default')

@section('title')
Test
@parent
@stop

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Test</h3>
            </div>
            <div class="box-body">
                <p>This is a custom test page.</p>
            </div>
        </div>
    </div>
</div>
@endsection
