<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard | Auditorías</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-white">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="m-0">Bienvenido, <?= esc($nombre) ?></h4>
    <a class="btn btn-outline-secondary btn-sm" href="<?= site_url('logout') ?>">Salir</a>
  </div>
  <div class="alert alert-success">Login ok. Aquí montamos los módulos (Proveedor, Consultor, Admin).</div>
</div>
</body>
</html>
