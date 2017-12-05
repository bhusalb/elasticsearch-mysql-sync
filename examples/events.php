<?php

require_once('vendor/autoload.php');


use Bhusalb\Config;
use Bhusalb\Sync;
use Bhusalb\ExecutionTime;

$executionTime = new ExecutionTime();

$executionTime->start();

$config = new Config([
    'stateFilePath' => 'path-to-state-file/event-state-file.json',
    'dbName' => 'bsr',
    'dbPassword' => 'root',
    'dbUser' => 'root',
    'elasticSearchType' => 'event',
    'elasticSearchIndex' => 'bsr_new',
    'sql' => "select events.id as _id, events.id, title, instructions, venue, is_lumpsum, combo, events.created_at, last_name, reporter, middle_name, events.deleted_at, event_type_id, is_videoavailable, category_id, events.updated_at, parent_id, event_date, exclusive, location, first_name, slug, events.status, upload_date, supplier_id, CONCAT(users.first_name,\" \", users.last_name) as full_name from events join users on users.id=events.supplier_id where events.updated_at > ?",
    'dateTimeField' => ['deleted_at', 'created_at', 'updated_at', 'event_date', 'upload_date'],
    'sqlParameter' => ['lastExecutionTime']
]);

$sync = new Sync($config);

$sync->run();

$executionTime->end();

echo $executionTime;

