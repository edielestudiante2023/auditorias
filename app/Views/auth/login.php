<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Ingreso | Sistema de Gestión de Auditorías - Cycloid Talent</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/x-icon" href="/auditorias/assets/images/brand/favicon.ico">
  <link rel="shortcut icon" type="image/x-icon" href="/auditorias/assets/images/brand/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }

    body {
      background: linear-gradient(135deg, #667eea, #764ba2, #f093fb, #4facfe);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
      min-height: 100vh;
      display: flex;
      align-items: center;
      position: relative;
      overflow: hidden;
    }

    /* Partículas flotantes en el fondo */
    body::before {
      content: '';
      position: absolute;
      width: 200%;
      height: 200%;
      background-image:
        radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px),
        radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
      background-size: 50px 50px, 80px 80px;
      background-position: 0 0, 40px 40px;
      animation: float 20s linear infinite;
    }

    .login-card {
      border-radius: 15px;
      border: none;
      animation: fadeInUp 0.6s ease-out;
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95) !important;
    }

    .logo-container {
      text-align: center;
      margin-bottom: 30px;
      animation: fadeInUp 0.8s ease-out;
    }

    .logo-container img {
      max-width: 280px;
      height: auto;
      filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
      animation: float 3s ease-in-out infinite;
    }

    .btn-login {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      padding: 12px;
      font-weight: 500;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .btn-login::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s;
    }

    .btn-login:hover::before {
      left: 100%;
    }

    .btn-login:hover {
      background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .brand-footer {
      color: white;
      font-weight: 500;
      text-shadow: 0 2px 4px rgba(0,0,0,0.2);
      animation: fadeInUp 1s ease-out;
    }

    .form-control {
      border: 2px solid #e0e0e0;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
      transform: translateY(-2px);
    }

    .alert {
      animation: fadeInUp 0.4s ease-out;
    }

    h5 {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      animation: pulse 2s ease-in-out infinite;
    }
  </style>
</head>
<body>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-sm-8 col-md-5">
      <div class="logo-container">
        <img src="<?= base_url('assets/images/brand/cycloidfblanco.png') ?>" alt="Cycloid Talent">
      </div>
      <div class="card shadow-lg login-card">
        <div class="card-body p-4">
          <h5 class="mb-4 text-center fw-bold">Sistema de Gestión de Auditorías</h5>

          <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
          <?php endif; ?>

          <form method="post" action="<?= site_url('login') ?>">

            <?= csrf_field() ?>
            <div class="mb-3">
              <label class="form-label">Correo</label>
              <input type="email" name="email" class="form-control" required value="<?= old('email') ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Contraseña</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-login w-100" type="submit">Ingresar</button>
          </form>
        </div>
      </div>
      <p class="text-center brand-footer mt-4">
        <strong>Cycloid Talent SAS</strong><br>
        <small>Sistema de Gestión de Seguridad y Salud en el Trabajo</small>
      </p>
    </div>
  </div>
</div>
</body>
</html>
