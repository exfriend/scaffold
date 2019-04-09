<?php

namespace Exfriend\Scaffold;

use Symfony\Component\Process\Process;

class AddLocalRoutes
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
        if ( !$this->command->confirm( 'Add local routes?', true ) )
        {
            return false;
        }

        \File::copy( __DIR__ . '/../stubs/routes/local.php', 'routes/local.php' );

        $r = file_get_contents( 'app/Providers/RouteServiceProvider.php' );

        if ( strpos( $r, 'local.php' ) )
        {
            return;
        }

        $r = str_replace( '$this->mapWebRoutes();', '$this->mapWebRoutes();' . PHP_EOL . PHP_EOL . ' if ( app()->environment() == \'local\' ) { Route::middleware( \'web\' )->namespace( $this->namespace )->group( base_path( \'routes/local.php\' ) ); } ', $r );

        file_put_contents( 'app/Providers/RouteServiceProvider.php', $r );

        //
        //        if ( preg_match( '~view\(\s*?[\'\"]*?welcome[\'\"]*?\s*?\)~is', $web, $mtchs ) )
        //        {
        //            $web = str_replace( $mtchs[ 0 ], "view('pages.landing')", $web );
        //            file_put_contents( 'routes/web.php', $web );
        //        }

    }

}