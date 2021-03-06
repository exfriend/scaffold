@extends('layouts.auth')
@section('title','Reset Password')

@section('content')

    {{--    @dd($errors->all())--}}

    <div class="flex flex-col break-words bg-gray-900 sm:rounded-lg sm:shadow-lg sm:p-10">

        <div class="center">
            @include('partials.logo')
        </div>

        <form class="w-full p-6" method="POST" action="{{ route('password.update') }}">
            <input type="hidden" name="token" value="{{ $token }}">
            @csrf
            <div class="flex flex-col flex-wrap mb-6">
                <input autocomplete="username" id="email" placeholder="Email" type="email" class="input input-minimal bg-gray-800 rounded text-gray-200 border-gray-700 w-full {{ $errors->has('email') ? ' border-red-500' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                @if ($errors->has('email'))
                    <p class="text-red-500 text-sm mt-4">
                        {{ $errors->first('email') }}
                    </p>
                @endif
            </div>

            <div class="flex flex-col flex-wrap mb-6">

                <input autocomplete="new-password" id="password" placeholder="Password" type="password" class="input input-minimal border-gray-700 w-full {{ $errors->has('password') ? ' border-red-500' : '' }}" name="password" required>

                @if ($errors->has('password'))
                    <p class="text-red-500 text-sm mt-4">
                        {{ $errors->first('password') }}
                    </p>
                @endif
            </div>


            <div class="flex flex-col flex-wrap mb-6">

                <input autocomplete="new-password" id="password_confirmation" placeholder="Confirm Password" type="password" class="input input-minimal border-gray-700 w-full {{ $errors->has('password_confirmation') ? ' border-red-500' : '' }}" name="password_confirmation" required>

                @if ($errors->has('password_confirmation'))
                    <p class="text-red-500 text-sm mt-4">
                        {{ $errors->first('password_confirmation') }}
                    </p>
                @endif
            </div>

            <div class="flex flex-wrap w-full items-center pt-8">
                <button type="submit" class="btn w-full text-gray-400 font-normal bg-gray-700 hover:bg-gray-700">
                    Reset Password
                </button>
            </div>
        </form>

    </div>

@endsection
