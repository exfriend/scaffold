<?php

namespace Exfriend\Scaffold;

use Symfony\Component\Process\Process;

class ScaffoldDocker
{
    private $command;

    protected $project = 'website';

    protected $env;
    protected $envInsert;
    protected $dockerComposeVolumes = 'volumes:' . PHP_EOL;
    protected $dockerComposeServices = 'services:' . PHP_EOL;

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
        if ( !$this->command->confirm( 'Scaffold Docker?', true ) )
        {
            return;
        }

        \File::deleteDirectory( '.docker' );

        $dockerDirExists = file_exists( '.docker' );
        if ( $dockerDirExists )
        {
            $this->command->error( '.docker directory already exists in ' . getcwd() );
            return 0;
        }

        $this->env = trim( file_get_contents( '.env' ) );
        if ( strpos( $this->env, '# AFTERGLOW_START' ) !== false )
        {
            $this->env = trim( substr( $this->env, strpos( $this->env, '# AFTERGLOW_END' ) + 15 ) );
        }

        $this->project = $this->command->ask( 'Subsystem name?', 'website' );
        $this->command->line( 'Initializing docker' );
        $this->initDocker();

        $this->addWorkspace();
        $this->addPhp();

        if ( $this->command->confirm( 'Add Caddy?', true ) )
        {
            $this->addCaddy();
        }
        if ( $this->command->confirm( 'Add MySQL?', true ) )
        {
            $this->addMysql();
        }
        if ( $this->command->confirm( 'Add PhpMyAdmin?', true ) )
        {
            $this->addMyAdmin();
        }
        if ( $this->command->confirm( 'Add worker?', true ) )
        {
            $this->addWorker();
        }

        $this->finish();

    }

    public function finish()
    {
        info( 'Finishing' );

        $compose = implode( [
            "version: '3'",
            "networks:",
            "  frontend:",
            "    driver: \${" . strtoupper( $this->project ) . "__NETWORKS_DRIVER}",
            "  backend:",
            "    driver: \${" . strtoupper( $this->project ) . "__NETWORKS_DRIVER}",
        ], PHP_EOL );

        file_put_contents( 'docker-compose.yml',
            $compose . PHP_EOL
            . $this->dockerComposeVolumes . PHP_EOL . $this->dockerComposeServices
        );

        file_put_contents( '.env', '# AFTERGLOW_START' . PHP_EOL . str_replace( 'CHANGEME', strtoupper( $this->project ), $this->envInsert ) . '# AFTERGLOW_END' . PHP_EOL . $this->env );

        file_put_contents( '.afterglow.json', json_encode( [
            'project' => $this->project,
        ] ) );

    }

    public function initDocker()
    {
        mkdir( '.docker' );

        $this->envInsert = implode( [
                'CHANGEME__TIMEZONE=Europe/Kiev',
                'CHANGEME__NETWORKS_DRIVER=bridge',
                'CHANGEME__VOLUMES_DRIVER=local',
                'CHANGEME__APP_CODE_PATH_HOST=../' . basename( getcwd() ) . '/',
                'CHANGEME__APP_CODE_PATH_CONTAINER=/var/www:cached',
                'CHANGEME__DATA_PATH_HOST=~/.laradock/' . strtolower( $this->project ) . '_data',
                'CHANGEME__DOCKER_HOST_IP=10.0.75.1',
                'CHANGEME__DOCKER_DIR=../' . basename( getcwd() ) . '/.docker',
            ], PHP_EOL ) . PHP_EOL;
    }

    public function addPhp()
    {
        $this->command->line( 'Adding PHP FPM' );
        mkdir( '.docker/php-fpm' );
        \File::copyDirectory( __DIR__ . '/../docker-stubs/php-fpm', '.docker/php-fpm' );

        $stub = <<<HERE
  website__php-fpm:
    build:
      context: \${WEBSITE__DOCKER_DIR}/php-fpm
    volumes:
    - \${WEBSITE__APP_CODE_PATH_HOST}:\${WEBSITE__APP_CODE_PATH_CONTAINER}
    - \${WEBSITE__DOCKER_DIR}/php-fpm/php.ini:/usr/local/etc/php/php.ini
    expose:
    - "9000"
    depends_on:
    - website__workspace
    extra_hosts:
    - "dockerhost:\${WEBSITE__DOCKER_HOST_IP}"
    networks:
    - backend
HERE;

        $stub = str_replace( 'WEBSITE', strtoupper( $this->project ), $stub );
        $stub = str_replace( 'website', strtolower( $this->project ), $stub );

        $this->dockerComposeServices .= $stub . PHP_EOL;
    }

    public function addWorkspace()
    {
        $this->command->line( 'Adding Workspace' );
        mkdir( '.docker/workspace' );
        \File::copyDirectory( __DIR__ . '/../docker-stubs/workspace', '.docker/workspace' );

        $stub = <<<HERE
  website__workspace:
    build:
      context: \${WEBSITE__DOCKER_DIR}/workspace
    volumes:
    - \${WEBSITE__APP_CODE_PATH_HOST}:\${WEBSITE__APP_CODE_PATH_CONTAINER}
    extra_hosts:
    - "dockerhost:\${WEBSITE__DOCKER_HOST_IP}"
    tty: true
    networks:
    - frontend
    - backend
HERE;

        $stub = str_replace( 'WEBSITE', strtoupper( $this->project ), $stub );
        $stub = str_replace( 'website', strtolower( $this->project ), $stub );

        $this->dockerComposeServices .= $stub . PHP_EOL;
    }


    public function addCaddy()
    {
        $this->command->line( 'Adding Caddy' );
        $this->envInsert .= implode( [
                'CHANGEME__CADDY_CONFIG_PATH=../' . basename( getcwd() ) . '/.docker/caddy/caddy',
                'CHANGEME__CADDY_HOST_LOG_PATH=../' . basename( getcwd() ) . '/storage/logs/caddy',
                'CHANGEME__CADDY_HOST_HTTP_PORT=80',
                'CHANGEME__CADDY_HOST_HTTPS_PORT=443',
            ], PHP_EOL ) . PHP_EOL;

        $this->dockerComposeVolumes .= '  ' . $this->project . '__caddy:' . PHP_EOL . '    driver: ${' . strtoupper( $this->project ) . '__VOLUMES_DRIVER}' . PHP_EOL;
        $this->dockerComposeServices .= '';

        \File::copyDirectory( __DIR__ . '/../docker-stubs/caddy', '.docker/caddy' );
        $caddyfile = file_get_contents( '.docker/caddy/caddy/Caddyfile' );
        $caddyfile = str_replace( 'CHANGEME', basename( $this->project ), $caddyfile );
        file_put_contents( '.docker/caddy/caddy/Caddyfile', $caddyfile );


        $stub = <<<HERE
  website__caddy:
    build:
      context: \${WEBSITE__DOCKER_DIR}/caddy
    volumes:
    - /mnt:/mnt
    - \${WEBSITE__APP_CODE_PATH_HOST}:\${WEBSITE__APP_CODE_PATH_CONTAINER}
    - \${WEBSITE__CADDY_CONFIG_PATH}:/etc/caddy
    - \${WEBSITE__CADDY_HOST_LOG_PATH}:/var/log/caddy
    - \${WEBSITE__DATA_PATH_HOST}:/root/.caddy
    ports:
    - "\${WEBSITE__CADDY_HOST_HTTP_PORT}:80"
    - "\${WEBSITE__CADDY_HOST_HTTPS_PORT}:443"
    depends_on:
    - website__php-fpm
    networks:
    - frontend
    - backend
HERE;

        $stub = str_replace( 'WEBSITE', strtoupper( $this->project ), $stub );
        $stub = str_replace( 'website', strtolower( $this->project ), $stub );

        $this->dockerComposeServices .= $stub . PHP_EOL;

    }

    public function addWorker()
    {
        $this->command->line( 'Adding worker' );

        \File::copyDirectory( __DIR__ . '/../docker-stubs/worker', '.docker/worker' );

        $stub = <<<HERE
  changeme__worker:
    build:
      context: \${CHANGEME__DOCKER_DIR}/worker
    volumes:
      - \${CHANGEME__APP_CODE_PATH_HOST}:\${CHANGEME__APP_CODE_PATH_CONTAINER}
    depends_on:
      - changeme__workspace
    extra_hosts:
      - "dockerhost:\${CHANGEME__DOCKER_HOST_IP}"
    networks:
      - backend
HERE;

        $stub = str_replace( 'CHANGEME', strtoupper( $this->project ), $stub );
        $stub = str_replace( 'changeme', strtolower( $this->project ), $stub );

        $this->dockerComposeServices .= $stub . PHP_EOL;

    }

    public function addMysql()
    {
        $this->command->line( 'Adding MySQL' );
        $this->envInsert .= implode( [
                'CHANGEME__MYSQL_VERSION=latest',
                'CHANGEME__MYSQL_DATABASE=homestead',
                'CHANGEME__MYSQL_USER=homestead',
                'CHANGEME__MYSQL_PASSWORD=secret',
                'CHANGEME__MYSQL_PORT=3307',
                'CHANGEME__MYSQL_ROOT_PASSWORD=root',
                'CHANGEME__MYSQL_ENTRYPOINT_INITDB=.docker/mysql/docker-entrypoint-initdb.d',
            ], PHP_EOL ) . PHP_EOL;

        $this->dockerComposeVolumes .= '  ' . $this->project . '__mysql:' . PHP_EOL . '    driver: ${' . strtoupper( $this->project ) . '__VOLUMES_DRIVER}' . PHP_EOL;
        $this->dockerComposeServices .= '';

        \File::copyDirectory( __DIR__ . '/../docker-stubs/mysql', '.docker/mysql' );

        $stub = <<<HERE
  changeme__mysql:
    build:
      context: \${CHANGEME__DOCKER_DIR}/mysql
      args:
      - MYSQL_VERSION=\${CHANGEME__MYSQL_VERSION}
    environment:
    - MYSQL_DATABASE=\${CHANGEME__MYSQL_DATABASE}
    - MYSQL_USER=\${CHANGEME__MYSQL_USER}
    - MYSQL_PASSWORD=\${CHANGEME__MYSQL_PASSWORD}
    - MYSQL_ROOT_PASSWORD=\${CHANGEME__MYSQL_ROOT_PASSWORD}
    - TZ=\${CHANGEME__TIMEZONE}
    volumes:
    - \${CHANGEME__DATA_PATH_HOST}/mysql:/var/lib/mysql
    - \${CHANGEME__MYSQL_ENTRYPOINT_INITDB}:/docker-entrypoint-initdb.d
    ports:
    - "\${CHANGEME__MYSQL_PORT}:3306"
    networks:
    - backend
    - frontend
HERE;

        $stub = str_replace( 'CHANGEME', strtoupper( $this->project ), $stub );
        $stub = str_replace( 'changeme', strtolower( $this->project ), $stub );

        $this->dockerComposeServices .= $stub . PHP_EOL;

        $this->env = str_replace( 'DB_HOST=127.0.0.1', 'DB_HOST=' . strtolower( $this->project ) . '__mysql', $this->env );

    }

    public function addMyAdmin()
    {
        $this->command->line( 'Adding phpMyAdmin' );
        $this->envInsert .= implode( [
                'CHANGEME__PMA_DB_ENGINE=mysql',
                'CHANGEME__PMA_USER=homestead',
                'CHANGEME__PMA_PASSWORD=secret',
                'CHANGEME__PMA_ROOT_PASSWORD=secret',
                'CHANGEME__PMA_PORT=8081',
                'CHANGEME__PMA_HOST=' . strtolower( $this->project ) . '__mysql',
            ], PHP_EOL ) . PHP_EOL;

        $this->dockerComposeVolumes .= '  ' . $this->project . '__phpmyadmin:' . PHP_EOL . '    driver: ${' . strtoupper( $this->project ) . '__VOLUMES_DRIVER}' . PHP_EOL;

        \File::copyDirectory( __DIR__ . '/../docker-stubs/phpmyadmin', '.docker/phpmyadmin' );

        $stub = <<<HERE
  database__phpmyadmin:
    build:
      context: \${DATABASE__DOCKER_DIR}/phpmyadmin
      args:
      - MSQT=\${DATABASE__PMA_HOST}
    environment:
    - PMA_ARBITRARY=1
    - MYSQL_USER=\${DATABASE__PMA_USER}
    - MYSQL_PASSWORD=\${DATABASE__PMA_PASSWORD}
    - MYSQL_ROOT_PASSWORD=\${DATABASE__PMA_ROOT_PASSWORD}
    ports:
    - "\${DATABASE__PMA_PORT}:80"
    depends_on:
    - "database__\${DATABASE__PMA_DB_ENGINE}"
    networks:
    - frontend
    - backend
HERE;

        $stub = str_replace( 'DATABASE', strtoupper( $this->project ), $stub );
        $stub = str_replace( 'database', strtolower( $this->project ), $stub );

        $this->dockerComposeServices .= $stub . PHP_EOL;

    }


}