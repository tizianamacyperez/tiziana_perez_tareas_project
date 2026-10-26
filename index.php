
<?php
require_once __DIR__ . '/conexion.php';

$errores = [];
$mensaje = '';

$nombre = '';
$apellido = '';
$dni = '';
$email = '';

$dbname = 'instituto_sagrado_corazon';
$usuario = 'root';
$password = '';

function escapar(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $usuario,
        $password
    );

    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $conexion->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if ($apellido === '') {
        $errores[] = 'El apellido es obligatorio.';
    }

    if (!preg_match('/^[0-9]{7,8}$/', $dni)) {
        $errores[] = 'El DNI debe tener 7 u 8 números.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo válido.';
    }

    if (empty($errores)) {
        try {
            $sql = 'INSERT INTO estudiantes
                    (nombre, apellido, dni, email)
                    VALUES
                    (:nombre, :apellido, :dni, :email)';

            $stmt = $conexion->prepare($sql);

            $stmt->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'dni' => $dni,
                'email' => $email
            ]);

            $mensaje = 'Estudiante registrado correctamente.';

            $nombre = '';
            $apellido = '';
            $dni = '';
            $email = '';

        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errores[] = 'Ese DNI ya está registrado.';
            } else {
                error_log($e->getMessage());
                $errores[] = 'Ocurrió un error al guardar los datos.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de estudiantes</title>
</head>
<body>

    <h1>Registrar estudiante</h1>

    
<?php if ($mensaje !== ''): ?>
    <p><?= escapar($mensaje) ?></p>
<?php endif; ?>

<?php if (!empty($errores)): ?>
    <ul>
        <?php foreach ($errores as $error): ?>
            <li><?= escapar($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>


    <?php if ($mensaje !== ''): ?>
        <p><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <label for="nombre">Nombre:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            maxlength="80"
            value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <br>

        <label for="apellido">Apellido:</label>
        <input
            type="text"
            id="apellido"
            name="apellido"
            maxlength="80"
            value="<?= htmlspecialchars($apellido, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <br>

        <label for="dni">DNI:</label>
        <input
            type="text"
            id="dni"
            name="dni"
            inputmode="numeric"
            pattern="[0-9]{7,8}"
            maxlength="8"
            value="<?= htmlspecialchars($dni, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <br>

        <label for="email">Correo electrónico:</label>
        <input
            type="email"
            id="email"
            name="email"
            maxlength="254"
            value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
            required
        >

        <br>

        <button type="submit">Registrar estudiante</button>
    </form>

</body>
</html>
