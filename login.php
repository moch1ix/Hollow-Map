<?php
/**
 * Hollow-Map - Tela de Login de Usuários
 * Conforme requisitos.md:
 * - HTML5, Tailwind CSS responsivo (Mobile-First)
 * - Integração com hash BCrypt
 * - Toasts para feedback de erros/sucesso
 */

declare(strict_types=1);

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Entrar - Hollow-Map';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-6">
        
        <!-- Cartão de Login -->
        <div class="bg-abyss-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl">
            <div class="text-center mb-6">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-soul-500 to-indigo-800 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-soul-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold font-hollow text-white">Retornar a Hallownest</h1>
                <p class="text-xs text-slate-400 mt-1">Insira suas credenciais protegidas para acessar sua conta.</p>
            </div>

            <form action="actions/login.php" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">E-mail</label>
                    <input type="email" name="email" required placeholder="seu@email.com"
                           class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-soul-400 transition-colors">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Senha</label>
                        <span class="text-[10px] text-soul-400 font-mono">BCrypt Encriptado</span>
                    </div>
                    <input type="password" name="senha" required placeholder="••••••••"
                           class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-soul-400 transition-colors">
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-soul-500 hover:bg-soul-400 text-white font-semibold text-sm shadow-lg shadow-soul-500/25 transition-all">
                    Conectar
                </button>
            </form>

            <!-- Credenciais de Demonstração Rápidas -->
            <div class="mt-6 pt-5 border-t border-slate-800 text-xs text-slate-400 space-y-2">
                <p class="font-semibold text-slate-300">Contas padrão criadas pelo seeder:</p>
                <div class="bg-abyss-950/80 p-3 rounded-xl border border-slate-800 text-[11px] font-mono space-y-1">
                    <div><span class="text-soul-300">Admin:</span> admin@hollowmap.com / admin123</div>
                    <div><span class="text-slate-400">Usuário:</span> cavaleiro@hollowmap.com / knight123</div>
                </div>
            </div>

            <div class="mt-4 text-center text-xs text-slate-400">
                Ainda não tem conta? 
                <a href="cadastro.php" class="text-soul-400 hover:text-soul-300 font-semibold underline">Cadastre-se aqui</a>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
