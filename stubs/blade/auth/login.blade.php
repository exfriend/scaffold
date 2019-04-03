@extends('layouts.auth')
@section('title','Sign In')

@section('content')
    <div class="flex flex-col break-words bg-gray-800 sm:rounded-lg sm:shadow-lg sm:p-10">

        <div class="center">
            @include('partials.logo')
        </div>

        <form class="w-full p-6" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="flex flex-col flex-wrap mb-6">
                <input id="email" placeholder="Email" type="email" class="input input-minimal bg-gray-700 text-gray-200 border-gray-700 w-full {{ $errors->has('email') ? ' border-red-500' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                @if ($errors->has('email'))
                    <p class="text-red-500 text-sm mt-4">
                        {{ $errors->first('email') }}
                    </p>
                @endif
            </div>

            <div class="flex flex-col flex-wrap mb-6">
                <input id="password" placeholder="Password" type="password" class="input bg-gray-700 text-gray-200 input-minimal border-gray-700 w-full {{ $errors->has('password') ? ' border-red-500' : '' }}" name="password" required>
                @if ($errors->has('password'))
                    <p class="text-red-500 text-sm mt-4">
                        {{ $errors->first('password') }}
                    </p>
                @endif
            </div>

            <input type="hidden" name="remember" id="remember" value="On">

            <div class="flex flex-wrap w-full items-center pt-8">
                <button type="submit" class="btn text-gray-400 font-normal bg-gray-700 hover:bg-gray-700">
                    Sign In
                </button>
                <a class="btn text-gray-400 font-normal bg-transparent" href="{{ route('register') }}">
                    Sign Up
                </a>
            </div>
            <div class="w-full text-center pt-8 -mb-8">
                <a class="text-gray-600" href="{{ route('password.request') }}">
                    Forgot Password
                </a>
            </div>
        </form>

    </div>
@endsection
