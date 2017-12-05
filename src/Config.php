<?php

namespace Bhusalb;

class Config
{

    public $stateFilePath = null;

    public $timeZone = 'UTC';

    public $dbHost = 'localhost';

    public $dbName = null;

    public $dbUser = null;

    public $dbPassword = null;

    public $elasticSearchIndex = null;

    public $elasticSearchType = null;

    public $sql = null;

    public $maxBulkActions = 500;

    public $elasticSearchHosts = ['localhost'];

    public $dateTimeField = [];

    public $sqlParameter = [];

    public function __construct($config = [])
    {
        foreach ($config as $property => $value)
            if (property_exists($this, $property))
                $this->{$property} = $value;
    }


    public function isValid()
    {
        $requiredFields = [
            'stateFilePath',
            'dbName',
            'dbPassword',
            'dbUser',
            'elasticSearchType',
            'elasticSearchIndex',
            'sql'
        ];

        foreach ($requiredFields as $value)
            if (is_null($this->{$value}))
                return false;

        return true;
    }
}