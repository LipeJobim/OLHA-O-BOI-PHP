<?php
if (isset($_GET['id'])) { 
    $id_boi = intval($_GET['id']); 

    $conexao = new mysqli('localhost', 'root', 'root', 'olhaboi'); 
    if ($conexao->connect_error) {
        die("Erro ao conectar ao banco de dados: " . $conexao->connect_error);
    }

    $consulta = $conexao->prepare("SELECT * FROM boi WHERE id = ?");
    $consulta->bind_param('i', $id_boi);
    $consulta->execute();
    $resultado = $consulta->get_result();
    ?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="detalhes.css">
    <title>Detalhes do Boi</title>
</head>
<body>
<a href="catalogo.php">Voltar</a>
<?php
    if ($resultado->num_rows > 0) { 
        $boi = $resultado->fetch_assoc();
        
        echo '<div class="boi-detalhes">';
        echo "<h1>Detalhes do Boi</h1>";

        if (isset($boi['imagem']) && !empty($boi['imagem'])) {
            echo '<img src="imagens/' . $boi['imagem'] . '" alt="' . $boi['nome'] . '">';
        }

        echo "<p>Nome: " . $boi['nome'] . "</p>"; 
        echo "<p>Raça: " . $boi['raca'] . "</p>"; 
        echo "<p>Peso: " . $boi['peso'] . " kg</p>"; 
        echo "<p>Preço: R$ " . number_format($boi['preco'], 2, ',', '.') . "</p>";
        echo "<p>Nome do Pai: " . $boi['nome_pai'] . "</p>";
        echo "<p>Nome da Mãe: " . $boi['nome_mae'] . "</p>";
        
        echo '</div>';
        
    } else { 
        echo '<div class="boi-detalhes error-message">';
        echo "Nenhum boi encontrado com o ID fornecido.";
        echo '</div>';
    }

    $consulta->close(); 
    $conexao->close(); 
} else {
    echo '<div class="boi-detalhes error-message">';
    echo "ID do boi não fornecido na URL.";
    echo '</div>';
}
?>

</body>
</html>
