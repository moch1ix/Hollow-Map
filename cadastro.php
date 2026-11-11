<?php
/**
 * Hollow-Map - Tela de Cadastro de Usuário
 * Conforme requisitos.md:
 * - Tratar senhas de usuários com hash BCrypt
 * - HTML5, Tailwind CSS responsivo (Mobile-First)
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

$pageTitle = 'Criar Conta - Hollow-Map';
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-6">
        
        <div class="bg-abyss-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl">
            <div class="text-center mb-6">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-soul-400 to-indigo-700 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-soul-500/30">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold font-hollow text-white">Criar Nova Jornada</h1>
                <p class="text-xs text-slate-400 mt-1">Junte-se à guilda de mapeamento de Hallownest &amp; Pharloom.</p>
            </div>

            <form action="actions/register.php" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Nome do Explorador</label>
                    <input type="text" name="nome" required placeholder="Ex: Quirrel, Hornet, O Cavaleiro..."
                           class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-soul-400 transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">E-mail</label>
                    <input type="email" name="email" required placeholder="seu@email.com"
                           class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-soul-400 transition-colors">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Senha (Mín. 6 caracteres)</label>
                        <span class="text-[10px] text-soul-400 font-mono">BCrypt Seguro</span>
                    </div>
                    <input type="password" name="senha" required minlength="6" placeholder="••••••••"
                           class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-soul-400 transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Confirmar Senha</label>
                    <input type="password" name="confirmar_senha" required minlength="6" placeholder="••••••••"
                           class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-soul-400 transition-colors">
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-soul-500 hover:bg-soul-400 text-white font-semibold text-sm shadow-lg shadow-soul-500/25 transition-all">
                    Registrar Conta
                </button>
            </form>

            <div class="mt-4 text-center text-xs text-slate-400">
                Já possui uma conta? 
                <a href="login.php" class="text-soul-400 hover:text-soul-300 font-semibold underline">Entrar aqui</a>
            </div>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
