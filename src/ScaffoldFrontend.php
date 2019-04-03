<?php

namespace Exfriend\Scaffold;

use Symfony\Component\Process\Process;

class ScaffoldFrontend
{
    private $command;

    public function __construct( $command )
    {
        $this->command = $command;
    }

    public function execAll( array $tasks, $comment = null )
    {
        foreach ( $tasks as $task )
        {
            $this->exec( $task, $comment );
        }
    }

    public function exec( $task, $comment = null )
    {
        $this->command->task( $comment . $task, function () use ( $task )
        {
            ( new Process( $task ) )->run();
        } );
    }


    protected $tasks = [
        //        'php artisan preset none',
        //        'npm install',
        //        'rm tailwind.js',
        //        'node_modules/.bin/tailwind init',
        //        'npm run dev && npm run dev',
    ];

    public function handle()
    {
        if ( !$this->command->confirm( 'Scaffold frontend?', true ) )
        {
            return false;
        }

        \File::deleteDirectory( 'resources/sass' );
        \File::deleteDirectory( 'resources/js' );
        \File::copyDirectory( __DIR__ . '/../stubs/frontend/resources/js', 'resources/js' );
        \File::copyDirectory( __DIR__ . '/../stubs/frontend/resources/css', 'resources/css' );
        \File::copy( __DIR__ . '/../stubs/frontend/webpack.mix.js', 'webpack.mix.js' );

        $packageJson = json_decode( file_get_contents( 'package.json' ), true );
        unset( $packageJson[ 'devDependencies' ][ 'popper.js' ] );
        unset( $packageJson[ 'devDependencies' ][ 'lodash' ] );
        unset( $packageJson[ 'devDependencies' ][ 'bootstrap' ] );
        file_put_contents( 'package.json', json_encode( $packageJson, JSON_PRETTY_PRINT ) );

        $this->exec(
            'npm install --save-dev tailwindcss@next laravel-mix-tailwind laravel-mix-purgecss tailwindcss-tables animate.css moment moment-timezone vee-validate',
            'Updating package.json: ' );

        $this->execAll(
            [
                'npm install',
                'node_modules/.bin/tailwind init',
                'npm run dev && npm run dev',
            ], 'Initializing frontend: ' );


    }

}