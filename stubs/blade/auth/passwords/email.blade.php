@extends('layouts.auth')
@section('title','Forgot Password')

@section('content')

    <div class="flex flex-col break-words bg-gray-800 sm:rounded-lg sm:shadow-lg sm:p-10">
        <div class="center">
            @include('partials.logo')
        </div>

        <form class="w-full p-6" method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="flex flex-col flex-wrap mb-6">
                <input id="email" placeholder="Email" type="email" class="input input-minimal bg-gray-700 text-gray-200 border-gray-700 w-full {{ $errors->has('email') ? ' border-red-500' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                @if ($errors->has('email'))
                    <p class="text-red-500 text-sm mt-4">
                        {{ $errors->first('email') }}
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap w-full items-center pt-8">
                <button type="submit" class="btn w-full text-gray-400 font-normal bg-gray-700 hover:bg-gray-700">
                    Send Password Reset Link
                </button>
            </div>
            <div class="w-full text-center pt-8 -mb-8">
                <a class="text-gray-600" href="{{ route('login') }}">
                    Back to Sign In
                </a>
            </div>
        </form>

    </div>

@endsection