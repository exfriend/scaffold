<?php

namespace Exfriend\Scaffold;

class Scaffold
{
    private $command;

    protected $recipes = [
        AddHelpersFile::class,
        AddMustHavePackages::class,
        ScaffoldFrontend::class,
    ];

    public function __construct( $command )
    {
        $this->command = $command;
    }

    public function handle()
    {
        foreach ( $this->recipes as $recipe )
        {
            ( new $recipe( $this ) )->handle();
        }

    }

}