<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['senha']) || !isset($_SESSION['user_id']) || !isset($_SESSION['nome'])) {
    unset($_SESSION['email']);
    unset($_SESSION['senha']);
    unset($_SESSION['user_id']);
    unset($_SESSION['nome']);
    header('Location: login.php');
    exit;
}

$logado = $_SESSION['email'];
$user_id = $_SESSION['user_id'];
$nome_usuario = $_SESSION['nome'];

$servername = "localhost";
$username_db = "root";
$password_db = "root";
$dbname = "olhaboi";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql_boi = "SELECT id, nome, preco FROM boi";
$result_boi = $conn->query($sql_boi);

$bois = array();

if ($result_boi->num_rows > 0) {
    while ($row = $result_boi->fetch_assoc()) {
        $bois[] = $row;
    }
}

$recibo = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['boi'], $_POST['quantidade'])) {
        $items = array_map(null, $_POST['boi'], $_POST['quantidade']);
        foreach ($items as $item) {
            list($boi_id, $quantidade) = $item;
            if ($boi_id && $quantidade) {
                $sql = "SELECT preco FROM boi WHERE id = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("i", $boi_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    $preco = $row['preco'] ?? 0;

                    $valor_total = $preco * $quantidade;

                    $disponibilidade = 100; 

                    $sql = "SELECT SUM(quantidade) AS total_vendidos FROM compras WHERE boi_id = ?";
                    if ($stmt = $conn->prepare($sql)) {
                        $stmt->bind_param("i", $boi_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $row = $result->fetch_assoc();

                        $total_vendidos = $row['total_vendidos'] ?? 0;
                        $bois_disponiveis = $disponibilidade - $total_vendidos;

                        if ($quantidade <= $bois_disponiveis) {
                            $sql = "INSERT INTO compras (user_id, boi_id, quantidade, data_compra) VALUES (?, ?, ?, NOW())";
                            if ($stmt = $conn->prepare($sql)) {
                                $stmt->bind_param("iii", $user_id, $boi_id, $quantidade);
                                if ($stmt->execute()) {
                                    $recibo .= "Detalhes da Compra:\n";
                                    $recibo .= "Usuário: " . $nome_usuario . "\n";
                                    $recibo .= "Boi: $boi_id\n";
                                    $recibo .= "Quantidade: $quantidade\n";
                                    $recibo .= "Valor Total: R$ $valor_total\n";
                                } else {
                                    $erro = "Erro ao processar a compra: " . $stmt->error;
                                }
                            } else {
                                $erro = "Erro ao preparar a inserção: " . $conn->error;
                            }
                        } else {
                            $erro = "Quantidade de bois indisponível.";
                        }
                    } else {
                        $erro = "Erro ao preparar a consulta: " . $conn->error;
                    }
                } else {
                    $erro = "Erro ao buscar o preço do boi: " . $conn->error;
                }
            } else {
                $erro = "Por favor, selecione o boi e a quantidade.";
            }
        }
    }
}
$pre_selected_boi_id = isset($_GET['boi_id']) ? intval($_GET['boi_id']) : 0;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="car.css">
    <title>Compra de Boi</title>


    <script>
        function calcularTotal() {
            var boiSelects = document.querySelectorAll('select[name="boi[]"]');
            var quantidadeInputs = document.querySelectorAll('input[name="quantidade[]"]');
            var total = 0;

            for (var i = 0; i < boiSelects.length; i++) {
                var quantidade = parseInt(quantidadeInputs[i].value);
                var preco = parseFloat(boiSelects[i].options[boiSelects[i].selectedIndex].getAttribute('data-preco'));

                if (!isNaN(quantidade) && !isNaN(preco)) {
                    total += quantidade * preco;
                }
            }

            document.getElementById('total').textContent = 'Total: R$ ' + total.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('addItem').addEventListener('click', function (e) {
                e.preventDefault();
                var itemContainer = document.getElementById('itemContainer');
                var newItem = itemContainer.children[0].cloneNode(true);
                newItem.querySelector('input').value = 1;
                itemContainer.appendChild(newItem);
            });

            document.getElementById('itemContainer').addEventListener('change', calcularTotal);
            document.getElementById('itemContainer').addEventListener('input', calcularTotal);
        });

        function imprimirRecibo() {
            var conteudoRecibo = document.getElementById('conteudoRecibo').textContent;
            var janelaImprimir = window.open('', 'Imprimir', 'height=400,width=600');
            janelaImprimir.document.write('<html><head><title>Recibo</title>');
            janelaImprimir.document.write('</head><body>');
            janelaImprimir.document.write('<pre>' + conteudoRecibo + '</pre>');
            janelaImprimir.document.write('</body></html>');
            janelaImprimir.document.close();
            janelaImprimir.print();
        }
    </script>
</head>

<body>
    <a href="catalogo.php">Voltar</a>
    <h1>Compra de Boi</h1>
    <?php if ($erro) echo "<p>$erro</p>"; ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div id="itemContainer">
            <div>
                <label for="boi">Selecione o boi:</label>
                <select name="boi[]" id="boi">
                    <option value="">Selecione um boi</option>
                    <?php foreach ($bois as $boi): ?>
                        <option value="<?php echo $boi['id']; ?>" data-preco="<?php echo $boi['preco']; ?>" <?php echo $pre_selected_boi_id == $boi['id'] ? 'selected' : ''; ?>>
                            <?php echo $boi['nome']; ?> - R$ <?php echo $boi['preco']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="quantidade">Quantidade:</label>
                <input type="number" name="quantidade[]" id="quantidade" min="1" value="1">
            </div>
        </div>
        <button id="addItem">Adicionar mais um item</button>
        <p id="total">Total: R$ 0.00</p>
        <input type="submit" value="Comprar">
    </form>

    <?php if ($recibo) { ?>
        <h2>Recibo</h2>
        <form>
            <label for="conteudoRecibo">Recibo:</label>
            <textarea id="conteudoRecibo" rows="6" readonly><?php echo $recibo; ?></textarea>
        </form>

        <button onclick="imprimirRecibo();" class="print-button">Imprimir Recibo</button>
    <?php } ?>
</body>

</html>
