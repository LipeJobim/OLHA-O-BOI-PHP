<?php
session_start();
if (!isset($_SESSION['email']) || !isset($_SESSION['senha']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$conn = new mysqli("localhost", "root", "root", "olhaboi");
if ($conn->connect_error) {
    die("Erro de conexão");
}

$sql = "SELECT id, nome, imagem FROM boi";
$result = $conn->query($sql);

$bois = [];
while ($row = $result->fetch_assoc()) {
    $bois[] = $row;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Bois</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cata.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<header class="topo">
    <div class="titulo-box">
        <h1>Catálogo de Bois</h1>
        <span>Selecione o animal desejado</span>
    </div>
    <a href="sair.php" class="btn-sair">Sair</a>
</header>

<section class="busca">
    <input type="text" id="filtro" placeholder="Buscar boi pelo nome...">
</section>

<main class="catalogo" id="lista-bois">
<?php if (empty($bois)): ?>
    <p class="vazio">Nenhum boi encontrado.</p>
<?php endif; ?>

<?php foreach ($bois as $boi): ?>
    <div class="produto">
        <span class="badge">Disponível</span>

        <img src="imagens/<?php echo $boi['imagem'] ?: 'padrao.jpg'; ?>" 
             alt="<?php echo htmlspecialchars($boi['nome']); ?>">

        <h3><?php echo htmlspecialchars($boi['nome']); ?></h3>

        <a href="detalhes.php?id=<?php echo $boi['id']; ?>">
            <i class="fa fa-circle-info"></i> Detalhes
        </a>

        <a href="carrinho.php?boi_id=<?php echo $boi['id']; ?>&user_id=<?php echo $_SESSION['user_id']; ?>">
            <i class="fa fa-cart-shopping"></i> Comprar
        </a>
    </div>
<?php endforeach; ?>
</main>

<footer>
    © 2026 – Sistema OlhaBoi
</footer>

<script>
document.getElementById("filtro").addEventListener("keyup", function () {
    let termo = this.value.toLowerCase();
    document.querySelectorAll(".produto").forEach(function (card) {
        let nome = card.querySelector("h3").innerText.toLowerCase();
        card.style.display = nome.includes(termo) ? "flex" : "none";
    });
});
</script>

</body>
</html>
