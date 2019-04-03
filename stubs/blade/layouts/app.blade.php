@extends('layouts.html')

@section('styles')
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
@endsection

@section('body')
    <body class="text-sans">
    <div id="app">
        <div class="container mx-auto">
            @yield('content')
        </div>
    </div>
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('scripts')
    @include('partials.common_scripts')
    </body>
@endsection