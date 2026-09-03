<?php

namespace Tests\Unit;

require_once __DIR__.'/../../scripts/prepare-sqlite-deployment.php';

use PHPUnit\Framework\TestCase;

use function Sohni\Scripts\prepareSqliteDeployment;
use function Sohni\Scripts\sqliteDatabasePath;

class SqliteDeploymentScriptTest extends TestCase
{
    public function test_sqlite_database_path_uses_storage_directory_by_default(): void
    {
        $basePath = '/var/www/workspace/frontend';

        $this->assertSame(
            '/var/www/workspace/frontend/storage/database/database.sqlite',
            sqliteDatabasePath($basePath)
        );
    }

    public function test_prepare_sqlite_deployment_creates_database_and_updates_env(): void
    {
        $basePath = sys_get_temp_dir().'/sohni-sqlite-'.bin2hex(random_bytes(5));

        mkdir($basePath, 0775, true);
        file_put_contents($basePath.'/.env.example', "APP_NAME=Laravel\nDB_CONNECTION=mysql\n");

        $databasePath = prepareSqliteDeployment($basePath);

        $this->assertFileExists($databasePath);
        $this->assertFileExists($basePath.'/.env');
        $this->assertStringContainsString('DB_CONNECTION=sqlite', (string) file_get_contents($basePath.'/.env'));
        $this->assertStringContainsString('DB_DATABASE='.$databasePath, (string) file_get_contents($basePath.'/.env'));
        $this->assertDirectoryExists($basePath.'/storage/database');
        $this->assertDirectoryExists($basePath.'/bootstrap/cache');
    }
}
