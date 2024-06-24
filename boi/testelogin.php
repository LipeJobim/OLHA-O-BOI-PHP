<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "olhaboi";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT id, email, nome FROM usuario WHERE email = ? AND senha = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $email, $senha);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $email, $nome);
            $stmt->fetch();

            $_SESSION['email'] = $email;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['nome'] = $nome;
            $_SESSION['senha'] = $senha;

            header("Location: catalogo.php");
            exit;
        } else {
            echo "Usuário ou senha inválidos.";
        }
    } else {
        echo "Erro ao preparar a consulta: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
?>
