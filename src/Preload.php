<?php

use App\Database\ORM;
use App\Helpers\Cache;

// Load DB
ORM::getInstance();
// Load Cache
Cache::getInstance();
