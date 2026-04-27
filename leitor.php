<?php 
include 'config.php'; 

$serie_escolhida = isset($_GET['serie']) ? $_GET['serie'] : '';
$categoria = isset($_GET['cat']) ? $_GET['cat'] : '';
$arquivo_aberto = isset($_GET['arquivo']) ? $_GET['arquivo'] : '';
$titulo_aberto = isset($_GET['titulo']) ? $_GET['titulo'] : '';
$offset_aberto = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

// Lógica de busca no banco
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
        
        body { font-family: 'Sora', sans-serif; background: var(--bg); color: #fff; min-height: 100vh; }

        /* FUNDO ESTELAR */
        #stars-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(ellipse at bottom, #1B2735 0%, #090A0F 100%); z-index: -1; }
        #stars, #stars2 { position: absolute; top: 0; left: 0; background: transparent; }
        #stars { width: 1px; height: 1px; animation: animStar 50s linear infinite; }
        #stars2 { width: 2px; height: 2px; animation: animStar 100s linear infinite; }
        @keyframes animStar { from { transform: translateY(0px); } to { transform: translateY(-2000px); } }

        /* HEADER */
        .header { 
            padding: 20px; text-align: center; background: rgba(15, 15, 20, 0.5); 
            backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 100;
        }
        .btn-voltar { text-decoration: none; color: var(--accent); font-size: 14px; font-weight: bold; display: block; margin-bottom: 5px; }
        
        /* GRID DE CARDS */
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .grid-books { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }

        .card-book {
            background: rgba(255, 255, 255, 0.03); border: 1px solid var(--border); 
            border-radius: 20px; padding: 25px; text-align: center; backdrop-filter: blur(5px);
            transition: 0.3s ease; display: flex; flex-direction: column; align-items: center;
        }
        .card-book:hover { border-color: var(--accent); transform: translateY(-5px); background: rgba(239, 68, 68, 0.05); }

        .book-icon { font-size: 40px; margin-bottom: 15px; }
        .book-title { font-size: 18px; font-weight: 700; margin-bottom: 5px; }
        .book-discipline { font-size: 12px; color: var(--accent); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px; }

        /* BOTÕES */
        .actions { display: flex; gap: 10px; width: 100%; }
        .btn { 
            flex: 1; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: 600; font-size: 14px;
            display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s;
        }
        .btn-read { background: var(--accent); color: #fff; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); }
        .btn-download { background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border); }
        .btn:hover { opacity: 0.9; transform: scale(1.02); }

        /* LEITOR PDF (SÓ APARECE QUANDO CLICAR EM LER) */
        #pdf-viewer-overlay { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg); 
            z-index: 1000; display: <?php echo $arquivo_aberto ? 'flex' : 'none'; ?>; flex-direction: column; 
        }
        .viewer-top { height: 60px; padding: 0 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); background: #0f0f14; }
        .canvas-wrap { flex: 1; overflow: auto; padding: 20px; display: flex; justify-content: center; }
        #pdf-canvas { max-width: 100%; box-shadow: 0 0 50px rgba(0,0,0,0.5); }
        
        .controls { height: 70px; background: #0f0f14; display: flex; align-items: center; justify-content: center; gap: 15px; }
        .ctrl-btn { padding: 10px 15px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: #fff; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body>

<div id="stars-container"><div id="stars"></div><div id="stars2"></div></div>

<div class="header">
    <a href="selecao.php" class="btn-voltar">← MUDAR SÉRIE</a>
    <h2><?php echo $serie_escolhida; ?></h2>
</div>

<div class="container">
    <div class="grid-books">
        <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $icon = (strpos($row['disciplina'], 'Matemática') !== false) ? '📐' : ((strpos($row['disciplina'], 'Português') !== false) ? '📚' : '🧪');
                ?>
                <div class="card-book">
                    <div class="book-icon"><?php echo $icon; ?></div>
                    <div class="book-title"><?php echo $row['titulo']; ?></div>
                    <div class="book-discipline"><?php echo $row['disciplina']; ?></div>
                    <div class="actions">
                        <a href="?serie=<?php echo $serie_escolhida; ?>&cat=<?php echo $categoria; ?>&arquivo=<?php echo urlencode($row['arquivo']); ?>&titulo=<?php echo urlencode($row['titulo']); ?>&offset=<?php echo $row['offset']; ?>" class="btn btn-read">Ler Online</a>
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
        <div style="font-weight: bold;"><?php echo $titulo_aberto; ?></div>
        <a href="leitor.php?serie=<?php echo $serie_escolhida; ?>&cat=<?php echo $categoria; ?>" style="color: var(--accent); text-decoration: none; font-weight: bold;">FECHAR ✕</a>
    </div>
    
    <div class="canvas-wrap">
        <canvas id="pdf-canvas"></canvas>
    </div>

    <div class="controls">
        <button class="ctrl-btn" onclick="changePage(-1)">Anterior</button>
        <span id="page-num">0</span> / <span id="page-count">0</span>
        <button class="ctrl-btn" onclick="changePage(1)">Próxima</button>
    </div>
</div>

<script>
    // Gerador de Estrelas
    function createStars(id, count) {
        const canvas = document.getElementById(id);
        let stars = "";
        for (let i = 0; i < count; i++) {
            stars += `${Math.random() * 2000}px ${Math.random() * 2000}px #FFF${i < count - 1 ? ',' : ''}`;
        }
        canvas.style.boxShadow = stars;
    }
    createStars('stars', 700); createStars('stars2', 200);

    // Lógica do PDF
    <?php if($arquivo_aberto): ?>
    const url = 'apostilas/<?php echo $arquivo_aberto; ?>';
    const offset = <?php echo $offset_aberto; ?>;
    let pdfDoc = null, pageNum = 1 + offset, pageRendering = false, pageNumPending = null;
    const canvas = document.getElementById('pdf-canvas'), ctx = canvas.getContext('2d');

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    function renderPage(num) {
        pageRendering = true;
        pdfDoc.getPage(num).then((page) => {
            const viewport = page.getViewport({scale: 1.5});
            canvas.height = viewport.height; canvas.width = viewport.width;
            const renderContext = { canvasContext: ctx, viewport: viewport };
            page.render(renderContext).promise.then(() => {
                pageRendering = false;
                if (pageNumPending !== null) { renderPage(pageNumPending); pageNumPending = null; }
            });
        });
        document.getElementById('page-num').textContent = num - offset;
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
    <?php endif; ?>
</script>

</body>
</html>