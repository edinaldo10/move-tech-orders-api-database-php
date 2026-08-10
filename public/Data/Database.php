<?php
namespace Data;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    public static function bootstrap(): void
    {
        $databaseUrl = $_ENV['DATABASE_URL'] ?? null;
        
        $host = 'localhost';
        $port = '5432';
        $database = 'orders';
        $username = 'postgres';
        $password = 'postgres';

        if (!empty($databaseUrl)) {
            $parsed = parse_url($databaseUrl);
            if ($parsed !== false && isset($parsed['host'])) {
                $host = $parsed['host'];
                $port = $parsed['port'] ?? 5432;
                $database = ltrim($parsed['path'] ?? '/orders', '/');
                $username = $parsed['user'] ?? 'postgres';
                $password = $parsed['pass'] ?? 'postgres';
            }
        }

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver'    => 'pgsql',
            'host'      => $host,
            'port'      => $port,
            'database'  => $database,
            'username'  => $username,
            'password'  => $password,
            'charset'   => 'utf8',
            'prefix'    => '',
            'schema'    => 'public',
            'sslmode'   => 'prefer',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}