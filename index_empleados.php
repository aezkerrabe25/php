<?php
session_start();

if (!isset($_SESSION['errores'])) {
    $_SESSION['errores'] = 0;
}

require_once 'DBManager.php';
$db = new DBManager('localhost', 'root', '', 'empresa');

$errores          = [];
$mensaje_ok       = '';
$mensaje_intentos = '';
$estadistica      = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {

    $nombre     = trim($_POST['nombre']     ?? '');
    $email      = trim($_POST['email']      ?? '');
    $salario    = trim($_POST['salario']    ?? '');
    $fecha_alta = trim($_POST['fecha_alta'] ?? '');

    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio.";
    } elseif (strlen($nombre) < 3 || strlen($nombre) > 50) {
        $errores[] = "El nombre debe tener entre 3 y 50 caracteres.";
    }

    if (empty($email)) {
        $errores[] = "El email es obligatorio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El email no tiene un formato valido.";
    }

    if (empty($salario)) {
        $errores[] = "El salario es obligatorio.";
    } elseif (!is_numeric($salario) || $salario < 1000 || $salario > 99999) {
        $errores[] = "El salario debe ser un numero entre 1000 y 99999.";
    }

    if (empty($fecha_alta)) {
        $errores[] = "La fecha de alta es obligatoria.";
    } else {
        $hoy      = new DateTime();
        $fechaObj = new DateTime($fecha_alta);
        if ($fechaObj > $hoy) {
            $errores[] = "La fecha de alta no puede ser futura.";
        }
    }

    if (!empty($errores)) {
        $_SESSION['errores']++;
        if ($_SESSION['errores'] >= 3) {
            $mensaje_intentos = "Demasiados intentos fallidos. Revisa los datos con cuidado.";
        }
    } else {
        $n  = $db->escape($nombre);
        $e  = $db->escape($email);
        $s  = $db->escape($salario);
        $fa = $db->escape($fecha_alta);

        $sql = "INSERT INTO empleados (nombre, email, salario, fecha_alta)
                VALUES ('$n', '$e', '$s', '$fa')";
        $db->insert($sql);

        $mensaje_ok          = "Empleado registrado correctamente.";
        $_SESSION['errores'] = 0;
        $_POST               = [];
    }
}

$empleados = $db->select("SELECT * FROM empleados ORDER BY id DESC");

if (isset($_POST['btn_media'])) {

    if (count($empleados) === 0) {
        $estadistica = "No hay empleados registrados.";
    } else {
        $total = 0;
        foreach ($empleados as $emp) {
            $total += $emp['salario'];
        }
        $media       = $total / count($empleados);
        $estadistica = "Salario medio: " . number_format($media, 2) . " euros";
    }

} elseif (isset($_POST['btn_mayor'])) {

    if (count($empleados) === 0) {
        $estadistica = "No hay empleados registrados.";
    } else {
        $mayor = $empleados[0];
        foreach ($empleados as $emp) {
            if ($emp['salario'] > $mayor['salario']) {
                $mayor = $emp;
            }
        }
        $estadistica = "Empleado con mayor salario: " . $mayor['nombre'] . " - " . $mayor['salario'] . " euros";
    }

} elseif (isset($_POST['btn_altas_anio'])) {

    $anio_actual = (new DateTime())->format('Y');
    $contador    = 0;
    foreach ($empleados as $emp) {
        $anio_emp = (new DateTime($emp['fecha_alta']))->format('Y');
        if ($anio_emp === $anio_actual) {
            $contador++;
        }
    }
    $estadistica = "Empleados dados de alta en " . $anio_actual . ": " . $contador;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Empleados</title>
    <style>
        body  { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; }
        .error { color: red; }
        .ok    { color: green; }
        .aviso { color: orange; font-weight: bold; }
        input, button { margin: 4px 0; padding: 6px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
    </style>
</head>
<body>

<h1>Registro de Empleados</h1>

<?php if ($mensaje_intentos): ?>
    <p class="aviso"><?= $mensaje_intentos ?></p>
<?php endif; ?>

<?php if (!empty($errores)): ?>
    <ul class="error">
        <?php foreach ($errores as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($mensaje_ok): ?>
    <p class="ok"><?= $mensaje_ok ?></p>
<?php endif; ?>

<form method="POST" action="">
    <label>Nombre:<br>
        <input type="text" name="nombre" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
    </label><br>

    <label>Email:<br>
        <input type="text" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </label><br>

    <label>Salario:<br>
        <input type="number" name="salario" value="<?= htmlspecialchars($_POST['salario'] ?? '') ?>">
    </label><br>

    <label>Fecha de alta:<br>
        <input type="date" name="fecha_alta" value="<?= htmlspecialchars($_POST['fecha_alta'] ?? '') ?>">
    </label><br><br>

    <button type="submit" name="guardar">Guardar</button>
</form>

<h2>Estadisticas</h2>
<form method="POST" action="">
    <button type="submit" name="btn_media">Salario medio</button>
    <button type="submit" name="btn_mayor">Mayor salario</button>
    <button type="submit" name="btn_altas_anio">Altas este año</button>
</form>

<?php if ($estadistica): ?>
    <p><strong><?= htmlspecialchars($estadistica) ?></strong></p>
<?php endif; ?>

<h2>Lista de Empleados</h2>

<?php if (empty($empleados)): ?>
    <p>No hay empleados registrados.</p>
<?php else: ?>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Salario</th>
            <th>Fecha Alta</th>
        </tr>
        <?php foreach ($empleados as $emp): ?>
        <tr>
            <td><?= $emp['id'] ?></td>
            <td><?= htmlspecialchars($emp['nombre']) ?></td>
            <td><?= htmlspecialchars($emp['email']) ?></td>
            <td><?= number_format($emp['salario'], 2) ?> euros</td>
            <td><?= $emp['fecha_alta'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</body>
</html>
