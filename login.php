<?php
session_start();
require 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Buscar usuario por username
    $stmt = $pdo->prepare("SELECT id, username, password, nombre_completo, rol FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Comparamos contraseña directamente
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nombre_completo'] = $user['nombre_completo'];
            $_SESSION['rol'] = $user['rol'];

            header("Location: index.php");
            exit;
        } else {
            $message = "Contraseña incorrecta";
        }
    } else {
        $message = "Usuario no encontrado";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Refrigerios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<section class="section">
    <div class="container">
        <div class="card" style="max-width: 400px; margin: 50px auto; padding: 30px;">
            <h1 class="title has-text-centered">Iniciar Sesión</h1>           
            
            <?php if ($message): ?>
                <div class="notification is-danger"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label class="label">Usuario</label>
                    <div class="control">
                        <input class="input" type="text" name="username" required autofocus>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Contraseña</label>
                    <div class="control">
                        <input class="input" type="password" name="password" required>
                    </div>
                </div>

                <div class="field">
                    <div class="control has-text-centered">
                        <button class="button is-primary is-fullwidth" type="submit">Entrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>
</body>
</html>
