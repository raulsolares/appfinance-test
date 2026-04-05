<?php
// Layout inferior - Navegación Premium
?>
    <!-- Barra de Navegación Premium -->
    <nav class="fixed bottom-6 left-4 right-4 h-20 glass rounded-3xl flex items-center justify-around px-2 z-[1000] border-t border-white/10">
        <a href="index.php" class="flex flex-col items-center justify-center space-y-1 w-12 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'text-blue-400' : 'text-slate-500'; ?>">
            <i data-lucide="layout-grid" class="w-6 h-6"></i>
            <span class="text-[10px] font-medium tracking-wide">Inicio</span>
        </a>
        <a href="reports.php" class="flex flex-col items-center justify-center space-y-1 w-12 <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'text-blue-400' : 'text-slate-500'; ?>">
            <i data-lucide="bar-chart-3" class="w-6 h-6"></i>
            <span class="text-[10px] font-medium tracking-wide">Reportes</span>
        </a>
        
        <!-- Botón Central Flotante -->
        <a href="add_transaction.php" class="flex items-center justify-center w-14 h-14 bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-600/30 -mt-12 border-4 border-slate-950 transition-transform active:scale-95">
            <i data-lucide="plus" class="w-8 h-8 text-white"></i>
        </a>
        
        <a href="settings.php" class="flex flex-col items-center justify-center space-y-1 w-12 <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'text-blue-400' : 'text-slate-500'; ?>">
            <i data-lucide="wallet-cards" class="w-6 h-6"></i>
            <span class="text-[10px] font-medium tracking-wide">Cuentas</span>
        </a>
        <a href="settings.php" class="flex flex-col items-center justify-center space-y-1 w-12 <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'text-blue-400' : 'text-slate-500'; ?>">
            <i data-lucide="settings-2" class="w-6 h-6"></i>
            <span class="text-[10px] font-medium tracking-wide">Ajustes</span>
        </a>
    </nav>

    <script>
        // Inicializar iconos de Lucide
        lucide.createIcons();
    </script>
</body>
</html>
