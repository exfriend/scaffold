<?php

namespace Exfriend\Scaffold;

class MoveUserModel
{
    private $command;

    public function __construct( $command )
    {
        $this->command = $command;
    }

    public function handle()
    {
        if ( !$this->command->confirm( 'Move User to app/Models?', true ) || !file_exists( getcwd() . '/app/User.php' ) || file_exists( 'app/Models/User.php' ) )
        {
            return;
        }

        $this->command->task( 'Moving model... ', function ()
        {
            $user = file_get_contents( 'app/User.php' );
            $user = str_replace( 'namespace App;', 'namespace App\Models;', $user );
            $user = preg_replace( '~fillable.*?\[.*?];~ims', 'guarded = [];', $user );
            @mkdir( 'app/Models' );
            file_put_contents( 'app/Models/User.php', $user );
            unlink( 'app/User.php' );
        } );

        $this->command->task( 'Updating auth config... ', function ()
        {
            $user = file_get_contents( 'config/auth.php' );
            $user = str_replace( 'App\User', 'App\Models\User', $user );
            file_put_contents( 'config/auth.php', $user );
        } );

        $this->command->task( 'Updating auth controllers... ', function ()
        {
            $user = file_get_contents( 'app/Http/Controllers/Auth/RegisterController.php' );
            $user = str_replace( 'App\User', 'App\Models\User', $user );
            file_put_contents( 'app/Http/Controllers/Auth/RegisterController.php', $user );
        } );

    }

}