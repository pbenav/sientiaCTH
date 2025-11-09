@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="card">
        <div class="card-body">
            <h3 class="card-title">@lang('Error')</h3>
            <p>{{ $message ?? 'Ha ocurrido un error en el servidor.' }}</p>

            @if(!empty($details) && config('app.debug'))
                <hr>
                <h5>Detalles técnicos (APP_DEBUG = true)</h5>
                <pre style="white-space: pre-wrap;">{{ print_r($details, true) }}</pre>
            @else
                <p>Si el problema persiste, contacte con el administrador del sistema.</p>
            @endif

            <a href="{{ route('mobile.auth') }}" class="btn btn-primary">Volver a autenticación</a>
        </div>
    </div>
</div>
@endsection
