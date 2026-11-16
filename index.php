
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

            <button class="btn btn-primary" disabled>
                + Novo frete
            </button>
        </header>

        <!-- Cartões de resumo -->
        <section class="row g-4 mb-4">

            <div class="col-md-4">
                <div class="card resumo">
                    <div class="card-body">
                        <p>Produtos cadastrados</p>
                        <h2>0</h2>
                        <small>Itens disponíveis</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card resumo">
                    <div class="card-body">
                        <p>Transportadores</p>
                        <h2>0</h2>
                        <small>Parceiros cadastrados</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card resumo">
                    <div class="card-body">
                        <p>Fretes gerados</p>
                        <h2>0</h2>
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
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    Nenhum frete cadastrado.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>

    </main>
</div>

</body>
</html>
