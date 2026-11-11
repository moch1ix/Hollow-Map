<?php
/**
 * Hollow-Map - Mapa Interativo Principal
 * Conforme requisitos.md:
 * - HTML5, PHP, Tailwind CSS (Mobile-First)
 * - Apontar localização de itens, inimigos e upgrades em Hollow Knight e Silksong
 * - Consultas via PDO
 * - Sem alerts/confirms nativos (utiliza modais e toasts)
 */

declare(strict_types=1);

require_once __DIR__ . '/config/bd.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Hollow-Map - Mapa Interativo de Hallownest & Silksong';
$activePage = 'mapa';
$jogoSlugAtual = $_GET['jogo'] ?? 'hollow-knight';

// Dados com fallback caso o banco ainda esteja sendo iniciado
$dbError = null;
$categoriasDisponiveis = [];
$regioesDisponiveis = [];
$marcadores = [];
$jogoAtivo = [
    'slug' => $jogoSlugAtual,
    'nome' => $jogoSlugAtual === 'silksong' ? 'Hollow Knight: Silksong' : 'Hollow Knight'
];

try {
    $pdo = getDB();

    // Consulta jogo ativo
    $stmtJogo = $pdo->prepare("SELECT * FROM jogos WHERE slug = :slug LIMIT 1");
    $stmtJogo->execute([':slug' => $jogoSlugAtual]);
    $jogoDB = $stmtJogo->fetch();
    if ($jogoDB) {
        $jogoAtivo = $jogoDB;
    }

    // Consulta categorias
    $stmtCat = $pdo->query("SELECT * FROM categorias ORDER BY id ASC");
    $categoriasDisponiveis = $stmtCat->fetchAll();

    // Consulta regiões do jogo selecionado
    $stmtReg = $pdo->prepare("SELECT * FROM regioes WHERE jogo_id = :jogo_id ORDER BY nome ASC");
    $stmtReg->execute([':jogo_id' => $jogoAtivo['id'] ?? 1]);
    $regioesDisponiveis = $stmtReg->fetchAll();

    // Consulta marcadores do jogo selecionado
    $sqlMarcadores = "SELECT 
                        m.id, m.titulo, m.descricao, m.x_percent, m.y_percent, m.dificuldade, m.imagem_url, m.criado_em,
                        c.id as categoria_id, c.nome as categoria_nome, c.slug as categoria_slug, c.cor as categoria_cor,
                        r.id as regiao_id, r.nome as regiao_nome,
                        u.nome as usuario_nome,
                        j.slug as jogo_slug
                      FROM marcadores m
                      JOIN jogos j ON m.jogo_id = j.id
                      JOIN categorias c ON m.categoria_id = c.id
                      LEFT JOIN regioes r ON m.regiao_id = r.id
                      LEFT JOIN usuarios u ON m.usuario_id = u.id
                      WHERE j.slug = :slug
                      ORDER BY m.id ASC";

    $stmtM = $pdo->prepare($sqlMarcadores);
    $stmtM->execute([':slug' => $jogoSlugAtual]);
    $marcadores = $stmtM->fetchAll();

} catch (Exception $e) {
    $dbError = $e->getMessage();
}

$mapSvgPath = $jogoSlugAtual === 'silksong' ? 'assets/img/map_pharloom.svg' : 'assets/img/map_hallownest.svg';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="flex-1 flex flex-col relative overflow-hidden">

    <?php if ($dbError): ?>
        <!-- Aviso Amigável de Configuração do Banco de Dados -->
        <div class="m-4 p-4 rounded-2xl bg-amber-950/80 border border-amber-600/50 text-amber-200 text-sm flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <span class="font-bold">Aviso de Configuração do MySQL:</span> <?= e($dbError) ?>
                </div>
            </div>
            <div class="shrink-0 text-xs bg-amber-900/60 px-3 py-1.5 rounded-lg border border-amber-500/40 font-mono">
                mysql -u root &lt; database/schema.sql
            </div>
        </div>
    <?php endif; ?>

    <!-- Barra de Controle & Filtros Superior (MobileFirst) -->
    <section class="bg-abyss-900/90 border-b border-slate-800 p-3 sm:px-6 backdrop-blur-md">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            
            <!-- Título & Status da Área -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-base sm:text-lg font-bold font-hollow text-white flex items-center gap-2">
                        <?= e($jogoAtivo['nome']) ?>
                        <span id="markers-count-badge" class="text-[11px] font-sans font-semibold px-2 py-0.5 rounded-full bg-slate-800 text-soul-300 border border-slate-700">
                            <?= count($marcadores) ?> marcados
                        </span>
                    </h1>
                    <p class="text-[11px] text-slate-400">
                        <?= $jogoSlugAtual === 'silksong' ? 'Reino de Pharloom • Seda e Melodia' : 'Reino de Hallownest • O Grande Vazio' ?>
                    </p>
                </div>

                <!-- Botão de Novo Ponto Mobile -->
                <?php if (is_logged_in()): ?>
                    <button type="button" 
                            onclick="window.showToast('Toque ou clique em qualquer ponto do mapa para definir a localização!', 'info')"
                            class="sm:hidden px-3 py-1.5 text-xs font-semibold rounded-xl bg-soul-500 text-white shadow-md">
                        + Marcar
                    </button>
                <?php endif; ?>
            </div>

            <!-- Campo de Busca e Filtros Rápidos -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <!-- Busca de Itens -->
                <div class="relative flex-1 sm:w-64">
                    <input type="text" 
                           id="search-input" 
                           placeholder="Buscar item, amuleto, chefe..."
                           class="w-full bg-abyss-950 border border-slate-700 rounded-xl pl-9 pr-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-soul-400 transition-colors">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>

                <!-- Dica para o usuário -->
                <?php if (is_logged_in()): ?>
                    <div class="hidden sm:flex items-center text-xs text-soul-300 bg-soul-950/60 border border-soul-500/30 px-3 py-1.5 rounded-xl font-medium">
                        💡 Clique no mapa para marcar um segredo
                    </div>
                <?php else: ?>
                    <a href="login.php" class="hidden sm:flex items-center text-xs text-slate-300 hover:text-white bg-slate-800/80 px-3 py-1.5 rounded-xl border border-slate-700">
                        Conecte-se para adicionar marcadores
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filtros em Carrossel/Scroll Horizontal por Categorias -->
        <div class="max-w-7xl mx-auto flex items-center gap-2 overflow-x-auto py-2.5 mt-1 no-scrollbar text-xs">
            <button type="button" 
                    onclick="filterByCat('all', this)"
                    class="cat-filter-btn px-3 py-1 rounded-lg font-semibold bg-soul-500 text-white shrink-0 transition-all">
                Todos
            </button>
            <?php foreach ($categoriasDisponiveis as $cat): ?>
                <button type="button" 
                        onclick="filterByCat('<?= e($cat['slug']) ?>', this)"
                        class="cat-filter-btn px-3 py-1 rounded-lg font-medium text-slate-300 bg-slate-800/70 hover:bg-slate-700/80 hover:text-white shrink-0 transition-all border border-slate-700/60 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full" style="background-color: <?= e($cat['cor']) ?>;"></span>
                    <?= e($cat['nome']) ?>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Canvas do Mapa Interativo -->
    <div class="relative flex-1 bg-abyss-950 overflow-hidden map-container cursor-grab" id="map-viewport">
        
        <!-- Controles Flutuantes de Zoom (Desktop & Mobile) -->
        <div class="absolute bottom-5 right-5 z-30 flex flex-col gap-2 bg-abyss-900/90 border border-slate-800 rounded-2xl p-1.5 backdrop-blur-md shadow-2xl">
            <button type="button" id="btn-zoom-in" title="Aproximar Zoom" class="w-9 h-9 rounded-xl bg-slate-800/80 hover:bg-slate-700 flex items-center justify-center text-slate-200 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            </button>
            <button type="button" id="btn-zoom-out" title="Afastar Zoom" class="w-9 h-9 rounded-xl bg-slate-800/80 hover:bg-slate-700 flex items-center justify-center text-slate-200 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
            </button>
            <button type="button" id="btn-zoom-reset" title="Centralizar Mapa" class="w-9 h-9 rounded-xl bg-slate-800/80 hover:bg-slate-700 flex items-center justify-center text-slate-200 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
            </button>
        </div>

        <!-- Camada Móvel do Mapa (Suporte a Zoom e Drag) -->
        <div id="map-wrapper" class="relative w-full h-full flex items-center justify-center transform origin-center transition-transform duration-75">
            <div class="relative inline-block select-none shadow-2xl">
                <!-- SVG do Mapa -->
                <img id="map-image" 
                     src="<?= e($mapSvgPath) ?>" 
                     alt="Mapa de <?= e($jogoAtivo['nome']) ?>" 
                     class="max-w-none w-[1300px] h-auto pointer-events-none rounded-xl border border-slate-800/60 shadow-2xl">

                <!-- Camada de Marcadores Interativos -->
                <div id="markers-layer" class="absolute inset-0 pointer-events-auto"></div>
            </div>
        </div>

    </div>

</main>

<!-- Inclusão do Controlador Interativo do Mapa -->
<script src="assets/js/map.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const markersData = <?= json_encode($marcadores, JSON_UNESCAPED_UNICODE) ?>;

        window.hollowMap = new HollowInteractiveMap({
            containerId: 'map-viewport',
            wrapperId: 'map-wrapper',
            mapImgId: 'map-image',
            markersLayerId: 'markers-layer',
            markers: markersData,
            isLoggedIn: <?= is_logged_in() ? 'true' : 'false' ?>,
            currentGame: <?= json_encode($jogoSlugAtual) ?>
        });

        // Conectar input de pesquisa
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                window.hollowMap.filterSearch(e.target.value);
            });
        }
    });

    function filterByCat(categorySlug, buttonEl) {
        document.querySelectorAll('.cat-filter-btn').forEach(btn => {
            btn.classList.remove('bg-soul-500', 'text-white');
            btn.classList.add('bg-slate-800/70', 'text-slate-300');
        });

        buttonEl.classList.remove('bg-slate-800/70', 'text-slate-300');
        buttonEl.classList.add('bg-soul-500', 'text-white');

        if (window.hollowMap) {
            window.hollowMap.filterCategory(categorySlug);
        }
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
