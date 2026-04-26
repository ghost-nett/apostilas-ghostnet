<?php 
include 'config.php'; 

// Pega os dados enviados pela página de seleção
$serie_escolhida = isset($_GET['serie']) ? $_GET['serie'] : '';
$categoria = isset($_GET['cat']) ? $_GET['cat'] : '';

// Lógica de Banco de Dados (Mantida 100% igual)
if ($serie_escolhida == "SP Acao") {
    $sql = "SELECT * FROM apostilas WHERE serie = 'SP Acao' ORDER BY disciplina DESC, titulo ASC";
} elseif ($serie_escolhida != "") {
    if ($categoria == 'base') {
        $sql = "SELECT * FROM apostilas WHERE serie = '$serie_escolhida' AND (disciplina LIKE '%Português%' OR disciplina LIKE '%Matemática%') AND disciplina != 'SP Acao' ORDER BY disciplina DESC, titulo ASC";
    } else {
        $sql = "SELECT * FROM apostilas WHERE serie = '$serie_escolhida' AND disciplina NOT LIKE '%Português%' AND disciplina NOT LIKE '%Matemática%' AND disciplina != 'SP Acao' ORDER BY disciplina ASC";
    }
} else {
    $sql = "SELECT * FROM apostilas ORDER BY serie ASC, disciplina DESC, titulo ASC";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Ghostnet Apostilas — Leitor</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
 
  :root {
    --bg: #090A0F;
    --surface: rgba(21, 21, 28, 0.4); 
    --border: rgba(255,255,255,0.1);
    --text: #f3f4f6;
    --muted: rgba(243,244,246,0.4);
    --accent: #ef4444;
  }
 
  body {
    font-family: 'Sora', sans-serif;
    background: var(--bg);
    color: var(--text);
    height: 100vh;
    height: 100dvh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  /* ── FUNDO IGUAL À PÁGINA INICIAL ── */
  #stars-container { 
    position: fixed; 
    top: 0; left: 0; 
    width: 100%; height: 100%; 
    background: radial-gradient(ellipse at bottom, #1B2735 0%, #090A0F 100%); 
    z-index: -2; 
    overflow: hidden; 
  }
  #stars, #stars2 { position: absolute; top: 0; left: 0; background: transparent; }
  @keyframes animStar { from { transform: translateY(0px); } to { transform: translateY(-2000px); } }
  #stars { width: 1px; height: 1px; animation: animStar 50s linear infinite; }
  #stars2 { width: 2px; height: 2px; animation: animStar 100s linear infinite; }
  #stars:after, #stars2:after { content: " "; position: absolute; top: 2000px; width: inherit; height: inherit; box-shadow: inherit; }

  /* ── BARRAS TRANSPARENTES (GLASS) ── */
  .topbar {
    height: 55px;
    background: rgba(15, 15, 20, 0.5);
    backdrop-filter: blur(15px);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center; padding: 0 1.5rem; gap: 16px;
    z-index: 1000; flex-shrink: 0;
  }
  .topbar-back { color: var(--muted); text-decoration: none; font-size: 13px; font-weight: 600; }
  .topbar-title { font-size: 14px; font-weight: 600; color: #fff; flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
 
  .viewer-layout { flex: 1; display: flex; overflow: hidden; background: transparent; }
 
  .sidebar {
    width: 260px;
    background: rgba(15, 15, 20, 0.5);
    backdrop-filter: blur(25px);
    border-right: 1px solid var(--border);
    display: flex; flex-direction: column; overflow: hidden; flex-shrink: 0; z-index: 900;
  }
  .sidebar-head { padding: 20px 16px 12px; font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--muted); border-bottom: 1px solid var(--border); }
  .sidebar-books { overflow-y: auto; flex: 1; padding: 12px; }
  .sidebar-section { font-size: 10px; font-weight: 700; text-transform: uppercase; color: var(--accent); padding: 16px 8px 8px; }
  
  .sidebar-item { display: flex; align-items: center; gap: 12px; padding: 12px; border-radius: 12px; cursor: pointer; transition: 0.2s; margin-bottom: 8px; border: 1px solid transparent; }
  .sidebar-item:hover { background: rgba(255, 255, 255, 0.05); }
  .sidebar-item.active { 
    background: rgba(239, 68, 68, 0.15); 
    border: 1px solid #ef4444; 
    box-shadow: 0 0 15px rgba(239, 68, 68, 0.2); 
  }
  
  .sidebar-num { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; background: rgba(255,255,255,0.05); }
  .sidebar-name { font-size: 13px; font-weight: 600; color: #fff; }

  .main-area { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: transparent; }
  .canvas-wrap { flex: 1; overflow-y: auto; display: flex; flex-direction: column; align-items: center; padding: 2rem 1rem; background: transparent; }
  #pdf-canvas { max-width: 100%; border-radius: 4px; box-shadow: 0 10px 50px rgba(0,0,0,0.8); background: #fff; }

  /* ── CONTROLES DO RODAPÉ ── */
  .controls {
    height: 70px; background: rgba(15, 15, 20, 0.8); backdrop-filter: blur(15px); border-top: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center; gap: 12px; flex-shrink: 0;
    padding-bottom: env(safe-area-inset-bottom);
  }
  .ctrl-btn { width: 40px; height: 42px; border-radius: 10px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: #fff; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
  .ctrl-btn:hover { background: var(--accent); }
  #page-input { width: 55px; background: rgba(0,0,0,0.4); border: 1px solid var(--border); border-radius: 6px; color: var(--accent); font-weight: 700; text-align: center; padding: 6px 0; outline: none; }

  @media (max-width: 768px) {
    .sidebar { position: absolute; left: 0; top: 0; bottom: 0; transform: translateX(-100%); transition: transform 0.3s ease; width: 280px; }
    .sidebar.open { transform: translateX(0); box-shadow: 10px 0 30px rgba(0,0,0,0.5); }
  }
</style>
</head>
<body>

<div id="stars-container"><div id="stars"></div><div id="stars2"></div></div>

<div class="topbar">
  <button style="background:none; border:none; color:#fff; font-size:24px; cursor:pointer;" onclick="toggleSidebar()">☰</button>
  <a class="topbar-back" href="selecao.php">← VOLTAR</a>
  <div class="topbar-title" id="topbar-title">Selecione uma apostila</div>
</div>

<div class="viewer-layout">
  <div class="sidebar" id="sidebar">
    <div class="sidebar-head">Biblioteca Ghostnet</div>
    <div class="sidebar-books" id="sidebar-books">
      <?php
      $current_discipline = "";
      if ($result && $result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              if ($current_discipline != $row['disciplina']) {
                  $current_discipline = $row['disciplina'];
                  echo '<div class="sidebar-section">' . $current_discipline . '</div>';
              }
              $numero_icone = substr(trim($row['titulo']), -1);
              if(!is_numeric($numero_icone)) $numero_icone = "•";
              echo '<div class="sidebar-item" onclick="openBook(\'apostilas/'.$row['arquivo'].'\',\''.$row['titulo'].'\',\''.$row['disciplina'].'\',this, '.$row['offset'].')">';
              echo '  <div class="sidebar-num">'.$numero_icone.'</div>';
              echo '  <div class="sidebar-info">';
              echo '    <div class="sidebar-name">'.$row['titulo'].'</div>';
              echo '    <div class="sidebar-sub">'.$row['disciplina'].'</div>';
              echo '  </div>';
              echo '</div>';
          }
      }
      ?>
    </div>
  </div>
 
  <div class="main-area">
    <div class="canvas-wrap" id="canvas-wrap">
      <div id="loading-msg" style="text-align:center; padding-top:100px; color:#fff; display:block;">
        <div style="font-size:40px; margin-bottom:10px; animation: pulse 1.5s infinite;">🚀</div>
        <div id="loading-text" style="font-weight:bold; font-size:18px;">Escolha um material na lateral</div>
      </div>
      <canvas id="pdf-canvas" style="display:none"></canvas>
    </div>
 
    <div class="controls" id="controls" style="display:none">
      <button class="ctrl-btn" onclick="goToPage(1)">⏮</button>
      <button class="ctrl-btn" id="btn-prev" onclick="changePage(-1)">‹</button>
      <div style="font-size:13px; color:#fff">
        <input type="text" id="page-input" value="1" onchange="goToPage(this.value)"> / <span id="page-total">?</span>
      </div>
      <button class="ctrl-btn" id="btn-next" onclick="changePage(1)">›</button>
      <button class="ctrl-btn" id="btn-last" onclick="goToPage(totalPages)">⏭</button>
    </div>
  </div>
</div>
 
<script>
  function toggleSidebar() { document.getElementById('sidebar').classList.toggle('open'); }
  
  pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  let pdfDoc = null, currentPage = 1, totalPages = 0, zoom = 1, rendering = false, currentOffset = 0;

  function openBook(url, title, discipline, el, offset) {
    currentOffset = parseInt(offset);
    document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('topbar-title').textContent = title;
    if (window.innerWidth <= 768) { document.getElementById('sidebar').classList.remove('open'); }
    loadPDF(url);
  }

  function loadPDF(url) {
    const canvas = document.getElementById('pdf-canvas'), 
          loading = document.getElementById('loading-msg'), 
          loadingText = document.getElementById('loading-text'),
          controls = document.getElementById('controls');
    
    // Limpa o canvas e mostra o "Carregando..."
    canvas.style.display = 'none'; 
    controls.style.display = 'none';
    loading.style.display = 'block';
    loadingText.innerHTML = "Carregando livro...";

    pdfjsLib.getDocument(url).promise.then(pdf => {
      pdfDoc = pdf; totalPages = pdf.numPages;
      document.getElementById('page-total').textContent = totalPages - currentOffset;
      currentPage = 1 + currentOffset;
      
      // Esconde o loading e mostra o PDF
      controls.style.display = 'flex'; 
      loading.style.display = 'none'; 
      canvas.style.display = 'block';
      renderPage(currentPage);
    }).catch(err => {
      loadingText.innerHTML = "⚠️ Erro ao abrir material.";
    });
  }

  function renderPage(num) {
    rendering = true;
    pdfDoc.getPage(num).then(page => {
      const outputScale = window.devicePixelRatio || 1;
      const viewport = page.getViewport({ scale: zoom * outputScale });
      const canvas = document.getElementById('pdf-canvas'), ctx = canvas.getContext('2d');
      canvas.width = viewport.width; canvas.height = viewport.height;
      canvas.style.width = (viewport.width / outputScale) + "px";
      page.render({ canvasContext: ctx, viewport: viewport }).promise.then(() => { rendering = false; updateUI(); });
    });
  }

  function changePage(delta) { if (!rendering && currentPage + delta >= 1 && currentPage + delta <= totalPages) { currentPage += delta; renderPage(currentPage); } }
  function goToPage(val) { let n = parseInt(val) + currentOffset; if (n > 0 && n <= totalPages) { currentPage = n; renderPage(currentPage); } }
  function updateUI() { document.getElementById('page-input').value = currentPage - currentOffset; }

  function createStars(id, count) {
    const canvas = document.getElementById(id);
    let stars = "";
    for (let i = 0; i < count; i++) { stars += `${Math.random() * 2000}px ${Math.random() * 2000}px #FFF${i < count - 1 ? ',' : ''}`; }
    canvas.style.boxShadow = stars;
  }
  createStars('stars', 700); createStars('stars2', 200);
</script>

<style>
@keyframes pulse {
  0% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.2); opacity: 0.7; }
  100% { transform: scale(1); opacity: 1; }
}
</style>

</body>
</html>