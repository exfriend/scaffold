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
        'php artisan preset tailwindcss-auth',
        'npm install',
        'rm tailwind.js',
        'node_modules/.bin/tailwind init',
        'npm run dev && npm run dev',
        'npm install --save animate.css moment moment-timezone vee-validate',
    ];

    public function handle()
    {
        if ( $this->command->confirm( 'Scaffold frontend?', true ) )
        {
            return $this->execAll( $this->tasks, 'Scaffolding frontend: ' );
        }

    }

}