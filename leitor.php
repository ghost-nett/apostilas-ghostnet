<?php 
include 'config.php'; 

// Pega os parâmetros da URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$serie_escolhida = isset($_GET['serie']) ? $_GET['serie'] : '';
$categoria = isset($_GET['cat']) ? $_GET['cat'] : '';
$pagina_atual = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;

// --- LÓGICA 1: MODO LEITOR (Se tiver ID) ---
if ($id > 0) {
    $sql = "SELECT * FROM apostilas WHERE id = $id";
    $res = $conn->query($sql);
    $livro = $res->fetch_assoc();

    if ($livro) {
        $pasta = $livro['arquivo']; 
        $total = $livro['total_paginas'];
        $titulo = $livro['titulo'];
        $download = $livro['link_download'];

        // Monta o nome da imagem conforme o seu print (ex: nome_page-001.jpg)
        $numero_formatado = str_pad($pagina_atual, 3, "0", STR_PAD_LEFT);
        $caminho_imagem = "apostilas/$pasta/{$pasta}_page-{$numero_formatado}.jpg";
    }
} 

// --- LÓGICA 2: MODO GALERIA (Se NÃO tiver ID) ---
else {
    if ($serie_escolhida == "SP Acao") {
        $sql = "SELECT * FROM apostilas WHERE serie = 'SP Acao' ORDER BY disciplina DESC, titulo ASC";
    } else {
        if ($categoria == 'base') {
            $sql = "SELECT * FROM apostilas WHERE serie = '$serie_escolhida' AND (disciplina LIKE '%Português%' OR disciplina LIKE '%Matemática%') AND disciplina != 'SP Acao' ORDER BY disciplina DESC, titulo ASC";
        } else {
            $sql = "SELECT * FROM apostilas WHERE serie = '$serie_escolhida' AND disciplina NOT LIKE '%Português%' AND disciplina NOT LIKE '%Matemática%' AND disciplina != 'SP Acao' ORDER BY disciplina ASC";
        }
    }
    $result_galeria = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghostnet — <?php echo ($id > 0) ? $titulo : "Galeria"; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Sora', sans-serif; background: #090A0F; color: #fff; min-height: 100vh; }
        #stars-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(ellipse at bottom, #1B2735 0%, #090A0F 100%); z-index: -1; }
        
        /* HEADER */
        .header { padding: 15px; text-align: center; background: rgba(15, 15, 20, 0.7); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255,255,255,0.1); }
        .btn-voltar { color: #ef4444; text-decoration: none; font-weight: bold; font-size: 14px; }

        /* GALERIA DE CARDS */
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
        .card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 20px; text-align: center; backdrop-filter: blur(5px); }
        .card h3 { margin: 10px 0; font-size: 18px; }
        .card-actions { display: flex; gap: 10px; margin-top: 15px; }
        .btn-card { flex: 1; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 14px; }
        .btn-ler { background: #ef4444; color: #fff; }
        .btn-down { background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2); }

        /* MODO LEITOR */
        .viewer { display: flex; flex-direction: column; height: 100vh; background: #000; position: fixed; top: 0; left: 0; width: 100%; z-index: 1000; }
        .img-wrap { flex: 1; overflow: auto; display: flex; justify-content: center; padding: 10px; }
        .img-wrap img { max-width: 100%; height: auto; box-shadow: 0 0 30px rgba(0,0,0,0.5); }
        .controls { padding: 20px; background: #0f0f14; display: flex; justify-content: center; gap: 15px; align-items: center; border-top: 1px solid #ef4444; }
        .nav-btn { padding: 12px 20px; background: #ef4444; color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold; }
    </style>
</head>
<body>

<div id="stars-container"></div>

<?php if ($id > 0 && isset($livro)): ?>
    <div class="viewer">
        <div class="header" style="display:flex; justify-content: space-between;">
            <span style="font-size:14px;"><?php echo $titulo; ?></span>
            <a href="leitor.php?serie=<?php echo urlencode($serie_escolhida); ?>&cat=<?php echo $categoria; ?>" class="btn-voltar">FECHAR ✕</a>
        </div>
        <div class="img-wrap">
            <img src="<?php echo $caminho_imagem; ?>" alt="Página <?php echo $pagina_atual; ?>">
        </div>
        <div class="controls">
            <?php if($pagina_atual > 1): ?>
                <a href="?id=<?php echo $id; ?>&pg=<?php echo $pagina_atual - 1; ?>&serie=<?php echo urlencode($serie_escolhida); ?>&cat=<?php echo $categoria; ?>" class="nav-btn">‹ Anterior</a>
            <?php endif; ?>
            
            <span>Página <?php echo $pagina_atual; ?> de <?php echo $total; ?></span>

            <?php if($pagina_atual < $total): ?>
                <a href="?id=<?php echo $id; ?>&pg=<?php echo $pagina_atual + 1; ?>&serie=<?php echo urlencode($serie_escolhida); ?>&cat=<?php echo $categoria; ?>" class="nav-btn">Próxima ›</a>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <div class="header">
        <a href="selecao.php" class="btn-voltar">← VOLTAR</a>
        <h2><?php echo $serie_escolhida; ?></h2>
    </div>

    <div class="container">
        <?php while($row = $result_galeria->fetch_assoc()): ?>
            <div class="card">
                <h3><?php echo $row['titulo']; ?></h3>
                <p style="font-size:12px; color:rgba(255,255,255,0.5);"><?php echo $row['disciplina']; ?></p>
                <div class="card-actions">
                    <a href="?id=<?php echo $row['id']; ?>&pg=1&serie=<?php echo urlencode($serie_escolhida); ?>&cat=<?php echo $categoria; ?>" class="btn-card btn-ler">Ler Online</a>
                    <a href="<?php echo $row['link_download']; ?>" target="_blank" class="btn-card btn-down">Download</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

</body>
</html>