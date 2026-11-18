

<?php

require __DIR__ . '/config/conexao.php';

$erro = '';
$idEdicao = 0;

$campos = [
    'nome' => '',
    'documento' => '',
    'telefone' => '',
    'veiculo' => '',
    'placa' => ''
];

// Receber os dados do formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idRecebido = trim($_POST['id'] ?? '');

    if ($idRecebido !== '') {
        $idEdicao = filter_var(
            $idRecebido,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
    }

    foreach ($campos as $campo => $valor) {
        $campos[$campo] = trim($_POST[$campo] ?? '');
    }

    $tiposPermitidos = ['Caminhão', 'Van', 'Carro', 'Moto'];

    // Validar os campos
    if (
        $idEdicao === false ||
        $campos['nome'] === '' ||
        !in_array($campos['veiculo'], $tiposPermitidos, true)
    ) {
        $erro = 'Preencha os campos corretamente.';

    } else {

        if ($idEdicao === 0) {

            // Cadastrar novo transportador
            $sql = "INSERT INTO transportadores
                    (nome, documento, telefone, veiculo, placa)
                    VALUES
                    (:nome, :documento, :telefone, :veiculo, :placa)";

        } else {

            // Atualizar transportador existente
            $sql = "UPDATE transportadores SET
                    nome = :nome,
                    documento = :documento,
                    telefone = :telefone,
                    veiculo = :veiculo,
                    placa = :placa
                    WHERE id = :id";
        }

        $dados = $campos;

        if ($idEdicao !== 0) {
            $dados['id'] = $idEdicao;
        }

        $consulta = $pdo->prepare($sql);
        $consulta->execute($dados);

        if ($idEdicao === 0) {
            header('Location: transportadores.php?salvo=1');
        } else {
            header('Location: transportadores.php?editado=1');
        }

        exit;
    }

// Buscar transportador para edicao
} elseif (isset($_GET['editar'])) {

    $idEdicao = filter_var(
        $_GET['editar'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($idEdicao === false) {

        $erro = 'Identificador inválido.';
        $idEdicao = 0;

    } else {

        $sql = "SELECT * FROM transportadores WHERE id = :id";

        $consulta = $pdo->prepare($sql);
        $consulta->execute(['id' => $idEdicao]);

        $transportador = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($transportador) {

            foreach ($campos as $campo => $valor) {
                $campos[$campo] = $transportador[$campo] ?? '';
            }

        } else {

            $erro = 'Transportador não encontrado.';
            $idEdicao = 0;
        }
    }
}

// Listar os transportadores
$sql = "SELECT * FROM transportadores ORDER BY id DESC";

$consulta = $pdo->query($sql);
$transportadores = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Proteger textos exibidos no HTML
function escaparTexto($valor) {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Transportadores | LogiGest</title>

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
                <h1>Transportadores</h1>
                <p>Gerencie os responsáveis pelo transporte.</p>
            </div>
        </header>

        <?php if (isset($_GET['salvo'])): ?>
            <div class="alert alert-success">
                Transportador cadastrado com sucesso!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['editado'])): ?>
            <div class="alert alert-success">
                Transportador atualizado com sucesso!
            </div>
        <?php endif; ?>

        <?php if ($erro !== ''): ?>
            <div class="alert alert-danger">
                <?= escaparTexto($erro) ?>
            </div>
        <?php endif; ?>

        <!-- Formulario -->
        <section class="card painel mb-4">
            <div class="card-body p-4">

                <h5>Cadastro de transportador</h5>
                <p>Registre as informações do transportador.</p>

                <form method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?= $idEdicao > 0 ? (int)$idEdicao : '' ?>"
                >


                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">
                                Nome / Razão social
                            </label>

                            <input
                                type="text"
                                name="nome"
                                class="form-control"
                                maxlength="150"
                                value="<?= escaparTexto($campos['nome']) ?>"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                CPF ou CNPJ
                            </label>

                            <input
                                type="text"
                                name="documento"
                                class="form-control"
                                maxlength="18"
                                value="<?= escaparTexto($campos['documento']) ?>"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Telefone
                            </label>

                            <input
                                type="text"
                                name="telefone"
                                class="form-control"
                                maxlength="20"
                                value="<?= escaparTexto($campos['telefone']) ?>"
                            >
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Veículo
                            </label>

                            <select
                                name="veiculo"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Selecione um veículo
                                </option>

                                <?php
                                $tipos = [
                                    'Caminhão',
                                    'Van',
                                    'Carro',
                                    'Moto'
                                ];
                                ?>

                                <?php foreach ($tipos as $tipo): ?>
                                    <option
                                        value="<?= escaparTexto($tipo) ?>"
                                        <?= $campos['veiculo'] === $tipo
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        <?= escaparTexto($tipo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Placa (opcional)
                            </label>

                            <input
                                type="text"
                                name="placa"
                                class="form-control"
                                maxlength="10"
                                value="<?= escaparTexto($campos['placa']) ?>"
                            >
                        </div>

                    </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <?= $idEdicao > 0
                            ? 'Salvar alterações'
                            : 'Salvar transportador' ?>
                    </button>

                    <?php if ($idEdicao > 0): ?>
                        <a href="transportadores.php"
                        class="btn btn-outline-secondary ms-2">
                            Cancelar
                        </a>
                    <?php endif; ?>
                    </div>

                </form>

            </div>
        </section>

        <!-- Lista de transportadores -->
        <section class="card painel">
            <div class="card-body p-4">

                <h5>Transportadores cadastrados</h5>
                <p>Responsáveis disponíveis para os fretes.</p>

                <div class="table-responsive">
                    <table class="table table-hover">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Transportador</th>
                                <th>Telefone</th>
                                <th>Veículo</th>
                                <th>Placa</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (count($transportadores) === 0): ?>

                            <tr>
                                <td colspan="6"
                                    class="text-center py-4">
                                    Nenhum transportador cadastrado.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($transportadores as $transportador): ?>

                                <tr>
                                    <td>
                                        <?= (int)$transportador['id'] ?>
                                    </td>

                                    <td>
                                        <?= escaparTexto($transportador['nome']) ?>
                                    </td>

                                    <td>
                                        <?= escaparTexto($transportador['telefone']) ?>
                                    </td>

                                    <td>
                                        <?= escaparTexto($transportador['veiculo']) ?>
                                    </td>

                                    <td>
                                        <?= escaparTexto($transportador['placa']) ?>
                                    </td>

                                    <td>
                                        
                                    <a
                                        href="transportadores.php?editar=<?= (int)$transportador['id'] ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Editar
                                    </a>
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
