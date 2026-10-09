<?php
$db = "teste";
$host = "localhost";
$port = "3308";
$pass = "";
$user = "root";


$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo "Erro na conexão: " . $conn->connect_error;
} else {
    echo "Good Good";
}
