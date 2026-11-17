
<?php

// Copie este arquivo para conexao.php
// e configure os dados do seu MySQL.

$servidor = "127.0.0.1";
$banco = "logigest";
$usuario = "root";
$senha = "";

try {
    $pdo = new PDO(
        "mysql:host=$servidor;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (PDOException $erro) {
    error_log($erro->getMessage());
    die("Não foi possível conectar ao banco de dados.");
}
