<?php

use iutnc\NetVOD\dispatch\Dispatcher;

require_once 'vendor/autoload.php';

iutnc\NetVOD\repository\NetVODRepository::setConfig( 'db.config.ini' );
// DANGER IL FAUDRA CACHER LE .ini


session_start();

$action = $_GET['action'] ?? '';
$app = new Dispatcher($action);
$app->run();