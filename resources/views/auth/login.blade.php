<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESORTIC – Iniciar sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f4ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .login-header {
            background: #0d6efd;
            color: white;
            border-radius: 16px 16px 0 0;
            padding: 32px 32px 24px;
            text-align: center;
        }
        .login-header h2 { font-weight: 700; letter-spacing: 2px; margin-bottom: 4px; }
        .login-header p  { opacity: 0.85; font-size: 0.875rem; margin: 0; }
        .login-body { padding: 32px; }
    </style>
</head>
<body>

<div class="login-card card">
    <div class="login-header">
        <div class="mb-2"><i class="bi bi-building fs-2"></i></div>
        <h2>RESORTIC</h2>
        <p>Plataforma de gestión hotelera</p>
    </div>
    <div class="login-body">

        {{-- Alerta de éxito (ej. después de cerrar sesión) --}}
        @if(session('success'))
            <div class="alert alert-success py-2 small">
                <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Error de credenciales --}}
        @if($errors->has('email'))
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first('email') }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" novalidate>
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold small">Correo electrónico</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="correo@ejemplo.com"
                        autofocus
                        required
                    >
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold small">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••"
                        required
                    >
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar sesión
            </button>
        </form>

        <hr class="my-3">
        <p class="text-muted small text-center mb-1"><i class="bi bi-info-circle me-1"></i>Credenciales de prueba:</p>
        <p class="text-muted small text-center mb-0">
            <code>admin@resortic.com</code> / <code>admin123</code>
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
