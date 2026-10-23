<?php
/**
 * config/config.php — Conexão Centralizada com o Banco de Dados (MySQL)
 * Compatível 100% com Linux (cPanel, Hospedagens Compartilhadas, VPS, Docker) e Windows (Laragon)
 */

// Desativa exceptions automáticas para garantir tratamento gracioso de erros no PHP 8.1+
mysqli_report(MYSQLI_REPORT_OFF);

$db   = getenv('DB_NAME') ?: "tccdb";
$host = getenv('DB_HOST') ?: "localhost";
$port = (int) (getenv('DB_PORT') ?: 3306);
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : (getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : "mysql");

$conn = @new mysqli($host, $user, $pass, $db, $port);

// Fallback de porta: se falhar na porta 3306 em localhost, tenta 3308 (comum em setups Windows Laragon)
if ($conn->connect_error && $port === 3306 && $host === 'localhost') {
    $conn = @new mysqli($host, $user, $pass, $db, 3308);
}

if ($conn->connect_error) {
    error_log("Erro na conexão com o banco de dados: " . $conn->connect_error);
    die("Não foi possível conectar ao banco de dados. Verifique as configurações no config/config.php.");
}

// Configuração obrigatória de charset UTF-8 para Linux (evita erros em json_encode e acentuação)
$conn->set_charset("utf8mb4");

/**
 * Retorna o nome da tabela com a capitalização exata existente no banco de dados.
 * Essencial para servidores Linux (ext4), onde o MySQL é estritamente case-sensitive (lower_case_table_names=0).
 */
function db_table(mysqli $conn, string $tabela): string {
    static $map = null;
    if ($map === null) {
        $map = [];
        $res = @$conn->query("SHOW TABLES");
        if ($res) {
            while ($row = $res->fetch_row()) {
                $map[strtolower($row[0])] = $row[0];
            }
        }
    }
    $lower = strtolower($tabela);

    // Mapeamento direto ou resolução de fallback para variações comuns
    if (!isset($map[$lower])) {
        if ($lower === 'usuario' && isset($map['usuarios'])) {
            $lower = 'usuarios';
        } elseif ($lower === 'usuarios' && isset($map['usuario'])) {
            $lower = 'usuario';
        }
    }

    $real = $map[$lower] ?? $tabela;
    return "`" . str_replace("`", "``", $real) . "`";
}
