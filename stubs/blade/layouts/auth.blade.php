@extends('layouts.app')

@section('body')
    <body class="text-sans bg-gray-900 sm:bg-gray-800 h-screen">
    <div id="app">
        <div class="center h-screen">
            @yield('content')
        </div>
    </div>
    <script src="{{ mix('js/app.js') }}"></script>
    @yield('scripts')
    @include('partials.common_scripts')
    </body>
@overwrite

@section('content')
    I override content
@endsection