<?php
/**
 * Hollow-Map - Página de Histórico e Auditoria
 * Conforme requisitos.md (CORE):
 * "O sistema deve ter uma página de históricos e manter sempre os logs de
 *  qualquer alteração feita por qualquer usuário, para auditorias futuras."
 */

declare(strict_types=1);

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/logs.php';

$pageTitle = 'Histórico & Auditoria - Hollow-Map';
$activePage = 'historico';

$filtros = [
    'acao'        => $_GET['acao'] ?? '',
    'usuario'     => $_GET['usuario'] ?? '',
    'tabela'      => $_GET['tabela'] ?? '',
    'data_inicio' => $_GET['data_inicio'] ?? '',
    'data_fim'    => $_GET['data_fim'] ?? ''
];

$pagina = max(1, (int)($_GET['p'] ?? 1));
$limite = 25;
$offset = ($pagina - 1) * $limite;

$historico = ['total' => 0, 'itens' => []];
$dbError = null;

try {
    $pdo = getDB();
    $historico = buscar_historico($pdo, $filtros, $limite, $offset);
} catch (Exception $e) {
    $dbError = $e->getMessage();
}

$totalPaginas = max(1, (int)ceil($historico['total'] / $limite));

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">

    <!-- Cabeçalho da Página -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-soul-500/10 text-soul-400 border border-soul-500/30">
                    Regra CORE • Auditoria do Sistema
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-hollow text-white">
                Histórico de Alterações
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Rastreamento e auditoria permanente de todas as criações, edições, exclusões e acessos efetuados no sistema.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-4 py-2 bg-abyss-900 border border-slate-800 rounded-xl text-xs text-slate-300 font-mono">
                Total Registros: <strong class="text-soul-300 font-bold"><?= $historico['total'] ?></strong>
            </span>
        </div>
    </div>

    <?php if ($dbError): ?>
        <div class="p-4 mb-6 rounded-2xl bg-rose-950/80 border border-rose-600/50 text-rose-200 text-sm">
            <strong>Falha na Conexão:</strong> <?= e($dbError) ?>
        </div>
    <?php endif; ?>

    <!-- Formulário de Filtros de Auditoria (MobileFirst) -->
    <section class="bg-abyss-900/90 border border-slate-800 rounded-2xl p-5 mb-6 backdrop-blur-md shadow-xl">
        <form method="GET" action="historico.php" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
            
            <!-- Filtro de Ação -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Tipo de Ação</label>
                <select name="acao" class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-soul-400">
                    <option value="">Todas as Ações</option>
                    <option value="CRIAR_MARCADOR" <?= $filtros['acao'] === 'CRIAR_MARCADOR' ? 'selected' : '' ?>>Criar Marcador</option>
                    <option value="EDITAR_MARCADOR" <?= $filtros['acao'] === 'EDITAR_MARCADOR' ? 'selected' : '' ?>>Editar Marcador</option>
                    <option value="EXCLUIR_MARCADOR" <?= $filtros['acao'] === 'EXCLUIR_MARCADOR' ? 'selected' : '' ?>>Excluir Marcador</option>
                    <option value="LOGIN" <?= $filtros['acao'] === 'LOGIN' ? 'selected' : '' ?>>Login</option>
                    <option value="LOGOUT" <?= $filtros['acao'] === 'LOGOUT' ? 'selected' : '' ?>>Logout</option>
                    <option value="CADASTRO_USUARIO" <?= $filtros['acao'] === 'CADASTRO_USUARIO' ? 'selected' : '' ?>>Cadastro de Usuário</option>
                </select>
            </div>

            <!-- Filtro de Usuário -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Usuário / E-mail</label>
                <input type="text" name="usuario" value="<?= e($filtros['usuario']) ?>" placeholder="Nome ou e-mail..."
                       class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-soul-400">
            </div>

            <!-- Data Início -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">A partir de</label>
                <input type="date" name="data_inicio" value="<?= e($filtros['data_inicio']) ?>"
                       class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-soul-400">
            </div>

            <!-- Data Fim -->
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Até a data</label>
                <input type="date" name="data_fim" value="<?= e($filtros['data_fim']) ?>"
                       class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-soul-400">
            </div>

            <!-- Botões de Ação -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 text-xs font-semibold rounded-xl bg-soul-500 hover:bg-soul-400 text-white shadow-md transition-all">
                    Filtrar
                </button>
                <a href="historico.php" class="py-2 px-3 text-xs font-semibold rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                    Limpar
                </a>
            </div>
        </form>
    </section>

    <!-- Tabela / Lista de Registros de Histórico -->
    <div class="bg-abyss-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        
        <?php if (empty($historico['itens'])): ?>
            <div class="text-center py-16 px-4">
                <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center mx-auto text-slate-500 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-200">Nenhum registro encontrado</h3>
                <p class="text-xs text-slate-400 mt-1">Ajuste os filtros de busca para encontrar as alterações registradas.</p>
            </div>
        <?php else: ?>

            <!-- Visualização Desktop (Tabela estruturada) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 bg-abyss-950/60 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="p-4">Data / Hora</th>
                            <th class="p-4">Ação</th>
                            <th class="p-4">Usuário</th>
                            <th class="p-4">Tabela Afetada</th>
                            <th class="p-4">IP</th>
                            <th class="p-4 text-right">Detalhes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php foreach ($historico['itens'] as $log): ?>
                            <?php
                                $badgeAcao = match ($log['acao']) {
                                    'CRIAR_MARCADOR'   => 'bg-emerald-950/70 text-emerald-400 border border-emerald-500/30',
                                    'EDITAR_MARCADOR'  => 'bg-amber-950/70 text-amber-400 border border-amber-500/30',
                                    'EXCLUIR_MARCADOR' => 'bg-rose-950/70 text-rose-400 border border-rose-500/30',
                                    'LOGIN'            => 'bg-sky-950/70 text-sky-400 border border-sky-500/30',
                                    'LOGOUT'           => 'bg-slate-800 text-slate-400 border border-slate-700',
                                    'CADASTRO_USUARIO' => 'bg-purple-950/70 text-purple-400 border border-purple-500/30',
                                    default            => 'bg-slate-800 text-slate-300 border border-slate-700'
                                };
                            ?>
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="p-4 font-mono text-slate-400 whitespace-nowrap">
                                    <?= date('d/m/Y H:i:s', strtotime($log['criado_em'])) ?>
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-semibold <?= $badgeAcao ?>">
                                        <?= e($log['acao']) ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="font-medium text-slate-200"><?= e($log['usuario_nome'] ?? 'Sistema') ?></div>
                                    <?php if (!empty($log['usuario_email'])): ?>
                                        <div class="text-[10px] text-slate-500"><?= e($log['usuario_email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-slate-300 font-mono">
                                    <?= e($log['tabela']) ?> <?= $log['registro_id'] ? '#' . (int)$log['registro_id'] : '' ?>
                                </td>
                                <td class="p-4 font-mono text-slate-400">
                                    <?= e($log['ip_address']) ?>
                                </td>
                                <td class="p-4 text-right">
                                    <button type="button" 
                                            onclick='verDetalhesLog(<?= json_encode($log, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-soul-300 hover:text-white transition-colors">
                                        Inspecionar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Visualização Mobile (Cards Responsivos - MobileFirst) -->
            <div class="md:hidden divide-y divide-slate-800">
                <?php foreach ($historico['itens'] as $log): ?>
                    <div class="p-4 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-mono text-slate-400">
                                <?= date('d/m/Y H:i', strtotime($log['criado_em'])) ?>
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-soul-300">
                                <?= e($log['acao']) ?>
                            </span>
                        </div>
                        <div class="text-sm font-semibold text-white">
                            <?= e($log['usuario_nome'] ?? 'Sistema') ?>
                        </div>
                        <div class="text-xs text-slate-400 flex items-center justify-between">
                            <span>Tabela: <?= e($log['tabela']) ?> <?= $log['registro_id'] ? '#' . (int)$log['registro_id'] : '' ?></span>
                            <span class="font-mono text-[10px]"><?= e($log['ip_address']) ?></span>
                        </div>
                        <button type="button" 
                                onclick='verDetalhesLog(<?= json_encode($log, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                class="w-full py-1.5 text-xs text-center font-medium bg-slate-800 hover:bg-slate-700 rounded-lg text-soul-300 transition-colors">
                            Ver Detalhes do Log
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <!-- Paginação -->
        <?php if ($totalPaginas > 1): ?>
            <div class="p-4 border-t border-slate-800 bg-abyss-950/60 flex items-center justify-between text-xs">
                <span class="text-slate-400">
                    Página <?= $pagina ?> de <?= $totalPaginas ?>
                </span>
                <div class="flex items-center gap-1.5">
                    <?php if ($pagina > 1): ?>
                        <a href="?<?= http_build_query(array_merge($filtros, ['p' => $pagina - 1])) ?>" 
                           class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200">
                            Anterior
                        </a>
                    <?php endif; ?>
                    <?php if ($pagina < $totalPaginas): ?>
                        <a href="?<?= http_build_query(array_merge($filtros, ['p' => $pagina + 1])) ?>" 
                           class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200">
                            Próxima
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

</main>

<script>
    function verDetalhesLog(log) {
        let formattedDetails = log.detalhes;
        try {
            const parsed = JSON.parse(log.detalhes);
            formattedDetails = `<pre class="bg-abyss-950 p-3 rounded-xl border border-slate-800 text-xs text-soul-300 font-mono overflow-x-auto max-h-60 whitespace-pre-wrap">${JSON.stringify(parsed, null, 2)}</pre>`;
        } catch (e) {
            formattedDetails = `<p class="bg-abyss-950 p-3 rounded-xl border border-slate-800 text-xs text-slate-200">${log.detalhes || 'Sem detalhes adicionais.'}</p>`;
        }

        const msgHtml = `
            <div class="space-y-3 text-left">
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div><strong class="text-slate-400">Ação:</strong> <span class="text-soul-300 font-semibold">${log.acao}</span></div>
                    <div><strong class="text-slate-400">Data/Hora:</strong> <span class="text-slate-200">${log.criado_em}</span></div>
                    <div><strong class="text-slate-400">Usuário:</strong> <span class="text-slate-200">${log.usuario_nome || 'N/A'}</span></div>
                    <div><strong class="text-slate-400">IP:</strong> <span class="font-mono text-slate-300">${log.ip_address}</span></div>
                </div>
                <div>
                    <strong class="block text-xs text-slate-400 mb-1">Payload / Alterações:</strong>
                    ${formattedDetails}
                </div>
            </div>
        `;

        window.showCustomModal({
            title: `Auditoria - Log #${log.id}`,
            message: msgHtml,
            type: 'info',
            isConfirm: false,
            confirmText: 'Fechar'
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
