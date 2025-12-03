<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'db.php';

$message = "";
$messageType = "is-info";

// Registrar entrega desde el botón de "Marcar como entregado"
if (isset($_POST['entregar'])) {
    $codAlumno = $_POST['codAlumno'];
    $operador  = $_SESSION['username'] ?? 'Sistema';
    $lugar     = $_POST['lugar'] ?? '';

    // Verificar si ya fue entregado
    $stmt = $pdo->prepare("SELECT * FROM entregas WHERE CodAlumno = ?");
    $stmt->execute([$codAlumno]);
    if ($stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO entregas (CodAlumno, fecha_entrega, operador, lugar) VALUES (?, NOW(), ?, ?)");
        try {
            $stmt->execute([$codAlumno, $operador, $lugar]);
            $message = "✅ Refrigerio entregado a " . $codAlumno;
            $messageType = "is-success";
        } catch (PDOException $e) {
            $message = "⚠️ Error: " . $e->getMessage();
            $messageType = "is-danger";
        }
    } else {
        $message = "⚠️ Este estudiante ya recibió su refrigerio";
        $messageType = "is-warning";
    }
}

// Obtener todos los estudiantes
$stmt = $pdo->query("SELECT CodAlumno, Nombres1, Cedula1 FROM estudiantes ORDER BY Nombres1");
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entrega de Refrigerios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<section class="section">
    <div class="container">
        <!-- Botón de cerrar sesión -->
        <div class="level">
            <div class="level-left">
                <a href="logout.php" class="button is-danger is-small">Salir</a>
            </div>
            <h1 class="title has-text-centered">Registro de Refrigerios</h1>

        </div>

        
        <?php if ($message): ?>
            <div class="notification <?= $messageType ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <div class="field search">
            <label class="label">Buscar estudiante:</label>
            <div class="control">
                <input class="input" type="text" id="searchInput" placeholder="Nombre, Código o Cédula">
            </div>
        </div>

        <table class="table is-striped is-fullwidth">
            <thead>
                <tr>
                    <th class="codigo">Código</th>
                    <th class="nombre">Nombre</th>                    
                    <th class="accion">Acción</th>
                </tr>
            </thead>
            <tbody id="estudianteTable">
                <?php foreach ($estudiantes as $est): ?>
                <tr>
                    <td><?= htmlspecialchars($est['CodAlumno']) ?></td>
                    <td><p class="nombres"><?= htmlspecialchars($est['Nombres1']) ?></p><?= htmlspecialchars($est['Cedula1']) ?></td>
                    <td>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="codAlumno" value="<?= htmlspecialchars($est['CodAlumno']) ?>">
                            <button class="button is-success is-medium" type="submit" name="entregar">Entregar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
    // Filtrado en tiempo real
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#estudianteTable tr');

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let match = false;
            cells.forEach(cell => {
                if(cell.textContent.toLowerCase().includes(filter)){
                    match = true;
                }
            });
            row.style.display = match ? '' : 'none';
        });
    });
</script>
</body>
</html>
