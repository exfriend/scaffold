<?php

namespace Exfriend\Scaffold;

class AddHelpersFile
{
    private $command;

    public function __construct( $command )
    {
        $this->command = $command;
    }

    public function handle()
    {
        if ( !$this->command->confirm( 'Add app/helpers.php?', true ) || !file_exists( getcwd() . '/app' ) || file_exists( 'app/helpers.php' ) )
        {
            return;
        }

        $this->command->task( 'Adding helpers file', function ()
        {
            file_put_contents( getcwd() . '/app/helpers.php', file_get_contents( __DIR__ . '/../../stubs/helpers.php' ) );
        } );

        $this->command->task( 'Configuring composer autoload', function ()
        {
            $c = json_decode( file_get_contents( getcwd() . '/composer.json' ), true );
            if ( !isset( $c[ 'autoload' ][ 'files' ] ) )
            {
                $c[ 'autoload' ][ 'files' ] = [];
            }

            if ( !in_array( 'app/helpers.php', $c[ 'autoload' ][ 'files' ] ) )
            {
                $c[ 'autoload' ][ 'files' ]  [] = 'app/helpers.php';
                file_put_contents( getcwd() . '/composer.json', json_encode( $c, JSON_PRETTY_PRINT ) );
            }
        } );
    }

}