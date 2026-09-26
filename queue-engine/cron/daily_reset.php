<?php

require dirname(__DIR__).'/bootstrap.php';

$date = $argv[1] ?? date('Y-m-d');
$rows = (new QueueRepository(db()))->reset($date);
echo "Reset {$rows} queue rows for {$date}\n";
