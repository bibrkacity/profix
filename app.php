<?php

include_once __DIR__ . '/src/Comission.php';

$filename = 'input.txt';
if (isset($argv[1]))
    $filename = $argv[1];

$Comission = new Comission($filename);

$parsed = $Comission->parser();

foreach($parsed['rows'] as $one)
    print $one. "\n";

if( count($parsed['errors']) > 0)
{
    print 'Errors:'. "\n";
    foreach($parsed['errors'] as $one)
        print $one. "\n";
}
