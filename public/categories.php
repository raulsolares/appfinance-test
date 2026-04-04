<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Finance.php';

if (!Auth::check()) { header('Location: login.php'); exit; }

$finance = new Finance($pdo, Auth::userId());
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $color = $_POST['color'];
    
    $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type, color) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([Auth::userId(), $name, $type, $color])) {
        $message = "Categoría creada";
    }
}

$categories = $finance->getCategories();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - Finanzas Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style> body { background-color: #0f172a; color: #f8fafc; } </style>
</head>
<body class="p-6">
    <div class="flex items-center mb-6">
        <a href="index.php" class="text-blue-400 mr-4 text-xl">&larr;</a>
        <h1 class="text-2xl font-bold">Mis Categorías</h1>
    </div>

    <form method="POST" class="bg-slate-800 p-6 rounded-2xl mb-8 space-y-4">
        <h2 class="font-semibold">Nueva Categoría</h2>
        <input type="text" name="name" placeholder="Nombre (Ej. Gimnasio)" required
            class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3">
        
        <div class="flex gap-4">
            <select name="type" class="flex-1 bg-slate-900 border border-slate-700 rounded-lg p-3">
                <option value="expense">Gasto</option>
                <option value="income">Ingreso</option>
            </select>
            <input type="color" name="color" value="#3b82f6" class="w-16 h-12 bg-transparent border-none">
        </div>

        <button type="submit" class="w-full bg-blue-600 py-3 rounded-xl font-bold">Guardar</button>
    </form>

    <div class="space-y-3">
        <?php foreach($categories as $cat): ?>
            <div class="bg-slate-800 p-4 rounded-xl flex justify-between items-center border-l-4" style="border-color: <?php echo $cat['color']; ?>">
                <span><?php echo htmlspecialchars($cat['name']); ?></span>
                <span class="text-xs uppercase px-2 py-1 rounded <?php echo $cat['type'] === 'income' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-red-500/20 text-red-400'; ?>">
                    <?php echo $cat['type']; ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
