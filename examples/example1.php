<?php

require_once(dirname(__DIR__).'/vendor/autoload.php');

use Pancakes\Pancakes;

$urls = array(
    'www.google.com',
    'www.datashovel.com',
    'www.yahoo.com',
    'www.linkedin.com',
    'www.php.net'
);

$pancakes = new Pancakes();
foreach($urls as $url){
	$pancakes->stack('url',$url);
}
$pancakes->eat();

foreach($urls as $url){
	file_put_contents(__DIR__.'/results/'.$url, $pancakes->buffer['url'][$url]);
}
