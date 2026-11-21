<?php

require __DIR__ . '/config/conexao.php';

// Proteger os textos exibidos na pagina
function escapar($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Data escolhida pelo usuario
$dataSelecionada = $_GET['data'] ?? date('Y-m-d');
$erro = '';
$fretes = [];

// Validar a data recebida
$data = is_string($dataSelecionada)
    ? DateTimeImmutable::createFromFormat('!Y-m-d', $dataSelecionada)
    : false;

$dataValida = $data !== false
    && $data->format('Y-m-d') === $dataSelecionada;

if (!$dataValida) {

    $erro = 'Selecione uma data válida.';

} else {

    // Buscar fretes da data selecionada
    $sql = "SELECT
                f.id,
                f.data_frete,
                f.status,
                t.nome AS transportador,
                origem.cidade AS origem,
                destino.cidade AS destino,

                (
                    SELECT GROUP_CONCAT(
                        CONCAT(p.nome, ' (', fp.quantidade, ')')
                        ORDER BY p.nome
                        SEPARATOR ', '
                    )
                    FROM frete_produtos fp
                    INNER JOIN produtos p
                        ON p.id = fp.produto_id
                    WHERE fp.frete_id = f.id
                ) AS produtos

            FROM fretes f

            INNER JOIN transportadores t
                ON t.id = f.transportador_id

            INNER JOIN enderecos origem
                ON origem.id = f.origem_id

            INNER JOIN enderecos destino
                ON destino.id = f.destino_id

            WHERE f.data_frete = :data

            ORDER BY f.id DESC";

    $consulta = $pdo->prepare($sql);

    $consulta->execute([
        ':data' => $dataSelecionada
    ]);

    $fretes = $consulta->fetchAll(PDO::FETCH_ASSOC);
}

// Baixar relatorio em CSV
if ($dataValida && isset($_GET['baixar'])) {

    $nomeArquivo = 'fretes_' . $dataSelecionada . '.csv';

    header('Content-Type: text/csv; charset=utf-8');

    header(
        'Content-Disposition: attachment; filename="' .
        $nomeArquivo . '"'
    );

    header('Cache-Control: no-store');

    $arquivo = fopen('php://output', 'w');

    // Facilitar a leitura dos acentos no Excel
    fwrite($arquivo, "\xEF\xBB\xBF");

    // Cabecalho do arquivo CSV
    fputcsv(
        $arquivo,
        [
            'Frete',
            'Data',
            'Origem',
            'Destino',
            'Transportador',
            'Produtos',
            'Status'
        ],
        ';',
        '"',
        ''
    );

    // Adicionar os fretes ao arquivo
    foreach ($fretes as $frete) {

        // Evitar interpretacao de textos como formulas
        $seguro = function ($valor) {
            $texto = (string)$valor;

            if (preg_match('/^[=+\-@\t\r]/u', ltrim($texto))) {
                return "'" . $texto;
            }

            return $texto;
        };

        fputcsv(
            $arquivo,
            [
                $frete['id'],
                date('d/m/Y', strtotime($frete['data_frete'])),
                $seguro($frete['origem']),
                $seguro($frete['destino']),
                $seguro($frete['transportador']),
                $seguro($frete['produtos'] ?? ''),
                $seguro($frete['status'])
            ],
            ';',
            '"',
            ''
        );
    }

    fclose($arquivo);
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Relatórios | LogiGest</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="sistema">

    <?php require __DIR__ . '/includes/menu.php'; ?>

    <main class="conteudo">

        <header class="cabecalho">
            <div>
                <h1>Relatórios</h1>
                <p>
                    Consulte e exporte fretes
                    de um determinado dia.
                </p>
            </div>
        </header>

        <?php if ($erro !== ''): ?>
            <div class="alert alert-danger">
                <?= escapar($erro) ?>
            </div>
        <?php endif; ?>

        <!-- Filtro de fretes -->
        <section class="card painel mb-4">
            <div class="card-body p-4">

                <h5>Filtrar fretes por data</h5>
                <p>
                    Selecione um dia para visualizar
                    os registros.
                </p>

                <form method="GET">

                    <div class="row g-3 align-items-end">

                        <div class="col-md-5">

                            <label class="form-label">
                                Data do frete
                            </label>

                            <input
                                type="date"
                                name="data"
                                class="form-control"
                                value="<?= escapar($dataSelecionada) ?>"
                                required
                            >

                        </div>

                        <div class="col-md-7">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Filtrar fretes
                            </button>

                            <?php if ($dataValida): ?>

                                <a
                                    href="relatorios.php?data=<?= escapar($dataSelecionada) ?>&amp;baixar=1"
                                    class="btn btn-outline-success ms-2"
                                >
                                    Baixar relatório CSV
                                </a>

                            <?php endif; ?>

                        </div>

                    </div>

                </form>

            </div>
        </section>

        <!-- Resultado da consulta -->
        <section class="card painel">
            <div class="card-body p-4">

                <h5>Fretes do dia</h5>

                <p>
                    <?= count($fretes) ?>
                    frete(s) encontrado(s).
                </p>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Frete</th>
                                <th>Data</th>
                                <th>Origem</th>
                                <th>Destino</th>
                                <th>Transportador</th>
                                <th>Produtos</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (count($fretes) === 0): ?>

                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center py-4"
                                >
                                    Nenhum frete encontrado
                                    nesta data.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($fretes as $frete): ?>

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
                                        <?= escapar($frete['origem']) ?>
                                    </td>

                                    <td>
                                        <?= escapar($frete['destino']) ?>
                                    </td>

                                    <td>
                                        <?= escapar($frete['transportador']) ?>
                                    </td>

                                    <td>
                                        <?= escapar($frete['produtos'] ?? '') ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-success">
                                            <?= escapar($frete['status']) ?>
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
