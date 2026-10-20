<?php

namespace Tests;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * The PostgreSQL tests through "postgres://", the scheme byjg/anydataset-db also accepts (and the
 * one Gluo's installer writes). PgsqlDatabase used to answer only to "pgsql".
 */
#[RequiresPhpExtension('pdo_pgsql')]
class PostgresSchemeAliasTest extends PostgresDatabaseTest
{
    protected string $scheme = 'postgres';
}
