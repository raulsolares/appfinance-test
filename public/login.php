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
        $error = 'Credenciales no válidas';
    }
}

if (Auth::check()) { header('Location: index.php'); exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - Finanzas Pro Premium</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background: radial-gradient(circle at center, #1e1b4b, #020617);
            min-height: 100vh;
        }
        .glass { 
            background: rgba(30, 41, 59, 0.4); 
            backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
    </style>
</head>
<body class="flex items-center justify-center p-6">
    <div class="w-full max-w-sm glass rounded-[40px] p-10 shadow-2xl relative overflow-hidden">
        <!-- Decoración -->
        <div class="absolute -top-20 -right-20 w-40 h-40 bg-indigo-600/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-emerald-600/10 rounded-full blur-3xl"></div>

        <div class="relative z-10">
            <div class="flex flex-col items-center mb-10">
                <div class="w-16 h-16 bg-gradient-to-tr from-indigo-600 to-emerald-500 rounded-2xl flex items-center justify-center mb-4 shadow-xl shadow-indigo-600/20">
                    <i data-lucide="shield-check" class="w-8 h-8 text-white"></i>
                </div>
                <h1 class="text-3xl font-black tracking-tighter text-white">Finanzas Pro</h1>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.3em] mt-1">Versión Premium</p>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-4 rounded-2xl mb-8 text-xs font-bold text-center animate-shake">
                    <?php echo strtoupper($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Usuario</label>
                    <input type="text" name="username" required placeholder="tu_usuario"
                        class="w-full bg-slate-900/50 border border-white/5 rounded-2xl p-4 text-sm font-bold text-white focus:ring-2 ring-indigo-500 outline-none transition-all placeholder:text-slate-800">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest ml-1">Contraseña</label>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full bg-slate-900/50 border border-white/5 rounded-2xl p-4 text-sm font-bold text-white focus:ring-2 ring-indigo-500 outline-none transition-all placeholder:text-slate-800">
                </div>
                <button type="submit" 
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-5 rounded-2xl transition-all shadow-xl shadow-indigo-600/30 text-xs uppercase tracking-widest mt-4">
                    Iniciar Sesión
                </button>
            </form>
            
            <p class="text-center text-[10px] font-bold text-slate-600 uppercase tracking-widest mt-10">
                &copy; <?php echo date('Y'); ?> PROYECTO FINANZAS
            </p>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
