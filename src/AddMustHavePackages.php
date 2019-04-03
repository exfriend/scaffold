<?php

namespace Exfriend\Scaffold;

use Symfony\Component\Process\Process;


class AddMustHavePackages
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
        'composer require --dev barryvdh/laravel-ide-helper doctrine/dbal',
        'composer require barryvdh/laravel-cors laracasts/flash laravel-frontend-presets/tailwindcss pyaesone17/active-state',
    ];

    public function handle()
    {
        if ( $this->command->confirm( 'Install must have composer packages?', true ) )
        {
            $this->execAll( $this->tasks, 'Configuring composer: ' );
        }
    }

}