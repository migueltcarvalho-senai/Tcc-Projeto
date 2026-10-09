<?php
$db   = "tccdb";
$host = "localhost";
$port = "3308";
$pass = "";
$user = "root";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    // Em produção: registrar erro em log, nunca expor ao usuário
    error_log("Erro na conexão com o banco: " . $conn->connect_error);
    die("Não foi possível conectar ao banco de dados.");
}