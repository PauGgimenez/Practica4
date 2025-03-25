<?php
$host = 'localhost';
$dbname = 'shortener_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $original_url = filter_var($_POST['url'], FILTER_SANITIZE_URL);
    if (!filter_var($original_url, FILTER_VALIDATE_URL)) {
        die("URL no válida");
    }
    $short_code = substr(md5(time()), 0, 6);
    $stmt = $pdo->prepare("INSERT INTO urls (short_code, original_url) VALUES (?, ?)");
    $stmt->execute([$short_code, $original_url]);
    echo "URL acortada: <a href='/s.php?c=$short_code'>http://tudominio.com/s.php?c=$short_code</a>";
    exit;
}

if (isset($_GET['c'])) {
    $short_code = $_GET['c'];
    $stmt = $pdo->prepare("SELECT original_url FROM urls WHERE short_code = ?");
    $stmt->execute([$short_code]);
    $url = $stmt->fetchColumn();
    if ($url) {
        header("Location: $url");
        exit;
    } else {
        die("Código no encontrado");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acortador de URLs</title>
</head>
<body>
    <h2>Acortador de URLs</h2>
    <form method="post">
        <input type="url" name="url" required placeholder="Introduce tu URL">
        <button type="submit">Acortar</button>
    </form>
</body>
</html>
