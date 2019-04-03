<?php

namespace Exfriend\Scaffold;

use Symfony\Component\Process\Process;

class ScaffoldBlade
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

    public function handle()
    {
        if ( !$this->command->confirm( 'Scaffold Blade?', true ) )
        {
            return false;
        }

        $this->exec( 'php artisan make:auth --force' );

        @unlink( 'resources/views/welcome.blade.php' );
        @mkdir( 'resources/views/layouts' );
        @mkdir( 'resources/views/pages' );
        @mkdir( 'resources/views/partials' );

        \File::copyDirectory( __DIR__ . '/../stubs/blade/layouts', 'resources/views/layouts' );
        \File::copyDirectory( __DIR__ . '/../stubs/blade/partials', 'resources/views/partials' );
        \File::copyDirectory( __DIR__ . '/../stubs/blade/pages', 'resources/views/pages' );
        \File::copyDirectory( __DIR__ . '/../stubs/blade/auth', 'resources/views/auth' );

        $web = file_get_contents( 'routes/web.php' );

        if ( preg_match( '~view\(\s*?[\'\"]*?welcome[\'\"]*?\s*?\)~is', $web, $mtchs ) )
        {
            $web = str_replace( $mtchs[ 0 ], "view('pages.landing')", $web );
            file_put_contents( 'routes/web.php', $web );
        }

    }

}