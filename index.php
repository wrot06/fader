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

    // Obtener nombre del estudiante
    $stmt = $pdo->prepare("SELECT Nombres1 FROM estudiantes WHERE CodAlumno = ?");
    $stmt->execute([$codAlumno]);
    $est = $stmt->fetch(PDO::FETCH_ASSOC);
    $nombre = $est['Nombres1'] ?? $codAlumno;

    // Verificar si ya fue entregado
    $stmt = $pdo->prepare("SELECT * FROM entregas WHERE CodAlumno = ?");
    $stmt->execute([$codAlumno]);

    if ($stmt->rowCount() == 0) {
        $stmt = $pdo->prepare("INSERT INTO entregas (CodAlumno, fecha_entrega, operador, lugar) VALUES (?, NOW(), ?, ?)");
        try {
            $stmt->execute([$codAlumno, $operador, $lugar]);
            $message = "✅ Refrigerio entregado a <strong>$nombre</strong>";
            $messageType = "is-success";
        } catch (PDOException $e) {
            $message = "⚠️ Error: " . $e->getMessage();
            $messageType = "is-danger";
        }
    } else {
        $message = "⚠️ Refrigerio YA entregado a <strong>$nombre</strong>";
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

<label class="label">Buscar estudiante:</label>
<div class="field search has-addons is-fullwidth">            
    <div class="control is-expanded">
        <input class="input is-medium is-fullwidth" type="text" id="searchInput" placeholder="Nombre, Código o Cédula">
    </div>
        <div class="control">
<button id="btnLimpiar" class="button is-info is-medium" type="button">
    Limpiar
</button>

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
    <?php
        // Verificar si ya fue entregado
        $stmtEnt = $pdo->prepare("SELECT 1 FROM entregas WHERE CodAlumno = ?");
        $stmtEnt->execute([$est['CodAlumno']]);
        $yaEntregado = $stmtEnt->rowCount() > 0;
    ?>

    <?php if ($yaEntregado): ?>
        <span class="tag is-warning is-light"> Ya entregado </span>
    <?php else: ?>
        <form method="POST" style="margin:0;">
            <input type="hidden" name="codAlumno" value="<?= htmlspecialchars($est['CodAlumno']) ?>">
            <button class="button is-success is-medium" type="submit" name="entregar">Entregar</button>
        </form>
    <?php endif; ?>
</td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<script>
// ------------------------------
//  FILTRO EN TIEMPO REAL
// ------------------------------
const searchInput = document.getElementById('searchInput');

searchInput.addEventListener('keyup', function() {
    const filter = searchInput.value.toLowerCase();
    const rows = document.querySelectorAll('#estudianteTable tr');

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        let match = false;
        cells.forEach(cell => {
            if (cell.textContent.toLowerCase().includes(filter)) {
                match = true;
            }
        });
        row.style.display = match ? '' : 'none';
    });
});

// ------------------------------
//  BOTÓN LIMPIAR
// ------------------------------
document.getElementById('btnLimpiar').addEventListener('click', function() {
    searchInput.value = ""; // limpiar input
    const rows = document.querySelectorAll('#estudianteTable tr');

    rows.forEach(row => {
        row.style.display = ""; // mostrar todo
    });

    searchInput.focus(); // opcional
});

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');

    // Cuando se presiona cualquier botón de "Entregar"
    document.querySelectorAll('button[name="entregar"]').forEach(btn => {
        btn.addEventListener('click', function() {
            // Después de un breve retraso, para que el POST se ejecute
            setTimeout(() => {
                searchInput.focus(); // vuelve a poner el foco
            }, 100);
        });
    });
});
</script>

</body>
</html>
