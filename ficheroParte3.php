<?php
session_start();

if (!isset($_SESSION['colas'])) {
    $_SESSION['colas'] = [
        'EFECTIVO' => [],
        'IMPORTE_EXACTO' => [],
        'TARJETA' => []
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $coche = $_POST['coche'] ?? '';

    if ($accion === 'agregar' && $coche && isset($_SESSION['colas'][$tipo])) {
        $_SESSION['colas'][$tipo][] = $coche;
    } elseif ($accion === 'eliminar' && isset($_SESSION['colas'][$tipo]) && count($_SESSION['colas'][$tipo]) > 0) {
        array_shift($_SESSION['colas'][$tipo]);
    }
    exit(json_encode($_SESSION['colas']));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colas de Pago en Autopista</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="container py-5">
    <h2 class="text-center">Gestión de Colas en la Autopista</h2>
    <div class="row text-center my-3">
        <div class="col">
            <input type="text" id="coche" class="form-control" placeholder="Matrícula del coche">
        </div>
        <div class="col">
            <select id="tipo" class="form-select">
                <option value="EFECTIVO">Efectivo</option>
                <option value="IMPORTE_EXACTO">Importe Exacto</option>
                <option value="TARJETA">Tarjeta</option>
            </select>
        </div>
        <div class="col">
            <button class="btn btn-primary" onclick="gestionarCola('agregar')">Agregar</button>
            <button class="btn btn-danger" onclick="gestionarCola('eliminar')">Eliminar</button>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <h4>EFECTIVO</h4>
            <ul id="EFECTIVO" class="list-group"></ul>
        </div>
        <div class="col-md-4">
            <h4>IMPORTE EXACTO</h4>
            <ul id="IMPORTE_EXACTO" class="list-group"></ul>
        </div>
        <div class="col-md-4">
            <h4>TARJETA</h4>
            <ul id="TARJETA" class="list-group"></ul>
        </div>
    </div>
    <script>
        function gestionarCola(accion) {
            $.post("", {
                accion: accion,
                tipo: $("#tipo").val(),
                coche: $("#coche").val()
            }, function (data) {
                actualizarColas(JSON.parse(data));
            });
        }
        function actualizarColas(colas) {
            ["EFECTIVO", "IMPORTE_EXACTO", "TARJETA"].forEach(tipo => {
                let lista = $("#" + tipo).empty();
                colas[tipo].forEach(coche => lista.append("<li class='list-group-item'>" + coche + "</li>"));
            });
        }
        $(document).ready(() => gestionarCola('actualizar'));
    </script>
</body>
</html>
