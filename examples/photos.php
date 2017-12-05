<?php

require_once('vendor/autoload.php');


use Bhusalb\Config;
use Bhusalb\Sync;
use Bhusalb\ExecutionTime;

$executionTime = new ExecutionTime();

$executionTime->start();

$config = new Config([
    'stateFilePath' => 'path-to-state-file/photo-state-file.json',
    'dbName' => 'bsr',
    'dbPassword' => 'root',
    'dbUser' => 'root',
    'elasticSearchType' => 'photo',
    'elasticSearchIndex' => 'bsr_new',
    'sql' => "select photos.id as _id, photos.id, event_id as _parent, copyright, keywords, fotos_bullet, caption, photos.created_at, sale_type, byline_title, source, fotos_exclusive, province, photos.updated_at, credit, original_path, byline, headline, fotos_country, height, special_instructions, fotos_callforimage, fotos_sup_code, photos.deleted_at, event_id, fotos_reporter, is_main, name, width, fotos_promo, photos.supplier_id, photos.status, events.title, users.first_name, users.middle_name, users.last_name, events.event_date, IF(photos.height=photos.width,1,IF(photos.width>photos.height,2,IF(photos.width<photos.height,3,NULL))) as orientation, photos.fotos_location, photos.fotos_venue, photo_popularity.popularity, events.status as event_status, events.category_id, NOW() as documented_date from photos join events on events.id=photos.event_id join users on users.id=photos.supplier_id left join photo_popularity on photo_popularity.photo_id=photos.id where photos.updated_at > ? or events.updated_at > ?",
    'dateTimeField' => ['deleted_at', 'documented_date' , 'created_at', 'updated_at', 'event_date'],
    'sqlParameter' => ['lastExecutionTime', 'lastExecutionTime'],
    'maxBulkActions' => 500
]);

$sync = new Sync($config);

$sync->run();

$executionTime->end();

echo $executionTime;

