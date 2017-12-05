<?php

namespace Bhusalb;

use Elasticsearch\ClientBuilder;


class Sync
{
    protected $config;

    protected $stateFileData;

    protected $stateFile;

    protected $startTimeStamp;

    protected $db;

    protected $elasticSearchClient;


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


        if ($this->config->timeZone)
            date_default_timezone_set($this->config->timeZone);

        $this->startTimeStamp = time();
    }

    public function run()
    {
        $counter = 0;
        while (true) {
            $params = [];
            $statement = $this->db->prepare($this->config->sql
                . ' limit '
                . $this->config->maxBulkActions
                . ' offset '
                . ($this->config->maxBulkActions * $counter)
            );
            $statement->execute($this->getSqlParameter());

            $results = $statement->fetchAll(\PDO::FETCH_ASSOC);

            if (count($results) == 0)
                break;

            foreach ($results as $row) {

                $index = [
                    '_index' => $this->config->elasticSearchIndex,
                    '_type' => $this->config->elasticSearchType,
                ];

                if(isset($row['id'])) {
                    $index['_id'] = $row['_id'];
                    unset($row['_id']);
                }



                if(isset($row['_parent'])) {
                    $index['parent'] = $row['_parent'];
                    unset($row['_parent']);
                }

                $params['body'][] = [
                    'index' => $index
                ];

                foreach ($this->config->dateTimeField as $field)
                    $row[$field] = date(\DateTime::ATOM, strtotime($row[$field]));

                foreach ($row as $key => $value)
                    if (is_null($value) or empty($value))
                        unset($row[$key]);

                $params['body'][] = $row;
            }
            $params = self::convert_from_latin1_to_utf8_recursively($params);
            $res = $this->elasticSearchClient->bulk($params);
            $counter++;
            print('Number rows completed = ' . $this->config->maxBulkActions * $counter . PHP_EOL);
        }

        $this->stateFile->overwrite([
            'lastExecutionTime' => date(\DateTime::ATOM, $this->startTimeStamp),
            'endExecutionTime' => date(\DateTime::ATOM, time())
        ]);

    }

    public function getSqlParameter()
    {
        $parameters = [];
        foreach ($this->config->sqlParameter as $parameter)
            switch ($parameter) {
                case 'lastExecutionTime':
                    $parameters[] = $this->stateFile->lastExecutionTime;
                    break;
                default:
                    throw new InvalidSqlParameter();
                    break;
            }

        return $parameters;
    }

    public static function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat))
            return utf8_encode($dat);
        if (!is_array($dat))
            return $dat;
        $ret = array();
        foreach ($dat as $i => $d)
            $ret[$i] = self::convert_from_latin1_to_utf8_recursively($d);
        return $ret;
    }
}