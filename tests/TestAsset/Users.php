<?php

namespace Pop\Auth\Test\TestAsset;

use Pop\Db\Db;
use Pop\Db\Record;
use Pop\Db\Adapter;

class Users extends Record
{
    public function __construct(array $columns = null, Adapter\AbstractAdapter $db = null)
    {
        if (null === $db) {
            $db = Db::connect('sqlite', [
                'database' => __DIR__ . '/../tmp/access.sqlite'
            ]);
        }
        parent::__construct($columns, $db);
    }
}