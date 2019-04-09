<?php

Route::get( 'test', function ( Request $request )
{
    return 'I am only visible in non-production environment';
} );
