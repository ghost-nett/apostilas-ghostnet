<?php

$host = "crossover.proxy.rlwy.net";
$port = "50901";
$user = "root";
$pass = "CMPRDWTTukuXcxNsukwrFOMCISHFJsOv";
$dbname = "railway";

// Conexão
$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Checar conexão
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}


$conn->set_charset("utf8");
?>