<?php

namespace Bhusalb\Tests;


use Bhusalb\Config;
use Bhusalb\ConfigParameterMissingException;
use Bhusalb\InvalidStateFileException;
use Bhusalb\StateFileMissingException;
use Bhusalb\Sync;
use PHPUnit\Framework\TestCase;

class SyncTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_if_config_parameter_is_missing()
    {
        $this->expectException(ConfigParameterMissingException::class);

        $config = new Config([]);
        $sync = new Sync($config);
    }

    /**
     * @test
     */
    public function it_throws_exception_if_state_file_is_invalid()
    {
        $config = new Config([
            'dbName' => 'test',
            'dbUser' => 'ram',
            'dbPassword' => 'password',
            'elasticSearchType' => 'bsr',
            'elasticSearchIndex' => 'events',
            'stateFilePath' => '/home',
            'sql' => 'Select * from xyz'
        ]);
        $this->expectException(StateFileMissingException::class);

        $sync = new Sync($config);

    }

    /**
     * @test
     */
    public function it_throws_exception_if_json_is_invalid_in_state_file()
    {
        $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR . 'invalid-sample-state-file.json';

        $config = new Config([
            'dbName' => 'test',
            'dbUser' => 'ram',
            'dbPassword' => 'password',
            'elasticSearchType' => 'bsr',
            'elasticSearchIndex' => 'events',
            'stateFilePath' => $path,
            'sql' => 'Select * from xyz'
        ]);

        $this->expectException(InvalidStateFileException::class);

        $sync = new Sync($config);
    }
}