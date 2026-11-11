<?php
require_once __DIR__.'/config/bd.php'; require_once __DIR__.'/includes/auth.php';
$pageTitle='Explorar mapas'; require_once __DIR__.'/includes/header.php'; require_once __DIR__.'/includes/navbar.php';
$games=db()->query('SELECT * FROM games ORDER BY id')->fetchAll();
$selected=(int)($_GET['game']??($games[0]['id']??1));
?>
<main class="app-shell">
<aside class="sidebar">
 <div class="eyebrow">GUIA DE EXPLORAÇÃO</div><h1>Encontre seu<br><em>próximo destino.</em></h1>
 <label class="field-label" for="gameSelect">JOGO</label><select id="gameSelect"><?php foreach($games as $g): ?><option value="<?=$g['id']?>" <?=$selected===$g['id']?'selected':''?>><?=e($g['name'])?></option><?php endforeach; ?></select>
 <label class="field-label" for="searchInput">PESQUISAR</label><input id="searchInput" placeholder="Nome ou descrição...">
 <div class="filter-heading"><span>CATEGORIAS</span><button id="clearFilters" class="text-button">Limpar</button></div><div id="categoryFilters" class="category-list"><p class="muted">Carregando categorias…</p></div>
 <div class="filter-heading"><span>REGIÃO</span></div><select id="regionSelect"><option value="">Todas as regiões</option></select>
 <div class="progress-card"><div class="progress-top"><span>SEU PROGRESSO</span><strong id="progressText">Entre para salvar</strong></div><div class="progress-track"><span id="progressBar"></span></div><small id="progressCount">Faça login para sincronizar suas descobertas.</small></div>
</aside>
<section class="map-section"><div class="map-toolbar"><div><span class="live-dot"></span><span id="mapTitle">Hallownest / Pharloom</span></div><div class="toolbar-actions"><button id="resetMap" title="Centralizar">◎</button><button id="toggleList" title="Lista">☷</button></div></div>
<div id="map" aria-label="Mapa interativo"></div><div class="map-note" id="mapNotice">Mapa base não instalado. Adicione uma imagem autorizada no painel administrativo.</div>
<div id="markerList" class="marker-list hidden"></div></section>
</main>
<script>window.HM={api:'api',logged:<?=is_logged_in()?'true':'false'?>,userId:<?=json_encode(current_user()['id']??null)?>,initialGame:<?=$selected?>};</script>
<script src="assets/js/map.js"></script>
