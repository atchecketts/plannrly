<?php

// Set environment BEFORE autoload
$_SERVER['APP_ENV'] = 'testing';
$_ENV['APP_ENV'] = 'testing';
putenv('APP_ENV=testing');

require_once __DIR__.'/../vendor/autoload.php';
