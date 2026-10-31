<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada - GQ-Turismo</title>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/GQ-Turismo/assets/css/gq-design-tokens.css">
    <style>
        body {
            background-color: var(--c-selva-50, #f0f7f2);
            font-family: var(--font-sans, 'Inter', sans-serif);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: var(--space-md, 1rem);
            color: var(--gray-800, #1f2937);
        }
        .error-card {
            background: #ffffff;
            border-radius: var(--radius-lg, 16px);
            padding: var(--space-2xl, 3rem) var(--space-xl, 2rem);
            max-width: 540px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--gray-200, #e5e7eb);
        }
        .error-icon {
            font-size: 4rem;
            margin-bottom: var(--space-md, 1rem);
            display: inline-block;
        }
        .error-title {
            font-family: var(--font-display, 'Fraunces', serif);
            font-size: 2rem;
            color: var(--c-selva-900, #0a2f18);
            margin-bottom: var(--space-sm, 0.5rem);
        }
        .error-desc {
            color: var(--gray-600, #4b5563);
            margin-bottom: var(--space-xl, 2rem);
            line-height: 1.6;
        }
        .btn-home {
            display: inline-block;
            background: var(--c-selva-600, #1b5e20);
            color: #ffffff;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius-md, 8px);
            font-weight: 600;
            transition: background-color 0.2s ease;
        }
        .btn-home:hover {
            background: var(--c-selva-700, #144618);
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">🧭🔍</div>
        <h1 class="error-title">Destino no encontrado (404)</h1>
        <p class="error-desc">
            La página o recurso que buscas no existe o ha sido trasladada a otro itinerario.
        </p>
        <a href="/GQ-Turismo/index.php" class="btn-home">Explorar Destinos</a>
    </div>
</body>
</html>
