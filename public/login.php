<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Auth.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth($pdo);
    if ($auth->login($_POST['username'], $_POST['password'])) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}

// Si ya está logueado, ir al dashboard
if (Auth::check()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Finanzas Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #0f172a; color: #f8fafc; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md bg-slate-800 p-8 rounded-2xl shadow-xl">
        <h1 class="text-3xl font-bold mb-2 text-center">Finanzas Pro</h1>
        <p class="text-slate-400 text-center mb-8">Ingresa tus credenciales</p>

        <?php if ($error): ?>
            <div class="bg-red-500/20 text-red-400 p-3 rounded-lg mb-6 text-sm border border-red-500/50">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium mb-2">Usuario</label>
                <input type="text" name="username" required 
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Contraseña</label>
                <input type="password" name="password" required 
                    class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition-colors">
                Entrar
            </button>
        </form>
    </div>
</body>
</html>
