<?php 
include 'config.php'; 

$serie_escolhida = isset($_GET['serie']) ? $_GET['serie'] : '';
$categoria = isset($_GET['cat']) ? $_GET['cat'] : '';
$arquivo_aberto = isset($_GET['arquivo']) ? $_GET['arquivo'] : '';
$titulo_aberto = isset($_GET['titulo']) ? $_GET['titulo'] : '';
$offset_aberto = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

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
    <title>Ghostnet — <?php echo $serie_escolhida ?: 'Materiais'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --bg: #090A0F; --border: rgba(255,255,255,0.1); --accent: #ef4444; }
        
        body { font-family: 'Sora', sans-serif; background: var(--bg); color: #fff; min-height: 100vh; overflow-x: hidden; }

        #stars-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(ellipse at bottom, #1B2735 0%, #090A0F 100%); z-index: -1; }
        #stars, #stars2 { position: absolute; top: 0; left: 0; background: transparent; }
        #stars { width: 1px; height: 1px; animation: animStar 50s linear infinite; }
        #stars2 { width: 2px; height: 2px; animation: animStar 100s linear infinite; }
        @keyframes animStar { from { transform: translateY(0px); } to { transform: translateY(-2000px); } }

        .header { 
            padding: 15px; text-align: center; background: rgba(15, 15, 20, 0.7); 
            backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 100;
        }
        .btn-voltar { text-decoration: none; color: var(--accent); font-size: 13px; font-weight: bold; display: block; margin-bottom: 5px; }
        
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .grid-books { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }

        .card-book {
            background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border); 
            border-radius: 20px; padding: 25px; text-align: center; backdrop-filter: blur(5px);
            transition: 0.3s ease; display: flex; flex-direction: column; align-items: center;
        }
        .card-book:hover { border-color: var(--accent); transform: translateY(-5px); background: rgba(239, 68, 68, 0.05); }
        .book-title { font-size: 18px; font-weight: 700; margin-bottom: 5px; }
        .book-discipline { font-size: 12px; color: var(--accent); text-transform: uppercase; margin-bottom: 20px; }

        .actions { display: flex; gap: 10px; width: 100%; }
        .btn { flex: 1; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s; }
        .btn-read { background: var(--accent); color: #fff; }
        .btn-download { background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border); }

        /* LEITOR PDF OVERLAY */
        #pdf-viewer-overlay { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #000; 
            z-index: 1000; display: <?php echo $arquivo_aberto ? 'flex' : 'none'; ?>; flex-direction: column; 
        }
        .viewer-top { height: 60px; padding: 0 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); background: #0f0f14; }
        
        .canvas-wrap { 
            flex: 1; overflow: auto; display: flex; justify-content: center; align-items: flex-start; padding: 10px; background: #1a1a1a;
        }
        #pdf-canvas { 
            max-width: none; /* Permitir que o zoom funcione além da largura da tela */
            height: auto !important; box-shadow: 0 0 30px rgba(0,0,0,0.5); 
        }
        
        .controls { 
            height: auto; min-height: 80px; background: #0f0f14; display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 15px; 
            padding: 10px; padding-bottom: calc(10px + env(safe-area-inset-bottom));
        }
        .ctrl-group { display: flex; align-items: center; gap: 10px; }
        .ctrl-btn { width: 44px; height: 44px; background: rgba(255,255,255,0.08); border: 1px solid var(--border); color: #fff; border-radius: 10px; cursor: pointer; font-size: 18px; font-weight: bold; display: flex; align-items: center; justify-content: center; }
        .ctrl-btn:active { background: var(--accent); }
        
        #page-input { 
            width: 50px; background: #000; border: 1px solid var(--accent); color: var(--accent); 
            text-align: center; padding: 8px; border-radius: 8px; font-weight: bold; outline: none;
        }
        .zoom-info { font-size: 12px; color: rgba(255,255,255,0.5); width: 40px; text-align: center; }
    </style>
</head>
<body>

<div id="stars-container"><div id="stars"></div><div id="stars2"></div></div>

<div class="header">
    <a href="selecao.php" class="btn-voltar">← MUDAR SÉRIE</a>
    <h2 style="font-size: 1.2rem;"><?php echo $serie_escolhida; ?></h2>
</div>

<div class="container">
    <div class="grid-books">
        <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                ?>
                <div class="card-book">
                    <div class="book-title"><?php echo $row['titulo']; ?></div>
                    <div class="book-discipline"><?php echo $row['disciplina']; ?></div>
                    <div class="actions">
                        <a href="?serie=<?php echo urlencode($serie_escolhida); ?>&cat=<?php echo $categoria; ?>&arquivo=<?php echo urlencode($row['arquivo']); ?>&titulo=<?php echo urlencode($row['titulo']); ?>&offset=<?php echo $row['offset']; ?>" class="btn btn-read">Ler Online</a>
                        <a href="apostilas/<?php echo $row['arquivo']; ?>" download class="btn btn-download">Baixar</a>
                    </div>
                </div>
                <?php
            }
        }
        ?>
    </div>
</div>

<div id="pdf-viewer-overlay">
    <div class="viewer-top">
        <div style="font-size: 13px; font-weight: bold; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 60%;"><?php echo $titulo_aberto; ?></div>
        <a href="leitor.php?serie=<?php echo urlencode($serie_escolhida); ?>&cat=<?php echo $categoria; ?>" style="color: var(--accent); text-decoration: none; font-weight: bold; font-size: 14px;">FECHAR ✕</a>
    </div>
    
    <div class="canvas-wrap">
        <canvas id="pdf-canvas"></canvas>
    </div>

    <div class="controls">
        <div class="ctrl-group">
            <button class="ctrl-btn" onclick="changePage(-1)">‹</button>
            <div style="font-size: 14px;">
                <input type="number" id="page-input" value="1" onchange="goToPage(this.value)"> / <span id="page-count">0</span>
            </div>
            <button class="ctrl-btn" onclick="changePage(1)">›</button>
        </div>

        <div class="ctrl-group">
            <button class="ctrl-btn" onclick="changeZoom(-0.2)">−</button>
            <span class="zoom-info" id="zoom-val">100%</span>
            <button class="ctrl-btn" onclick="changeZoom(0.2)">+</button>
        </div>
    </div>
</div>

<script>
    function createStars(id, count) {
        const canvas = document.getElementById(id);
        let stars = "";
        for (let i = 0; i < count; i++) {
            stars += `${Math.random() * 2000}px ${Math.random() * 2000}px #FFF${i < count - 1 ? ',' : ''}`;
        }
        canvas.style.boxShadow = stars;
    }
    createStars('stars', 700); createStars('stars2', 200);

    <?php if($arquivo_aberto): ?>
    const url = 'apostilas/<?php echo $arquivo_aberto; ?>';
    const offset = <?php echo $offset_aberto; ?>;
    let pdfDoc = null, pageNum = 1 + offset, pageRendering = false, scale = 1.2; // Escala inicial
    const canvas = document.getElementById('pdf-canvas'), ctx = canvas.getContext('2d');

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    function renderPage(num) {
        pageRendering = true;
        pdfDoc.getPage(num).then((page) => {
            const viewport = page.getViewport({scale: scale});
            canvas.height = viewport.height; 
            canvas.width = viewport.width;
            
            const renderContext = { canvasContext: ctx, viewport: viewport };
            page.render(renderContext).promise.then(() => {
                pageRendering = false;
            });
        });
        document.getElementById('page-input').value = num - offset;
        document.getElementById('zoom-val').textContent = Math.round(scale * 100) + "%";
    }

    pdfjsLib.getDocument(url).promise.then((pdf) => {
        pdfDoc = pdf;
        document.getElementById('page-count').textContent = pdfDoc.numPages - offset;
        renderPage(pageNum);
    });

    function changePage(delta) {
        if (pageRendering) return;
        if (pageNum + delta <= offset || pageNum + delta > pdfDoc.numPages) return;
        pageNum += delta;
        renderPage(pageNum);
    }

    function goToPage(val) {
        const target = parseInt(val) + offset;
        if (target > offset && target <= pdfDoc.numPages) {
            pageNum = target;
            renderPage(pageNum);
        }
    }

    function changeZoom(delta) {
        if (pageRendering) return;
        let newScale = scale + delta;
        // Limites para não ficar pequeno demais nem gigante demais
        if (newScale >= 0.6 && newScale <= 3.0) {
            scale = newScale;
            renderPage(pageNum);
        }
    }
    <?php endif; ?>
</script>

</body>
</html>