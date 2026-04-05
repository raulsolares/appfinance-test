<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Finance.php';

if (!Auth::check()) { header('Location: login.php'); exit; }

$finance = new Finance($pdo, Auth::userId());
$message = '';

// Procesar formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_account') {
            $finance->createAccount($_POST['name'], $_POST['type'], $_POST['balance'], $_POST['color']);
            $message = "Cuenta añadida correctamente";
        } elseif ($_POST['action'] === 'add_category') {
            $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type, color) VALUES (?, ?, ?, ?)");
            $stmt->execute([Auth::userId(), $_POST['name'], $_POST['type'], $_POST['color']]);
            $message = "Categoría añadida correctamente";
        }
    }
}

$accounts = $finance->getAccounts();
$categories = $finance->getCategories();

$pageTitle = "Configuración - Finanzas Pro";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style> body { background-color: #0f172a; color: #f8fafc; } .card { background-color: #1e293b; border-radius: 1rem; padding: 1.2rem; margin-bottom: 1.5rem; } </style>
</head>
<body class="p-4 mb-20">
    <header class="mb-8 text-center">
        <h1 class="text-2xl font-bold">Configuración</h1>
    </header>

    <?php if ($message): ?>
        <div class="bg-emerald-600/20 text-emerald-400 p-3 rounded-lg mb-6 border border-emerald-600/50 text-sm text-center"><?php echo $message; ?></div>
    <?php endif; ?>

    <!-- Gestión de Cuentas -->
    <section>
        <h2 class="text-lg font-semibold mb-3 flex items-center">
            <span class="bg-blue-600 w-2 h-6 rounded-full mr-2"></span> Mis Cuentas
        </h2>
        <div class="space-y-3 mb-4">
            <?php foreach ($accounts as $acc): ?>
                <div class="card flex justify-between items-center !mb-0" style="border-left: 4px solid <?php echo $acc['color']; ?>">
                    <span><?php echo htmlspecialchars($acc['name']); ?></span>
                    <span class="text-xs text-slate-400 uppercase bg-slate-800 px-2 py-1 rounded"><?php echo $acc['type']; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <button onclick="document.getElementById('modalAccount').classList.remove('hidden')" class="w-full bg-slate-800 hover:bg-slate-700 text-blue-400 py-3 rounded-xl border border-dashed border-slate-600 mb-8">
            + Añadir Cuenta
        </button>
    </section>

    <!-- Gestión de Categorías -->
    <section>
        <h2 class="text-lg font-semibold mb-3 flex items-center">
            <span class="bg-emerald-600 w-2 h-6 rounded-full mr-2"></span> Categorías
        </h2>
        <div class="grid grid-cols-2 gap-3 mb-4">
            <?php foreach ($categories as $cat): ?>
                <div class="bg-slate-800 p-3 rounded-lg flex items-center">
                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: <?php echo $cat['color']; ?>"></div>
                    <span class="text-sm truncate"><?php echo htmlspecialchars($cat['name']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <button onclick="document.getElementById('modalCategory').classList.remove('hidden')" class="w-full bg-slate-800 hover:bg-slate-700 text-emerald-400 py-3 rounded-xl border border-dashed border-slate-600">
            + Añadir Categoría
        </button>
    </section>

    <!-- Modales (Simplificados para móvil) -->
    <div id="modalAccount" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-[100]">
        <div class="bg-slate-900 w-full max-w-sm rounded-2xl p-6 border border-slate-800">
            <h3 class="text-xl font-bold mb-4 text-blue-400">Nueva Cuenta</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_account">
                <input type="text" name="name" placeholder="Nombre (Ej. Mi Tarjeta)" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3">
                <select name="type" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3">
                    <option value="bank">Banco</option>
                    <option value="cash">Efectivo</option>
                    <option value="credit">Crédito</option>
                </select>
                <input type="number" name="balance" step="0.01" placeholder="Saldo Inicial" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3">
                <input type="color" name="color" value="#3b82f6" class="w-full h-10 rounded-lg">
                <div class="flex gap-3">
                    <button type="button" onclick="this.closest('#modalAccount').classList.add('hidden')" class="flex-1 py-3 text-slate-400">Cancelar</button>
                    <button type="submit" class="flex-1 bg-blue-600 py-3 rounded-xl font-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalCategory" class="hidden fixed inset-0 bg-black/80 flex items-center justify-center p-4 z-[100]">
        <div class="bg-slate-900 w-full max-w-sm rounded-2xl p-6 border border-slate-800">
            <h3 class="text-xl font-bold mb-4 text-emerald-400">Nueva Categoría</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_category">
                <input type="text" name="name" placeholder="Nombre (Ej. Gimnasio)" required class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3">
                <select name="type" class="w-full bg-slate-800 border border-slate-700 rounded-lg p-3">
                    <option value="expense">Gasto</option>
                    <option value="income">Ingreso</option>
                </select>
                <input type="color" name="color" value="#10b981" class="w-full h-10 rounded-lg">
                <div class="flex gap-3">
                    <button type="button" onclick="this.closest('#modalCategory').classList.add('hidden')" class="flex-1 py-3 text-slate-400">Cancelar</button>
                    <button type="submit" class="flex-1 bg-emerald-600 py-3 rounded-xl font-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Barra de Navegación Inferior -->
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-900 border-t border-slate-800 flex justify-around p-4 z-50">
        <a href="index.php" class="text-slate-400 text-center">
            <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs">Inicio</span>
        </a>
        <a href="add_transaction.php" class="bg-blue-600 rounded-full w-12 h-12 -mt-10 shadow-lg flex items-center justify-center text-white text-2xl font-bold">
            +
        </a>
        <a href="reports.php" class="text-slate-400 text-center">
            <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <span class="text-xs">Reportes</span>
        </a>
        <a href="settings.php" class="text-blue-400 text-center">
            <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <span class="text-xs">Ajustes</span>
        </a>
    </nav>
</body>
</html>
