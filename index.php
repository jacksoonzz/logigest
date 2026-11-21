<?php

require __DIR__ . '/config/conexao.php';

// Contar os produtos cadastrados
$sqlProdutos = "SELECT COUNT(*) FROM produtos";
$totalProdutos = (int) $pdo->query($sqlProdutos)->fetchColumn();

// Contar os transportadores
$sqlTransportadores = "SELECT COUNT(*) FROM transportadores";
$totalTransportadores = (int) $pdo->query($sqlTransportadores)->fetchColumn();

// Contar os fretes gerados
$sqlFretes = "SELECT COUNT(*) FROM fretes";
$totalFretes = (int) $pdo->query($sqlFretes)->fetchColumn();

// Buscar os cinco fretes mais recentes
$sqlRecentes = "SELECT
                    f.id,
                    f.data_frete,
                    f.status,
                    t.nome AS transportador,
                    origem.cidade AS origem,
                    destino.cidade AS destino

                FROM fretes f

                INNER JOIN transportadores t
                    ON t.id = f.transportador_id

                INNER JOIN enderecos origem
                    ON origem.id = f.origem_id

                INNER JOIN enderecos destino
                    ON destino.id = f.destino_id

                ORDER BY f.id DESC
                LIMIT 5";

$consulta = $pdo->query($sqlRecentes);
$fretesRecentes = $consulta->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | LogiGest</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Nosso CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="sistema">

    <!-- Menu lateral -->
    <?php require __DIR__ . '/includes/menu.php'; ?>

    <!-- Conteúdo principal -->
    <main class="conteudo">

        <header class="cabecalho">
            <div>
                <h1>Dashboard</h1>
                <p>Visão geral das operações logísticas.</p>
            </div>
            
            <a href="fretes.php" class="btn btn-primary">
                + Novo frete
            </a>
        </header>

        <!-- Cartões de resumo -->
        <section class="row g-4 mb-4">

            <div class="col-md-4">
                <div class="card resumo">
                    <div class="card-body">
                        <p>Produtos cadastrados</p>                    
                        <h2><?= $totalProdutos ?></h2>
                        <small>Itens disponíveis</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card resumo">
                    <div class="card-body">
                        <p>Transportadores</p>                      
                        <h2><?= $totalTransportadores ?></h2>
                        <small>Parceiros cadastrados</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card resumo">
                    <div class="card-body">
                        <p>Fretes gerados</p>                       
                        <h2><?= $totalFretes ?></h2>
                        <small>Registros no sistema</small>
                    </div>
                </div>
            </div>

        </section>

        <!-- Tabela de fretes -->
        <section class="card painel">
            <div class="card-body">

                <h5>Fretes recentes</h5>
                <p>Acompanhe os últimos fretes cadastrados.</p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Frete</th>
                                <th>Data</th>
                                <th>Origem</th>
                                <th>Destino</th>
                                <th>Transportador</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (count($fretesRecentes) === 0): ?>

                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        Nenhum frete cadastrado.
                                    </td>
                                </tr>

                            <?php else: ?>

                                <?php foreach ($fretesRecentes as $frete): ?>

                                    <tr>
                                        <td>
                                            #<?= (int)$frete['id'] ?>
                                        </td>

                                        <td>
                                            <?= date(
                                                'd/m/Y',
                                                strtotime($frete['data_frete'])
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $frete['origem'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $frete['destino'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $frete['transportador'],
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </td>

                                        <td>
                                            <span class="badge bg-success">
                                                <?= htmlspecialchars(
                                                    $frete['status'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </span>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>

            </div>
        </section>

    </main>
</div>

</body>
</html>
