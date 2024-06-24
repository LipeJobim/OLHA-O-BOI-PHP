<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['senha']) || !isset($_SESSION['user_id'])) {
    unset($_SESSION['email']);
    unset($_SESSION['senha']);
    unset($_SESSION['user_id']);
    header('Location: login.php');
    exit;
}
$logado = $_SESSION['email'];

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "olhaboi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, nome, imagem FROM boi";
$result = $conn->query($sql);

$bois = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $bois[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="catalogo.css">
    <title>Catálogo de Bois</title>
</head>
<body>
    <div class="d-flex">
        <a href="sair.php" class="">Encerrar</a>
    </div>
    <h1>Catálogo</h1>
    <div class="catalogo">
        <?php foreach ($bois as $boi): ?>
            <div class="produto">
                <?php if (isset($boi["imagem"])): ?>
                    <img src="imagens/<?php echo $boi["imagem"]; ?>" alt="<?php echo $boi["nome"]; ?>">
                <?php endif; ?>
                <h3><?php echo $boi["nome"]; ?></h3>
                <a href="detalhes.php?id=<?php echo $boi["id"]; ?>">Detalhes</a>
                <a href="carrinho.php?boi_id=<?php echo $boi["id"]; ?>&user_id=<?php echo $_SESSION["user_id"]; ?>">Comprar</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
