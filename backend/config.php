<?php
// Oturumu başlat
session_start();

// CORS başlıkları — credentials için kritik!
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

require_once __DIR__ . '/vendor/autoload.php';

use MongoDB\Client;

$uri = "mongodb://admin:Bel33.Mez33@mezitlibim.mezitli.bel.tr:27018/?authSource=admin";

$client = new Client($uri);
$database = $client->yemekhane;

function db() {
    global $database;
    return $database;
}