

<?php

require __DIR__ . '/config/conexao.php';

$erro = '';
$idEdicao = 0;

$campos = [
    'descricao' => '',
    'cep' => '',
    'logradouro' => '',
    'numero' => '',
    'bairro' => '',
    'cidade' => '',
    'estado' => '',
    'complemento' => ''
];

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idRecebido = trim($_POST['id'] ?? '');

    // Identificar se é cadastro ou edição
    if ($idRecebido !== '') {
        $idEdicao = filter_var(
            $idRecebido,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
    }

    // Receber os campos do formulário
    foreach ($campos as $campo => $valor) {
        $campos[$campo] = trim($_POST[$campo] ?? '');
    }

    $campos['estado'] = strtoupper($campos['estado']);

    // Validar os dados
    if (
        $idEdicao === false ||
        $campos['descricao'] === '' ||
        $campos['logradouro'] === '' ||
        $campos['numero'] === '' ||
        $campos['bairro'] === '' ||
        $campos['cidade'] === '' ||
        !preg_match('/^[A-Z]{2}$/', $campos['estado'])
    ) {
        $erro = 'Preencha os campos corretamente.';

    } else {

        if ($idEdicao === 0) {

            // Cadastrar novo endereço
            $sql = "INSERT INTO enderecos
                    (descricao, cep, logradouro, numero,
                     bairro, cidade, estado, complemento)
                    VALUES
                    (:descricao, :cep, :logradouro, :numero,
                     :bairro, :cidade, :estado, :complemento)";

        } else {

            // Atualizar endereço existente
            $sql = "UPDATE enderecos SET
                    descricao = :descricao,
                    cep = :cep,
                    logradouro = :logradouro,
                    numero = :numero,
                    bairro = :bairro,
                    cidade = :cidade,
                    estado = :estado,
                    complemento = :complemento
                    WHERE id = :id";
        }

        $dados = $campos;

        if ($idEdicao !== 0) {
            $dados['id'] = $idEdicao;
        }

        $consulta = $pdo->prepare($sql);
        $consulta->execute($dados);

        if ($idEdicao === 0) {
            header('Location: enderecos.php?salvo=1');
        } else {
            header('Location: enderecos.php?editado=1');
        }

        exit;
    }

// Buscar um endereço para editar
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

        $sql = "SELECT * FROM enderecos WHERE id = :id";

        $consulta = $pdo->prepare($sql);
        $consulta->execute(['id' => $idEdicao]);

        $endereco = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($endereco) {

            foreach ($campos as $campo => $valor) {
                $campos[$campo] = $endereco[$campo] ?? '';
            }

        } else {
            $erro = 'Endereço não encontrado.';
            $idEdicao = 0;
        }
    }
}

// Listar os endereços
$sql = "SELECT * FROM enderecos ORDER BY id DESC";

$consulta = $pdo->query($sql);
$enderecos = $consulta->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Endereços | LogiGest</title>

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
                <h1>Endereços</h1>
                <p>Cadastre os locais utilizados nas rotas.</p>
            </div>
        </header>

        <?php if (isset($_GET['salvo'])): ?>
            <div class="alert alert-success">
                Endereço cadastrado com sucesso!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['editado'])): ?>
            <div class="alert alert-success">
                Endereço atualizado com sucesso!
            </div>
        <?php endif; ?>

        <?php if ($erro !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($erro) ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de cadastro -->
        <section class="card painel mb-4">
            <div class="card-body p-4">

                <h5>Cadastro de endereço</h5>
                <p>
                    Um endereço pode ser usado como
                    origem ou destino.
                </p>

                <form method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?= htmlspecialchars((string)$idEdicao, ENT_QUOTES, 'UTF-8') ?>"
                >

                    <div class="row g-3">

                        <?php
                        $camposFormulario = [
                            'descricao' => 'Descrição do local',
                            'cep' => 'CEP',
                            'logradouro' => 'Logradouro',
                            'numero' => 'Número',
                            'bairro' => 'Bairro',
                            'cidade' => 'Cidade',
                            'estado' => 'Estado (UF)',
                            'complemento' => 'Complemento'
                        ];
                        ?>

                        <?php foreach ($camposFormulario as $nome => $titulo): ?>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <?= $titulo ?>
                                </label>

                                <input
                                    type="text"
                                    name="<?= $nome ?>"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        $campos[$nome],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>"
                                    <?= in_array($nome, [
                                        'descricao',
                                        'logradouro',
                                        'numero',
                                        'bairro',
                                        'cidade',
                                        'estado'
                                    ]) ? 'required' : '' ?>
                                    <?= $nome === 'estado'
                                        ? 'maxlength="2"'
                                        : '' ?>
                                    <?= $nome === 'cep'
                                        ? 'maxlength="9"'
                                        : '' ?>
                                >
                            </div>

                        <?php endforeach; ?>

                    </div>

                    <div class="mt-4">
                        
                    <button type="submit" class="btn btn-primary">
                        <?= $idEdicao > 0
                            ? 'Salvar alterações'
                            : 'Salvar endereço' ?>
                    </button>

                    <?php if ($idEdicao > 0): ?>
                        <a href="enderecos.php"
                        class="btn btn-outline-secondary ms-2">
                            Cancelar
                        </a>
                    <?php endif; ?>
                    </div>

                </form>
            </div>
        </section>

        <!-- Lista de endereços -->
        <section class="card painel">
            <div class="card-body p-4">

                <h5>Endereços cadastrados</h5>
                <p>Locais disponíveis para os fretes.</p>

                <div class="table-responsive">
                    <table class="table table-hover">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Local</th>
                                <th>Cidade</th>
                                <th>Estado</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (count($enderecos) === 0): ?>

                            <tr>
                                <td colspan="5"
                                    class="text-center py-4">
                                    Nenhum endereço cadastrado.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($enderecos as $endereco): ?>

                                <tr>
                                    <td><?= (int)$endereco['id'] ?></td>

                                    <td><?= htmlspecialchars(
                                        $endereco['descricao'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <td><?= htmlspecialchars(
                                        $endereco['cidade'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                    <td><?= htmlspecialchars(
                                        $endereco['estado'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?></td>

                                <td>
                                    <a
                                        href="enderecos.php?editar=<?= (int)$endereco['id'] ?>"
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
