<?php 
include 'config.php'; 

// Pega o ID do banco e a página da URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pagina_atual = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;

// Busca os dados no banco
$sql = "SELECT * FROM apostilas WHERE id = $id";
$res = $conn->query($sql);
$livro = $res->fetch_assoc();

if (!$livro) { die("Livro não encontrado no banco de dados."); }

$pasta = $livro['arquivo']; // Nome da pasta: fis-qui-bio-2serie-2bi
$total = $livro['total_paginas']; // Vamos ajustar no banco para 210
$titulo = $livro['titulo'];

// Monta o nome da imagem (ex: fis-qui-bio-2serie-2bi_page-001.jpg)
// O str_pad adiciona os zeros à esquerda (001, 010, etc)
$numero_formatado = str_pad($pagina_atual, 3, "0", STR_PAD_LEFT);
$caminho_imagem = "apostilas/$pasta/{$pasta}_page-{$numero_formatado}.jpg";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghostnet — <?php echo $titulo; ?></title>
    <style>
        body { background: #000; color: #fff; margin: 0; font-family: sans-serif; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        .top-bar { padding: 15px; background: #0f0f14; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ef4444; }
        .container { flex: 1; overflow: auto; display: flex; justify-content: center; background: #1a1a1a; padding: 10px; }
        .img-pagina { max-width: 100%; height: auto; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
        .controls { padding: 20px; background: #0f0f14; display: flex; justify-content: center; gap: 15px; align-items: center; border-top: 1px solid #ef4444; }
        .btn { padding: 12px 20px; background: #ef4444; color: #fff; text-decoration: none; border-radius: 8px; font-weight: bold; }
        .btn-off { background: #333; cursor: not-allowed; }
    </style>
</head>
<body>

    <div class="top-bar">
        <span style="font-size: 14px; font-weight: bold;"><?php echo $titulo; ?></span>
        <a href="selecao.php" style="color: #ef4444; text-decoration: none; font-weight: bold;">FECHAR ✕</a>
    </div>

    <div class="container">
        <img src="<?php echo $caminho_imagem; ?>" class="img-pagina" alt="Página <?php echo $pagina_atual; ?>">
    </div>

    <div class="controls">
        <?php if($pagina_atual > 1): ?>
            <a href="?id=<?php echo $id; ?>&pg=<?php echo $pagina_atual - 1; ?>" class="btn">‹ Anterior</a>
        <?php else: ?>
            <span class="btn btn-off">‹ Anterior</span>
        <?php endif; ?>

        <span style="font-size: 14px;">Página <?php echo $pagina_atual; ?> de <?php echo $total; ?></span>

        <?php if($pagina_atual < $total): ?>
            <a href="?id=<?php echo $id; ?>&pg=<?php echo $pagina_atual + 1; ?>" class="btn">Próxima ›</a>
        <?php else: ?>
            <span class="btn btn-off">Próxima ›</span>
        <?php endif; ?>
    </div>

</body>
</html>