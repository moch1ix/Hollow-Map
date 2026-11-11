<?php
/**
 * Hollow-Map - Lista e Gerenciamento de Marcadores
 * Conforme requisitos.md:
 * - HTML5, PHP, Tailwind CSS (Mobile-First)
 * - Exclusão e edição com modais customizados
 * - PDO para consultas e filtros
 */

declare(strict_types=1);

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Marcadores - Hollow-Map';
$activePage = 'marcadores';
$jogoSlugAtual = $_GET['jogo'] ?? 'hollow-knight';

$categoriaFiltro = !empty($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$busca = trim($_GET['busca'] ?? '');

$marcadores = [];
$categoriasDisponiveis = [];
$regioesDisponiveis = [];
$dbError = null;

try {
    $pdo = getDB();

    // Categorias para filtro
    $stmtCat = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
    $categoriasDisponiveis = $stmtCat->fetchAll();

    // Regiões do jogo ativo
    $stmtReg = $pdo->prepare("SELECT r.* FROM regioes r JOIN jogos j ON r.jogo_id = j.id WHERE j.slug = :slug ORDER BY r.nome ASC");
    $stmtReg->execute([':slug' => $jogoSlugAtual]);
    $regioesDisponiveis = $stmtReg->fetchAll();

    // Query de marcadores com filtros e PDO
    $conditions = ["j.slug = :slug"];
    $params = [':slug' => $jogoSlugAtual];

    if ($categoriaFiltro) {
        $conditions[] = "m.categoria_id = :categoria_id";
        $params[':categoria_id'] = $categoriaFiltro;
    }

    if (!empty($busca)) {
        $conditions[] = "(m.titulo LIKE :busca OR m.descricao LIKE :busca_desc)";
        $params[':busca'] = '%' . $busca . '%';
        $params[':busca_desc'] = '%' . $busca . '%';
    }

    $where = 'WHERE ' . implode(' AND ', $conditions);

    $sql = "SELECT 
                m.*,
                c.nome as categoria_nome, c.cor as categoria_cor,
                r.nome as regiao_nome,
                u.nome as usuario_nome,
                j.nome as jogo_nome, j.slug as jogo_slug
            FROM marcadores m
            JOIN jogos j ON m.jogo_id = j.id
            JOIN categorias c ON m.categoria_id = c.id
            LEFT JOIN regioes r ON m.regiao_id = r.id
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            $where
            ORDER BY m.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $marcadores = $stmt->fetchAll();

} catch (Exception $e) {
    $dbError = $e->getMessage();
}

$currentUser = current_user();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="flex-1 py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">

    <!-- Cabeçalho -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold font-hollow text-white">
                Lista de Marcadores
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                Explore os itens, amuletos, chefes e segredos catalogados para <?= $jogoSlugAtual === 'silksong' ? 'Silksong' : 'Hollow Knight' ?>.
            </p>
        </div>

        <div>
            <a href="index.php?jogo=<?= e($jogoSlugAtual) ?>" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-xl bg-soul-500 hover:bg-soul-400 text-white shadow-lg transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                Ver no Mapa Interativo
            </a>
        </div>
    </div>

    <?php if ($dbError): ?>
        <div class="p-4 mb-6 rounded-2xl bg-rose-950/80 border border-rose-600/50 text-rose-200 text-sm">
            <?= e($dbError) ?>
        </div>
    <?php endif; ?>

    <!-- Barra de Filtros (MobileFirst) -->
    <section class="bg-abyss-900/90 border border-slate-800 rounded-2xl p-4 mb-6 backdrop-blur-md shadow-xl">
        <form method="GET" action="marcadores.php" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <input type="hidden" name="jogo" value="<?= e($jogoSlugAtual) ?>">

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Buscar por Nome / Descrição</label>
                <input type="text" name="busca" value="<?= e($busca) ?>" placeholder="Ex: Manto da Mariposa, Sly, Louva-a-Deus..."
                       class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-soul-400">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Filtrar Categoria</label>
                <select name="categoria" class="w-full bg-abyss-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-soul-400">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categoriasDisponiveis as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= $categoriaFiltro === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 text-xs font-semibold rounded-xl bg-soul-500 hover:bg-soul-400 text-white transition-all">
                    Filtrar
                </button>
                <a href="marcadores.php?jogo=<?= e($jogoSlugAtual) ?>" class="py-2 px-4 text-xs font-semibold rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors">
                    Limpar
                </a>
            </div>
        </form>
    </section>

    <!-- Lista de Marcadores -->
    <div class="bg-abyss-900 border border-slate-800 rounded-2xl overflow-hidden shadow-2xl">
        <?php if (empty($marcadores)): ?>
            <div class="text-center py-16 px-4">
                <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center mx-auto text-slate-500 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-200">Nenhum marcador encontrado</h3>
                <p class="text-xs text-slate-400 mt-1">Nenhum item corresponde aos critérios de pesquisa para este mapa.</p>
            </div>
        <?php else: ?>

            <!-- Desktop: Tabela estruturada -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 bg-abyss-950/60 text-slate-400 uppercase tracking-wider font-semibold">
                            <th class="p-4">Título</th>
                            <th class="p-4">Categoria</th>
                            <th class="p-4">Região</th>
                            <th class="p-4">Dificuldade</th>
                            <th class="p-4">Coordenadas</th>
                            <th class="p-4">Autor</th>
                            <th class="p-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <?php foreach ($marcadores as $m): ?>
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-slate-100 text-sm"><?= e($m['titulo']) ?></div>
                                    <div class="text-[11px] text-slate-400 line-clamp-1 mt-0.5"><?= e($m['descricao']) ?></div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold border"
                                          style="color: <?= e($m['categoria_cor']) ?>; border-color: <?= e($m['categoria_cor']) ?>44; background-color: <?= e($m['categoria_cor']) ?>1a;">
                                        <span class="w-1.5 h-1.5 rounded-full" style="background-color: <?= e($m['categoria_cor']) ?>;"></span>
                                        <?= e($m['categoria_nome']) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-slate-300">
                                    <?= e($m['regiao_nome'] ?? 'Não especificada') ?>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase <?= get_difficulty_badge($m['dificuldade'] ?? 'medio') ?>">
                                        <?= e($m['dificuldade'] ?? 'medio') ?>
                                    </span>
                                </td>
                                <td class="p-4 font-mono text-soul-400 whitespace-nowrap">
                                    X: <?= number_format((float)$m['x_percent'], 1) ?>% | Y: <?= number_format((float)$m['y_percent'], 1) ?>%
                                </td>
                                <td class="p-4 text-slate-400 whitespace-nowrap">
                                    <?= e($m['usuario_nome'] ?? 'Anônimo') ?>
                                </td>
                                <td class="p-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="index.php?jogo=<?= e($m['jogo_slug']) ?>" title="Ver no Mapa" class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-soul-400 hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>

                                        <?php if ($currentUser && ($currentUser['nivel'] === 'admin' || (int)$m['usuario_id'] === (int)$currentUser['id'])): ?>
                                            <a href="actions/marcador_excluir.php?id=<?= (int)$m['id'] ?>&token=<?= csrf_token() ?>" 
                                               data-confirm="Tem certeza que deseja excluir o marcador '<?= e($m['titulo']) ?>'?" 
                                               data-title="Excluir Marcador"
                                               class="p-1.5 rounded-lg bg-rose-950/40 hover:bg-rose-900 text-rose-400 hover:text-white border border-rose-500/30 transition-colors" title="Excluir">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile: Cards Responsivos (MobileFirst) -->
            <div class="md:hidden divide-y divide-slate-800">
                <?php foreach ($marcadores as $m): ?>
                    <div class="p-4 space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-bold text-white text-sm"><?= e($m['titulo']) ?></h3>
                                <span class="text-xs text-slate-400"><?= e($m['regiao_nome'] ?? 'Região Desconhecida') ?></span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold"
                                  style="color: <?= e($m['categoria_cor']) ?>; background-color: <?= e($m['categoria_cor']) ?>22;">
                                <?= e($m['categoria_nome']) ?>
                            </span>
                        </div>
                        <p class="text-xs text-slate-300"><?= e($m['descricao']) ?></p>
                        <div class="flex items-center justify-between text-[11px] pt-1">
                            <span class="font-mono text-soul-400">X: <?= number_format((float)$m['x_percent'], 1) ?>% | Y: <?= number_format((float)$m['y_percent'], 1) ?>%</span>
                            <div class="flex items-center gap-2">
                                <a href="index.php?jogo=<?= e($m['jogo_slug']) ?>" class="px-2.5 py-1 rounded bg-slate-800 text-soul-300 font-medium">Ver Mapa</a>
                                <?php if ($currentUser && ($currentUser['nivel'] === 'admin' || (int)$m['usuario_id'] === (int)$currentUser['id'])): ?>
                                    <a href="actions/marcador_excluir.php?id=<?= (int)$m['id'] ?>&token=<?= csrf_token() ?>"
                                       data-confirm="Excluir o marcador '<?= e($m['titulo']) ?>'?" 
                                       data-title="Excluir"
                                       class="px-2.5 py-1 rounded bg-rose-950 text-rose-300 border border-rose-800">Excluir</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
