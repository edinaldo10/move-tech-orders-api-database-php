<?php
namespace Data;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    public static function bootstrap(): void
    {
        // Tenta pegar via $_ENV e faz fallback para getenv() do sistema
        $databaseUrl = $_ENV['DATABASE_URL'] ?? getenv('DATABASE_URL') ?: null;
        
        $host = 'localhost';
        $port = '5432';
        $database = 'orders';
        $username = 'postgres';
        $password = 'postgres';

        if (!empty($databaseUrl)) {
            // Se a URL não tiver o scheme (ex: user:pass@host:port/db), o parse_url falha. 
            // Adicionamos um prefixo temporário se necessário, mas o padrão pgsql:// resolve.
            $parsed = parse_url($databaseUrl);
            
            if ($parsed !== false) {
                $host = $parsed['host'] ?? $host;
                $port = $parsed['port'] ?? $port;
                $database = isset($parsed['path']) ? ltrim($parsed['path'], '/') : $database;
                $username = $parsed['user'] ?? $username;
                $password = $parsed['pass'] ?? $password;
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