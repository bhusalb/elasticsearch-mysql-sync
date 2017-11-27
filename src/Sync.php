<?php

namespace Bhusalb;

use Elasticsearch\ClientBuilder;

class Sync
{
    public $config;

    public $stateFileData;

    public $stateFile;

    public $startTimeStamp;

    public $db;

    public $elasticSearchClient;

    public function __construct(Config $config)
    {
        $this->config = $config;

        if (!$this->config->isValid())
            throw new ConfigParameterMissingException();

        $this->stateFile = new StateFile($this->config->stateFilePath);

        $this->startTimeStamp = time();

        $this->db = new \PDO('mysql:host=' . $this->config->dbHost . ';dbname=' . $this->config->dbName,
            $this->config->dbUser,
            $this->config->dbPassword
        );

        $this->elasticSearchClient = ClientBuilder::create()
            ->setHosts($this->config->elasticSearchHosts)
            ->build();
    }

    public function run()
    {
        $counter = 0;
        $params = [];
        while (true) {
            $statement = $this->db->prepare($this->config->sql
                . ' limit '
                . $this->config->maxBulkActions
                . ' offset '
                . ($this->config->maxBulkActions * $counter)
            );
            $statement->execute([$this->stateFile->lastExecutionTime]);

            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($results) == 0)
                break;

            foreach ($results as $row) {
                $params['body'][] = [
                    'index' => [
                        '_index' => $this->config->elasticSearchIndex,
                        '_type' => $this->config->elasticSearchType,
                        '_id' => $row['_id']
                    ]
                ];

                $params['body'][] = $row;
            }

            $this->elasticSearchClient->bulk($params);
            $counter++;
        }
    }

}