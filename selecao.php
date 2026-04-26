<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ghostnet — Seleção</title>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Sora', sans-serif; background: #090A0F; color: #e8e4d8; display: flex; flex-direction: column; align-items: center; min-height: 100vh; padding: 40px 20px; overflow-x: hidden; }
        
        #stars-container { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(ellipse at bottom, #1B2735 0%, #090A0F 100%); z-index: -2; overflow: hidden; }
        #stars, #stars2 { position: absolute; top: 0; left: 0; background: transparent; }
        @keyframes animStar { from { transform: translateY(0px); } to { transform: translateY(-2000px); } }
        #stars { width: 1px; height: 1px; animation: animStar 50s linear infinite; }
        #stars2 { width: 2px; height: 2px; animation: animStar 100s linear infinite; }
        #stars:after, #stars2:after { content: " "; position: absolute; top: 2000px; width: inherit; height: inherit; box-shadow: inherit; }

        h1 { font-size: 2.5rem; margin-bottom: 10px; background: linear-gradient(to right, #ef4444, #991b1b); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 0 0 15px rgba(239, 68, 68, 0.3); text-align: center; }
        p.subtitle { color: rgba(232,228,216,0.6); margin-bottom: 40px; text-align: center; }

        .tabs { display: flex; gap: 15px; margin-bottom: 30px; justify-content: center; }
        .tab-btn { padding: 14px 28px; border-radius: 999px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.05); color: #94a3b8; cursor: pointer; transition: 0.4s; font-weight: 600; backdrop-filter: blur(5px); display: flex; align-items: center; gap: 8px; }
        .tab-btn.active { background: #dc2626; color: #fff; border-color: #ef4444; box-shadow: 0 0 20px rgba(220, 38, 38, 0.4); transform: scale(1.05); }

        .card { 
            background: rgba(21, 21, 28, 0.6); backdrop-filter: blur(10px); 
            border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; 
            padding: 25px; transition: 0.3s; box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
            display: flex; flex-direction: column;
        }
        .card:hover { border-color: #ef4444; transform: translateY(-5px); box-shadow: 0 0 20px rgba(239, 68, 68, 0.2); }

        /* Estilo centralizado para o card global */
        .global-container { width: 100%; max-width: 1000px; margin-bottom: 30px; }
        .global-container .card { 
            border: 1px solid rgba(239, 68, 68, 0.5); background: rgba(239, 68, 68, 0.1); 
            text-align: center; align-items: center;
        }

        .card-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .global-container .card-header { flex-direction: column; gap: 10px; margin-bottom: 10px; }
        
        .icon { background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 10px; color: #ef4444; display: flex; align-items: center; justify-content: center; }

        .option-btn { display: flex; justify-content: space-between; align-items: center; background: rgba(13, 17, 28, 0.8); padding: 16px; border-radius: 12px; text-decoration: none; color: #e8e4d8; margin-bottom: 10px; border: 1px solid transparent; transition: 0.2s; font-size: 14px; width: 100%; box-sizing: border-box; }
        .option-btn:hover { background: #dc2626; border-color: #ef4444; }
        .option-btn span { color: #ef4444; font-weight: bold; }
        .option-btn:hover span { color: #fff; }

        /* Botão especial do SP Ação centralizado */
        .btn-sp-central { justify-content: center; gap: 15px; background: #dc2626 !important; font-weight: bold; }

        .grid { display: none; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; max-width: 1000px; width: 100%; }
        .grid.active { display: grid; }
        .btn-voltar { margin-top: 40px; text-decoration: none; color: rgba(232,228,216,0.4); font-size: 14px; }
    </style>
</head>
<body>
    <div id="stars-container"><div id="stars"></div><div id="stars2"></div></div>

    <h1>Material Didático</h1>
    <p class="subtitle">Escolha seu destino e comece a estudar.</p>

    <div class="tabs">
        <button class="tab-btn active" onclick="showTab('fundamental', this)">🔥 Ensino Fundamental</button>
        <button class="tab-btn" onclick="showTab('medio', this)">🚀 Ensino Médio</button>
    </div>

    <div class="global-container">
        <div class="card">
            <div class="card-header">
                <div class="icon" style="background: #ef4444; color: #fff; font-size: 20px;">✨</div>
                <h2 style="color:#ef4444; margin:0;">São Paulo em Ação</h2>
            </div>
            <p style="font-size:14px; color:rgba(255,255,255,0.6); margin-bottom:20px; max-width: 400px;">Material de apoio geral.</p>
            <a href="leitor.php?serie=SP Acao&cat=sp_acao" class="option-btn btn-sp-central">Acessar São Paulo em Ação ➔</a>
        </div>
    </div>

    <div id="fundamental" class="grid active">
        <?php $fundamental = ['6 Ano', '7 Ano', '8 Ano', '9 Ano']; 
        foreach ($fundamental as $s) { ?>
        <div class="card">
            <div class="card-header">
                <div class="icon">📄</div>
                <h3 style="margin:0;"><?php echo $s; ?></h3>
            </div>
            <a href="leitor.php?serie=<?php echo urlencode($s); ?>&cat=base" class="option-btn">Português & Matemática <span>➔</span></a>
            <a href="leitor.php?serie=<?php echo urlencode($s); ?>&cat=geral" class="option-btn">Ciências e Humanas <span>➔</span></a>
        </div>
        <?php } ?>
    </div>

    <div id="medio" class="grid">
        <?php $medio = ['1 Serie', '2 Serie', '3 Serie']; 
        foreach ($medio as $s) { ?>
        <div class="card">
            <div class="card-header">
                <div class="icon">🎓</div>
                <h3 style="margin:0;"><?php echo $s; ?> EM</h3>
            </div>
            <a href="leitor.php?serie=<?php echo urlencode($s); ?>&cat=base" class="option-btn">Linguagens & Matemática <span>➔</span></a>
            <a href="leitor.php?serie=<?php echo urlencode($s); ?>&cat=geral" class="option-btn">Ciências e Natureza <span>➔</span></a>
        </div>
        <?php } ?>
    </div>

    <a href="index.php" class="btn-voltar">← Voltar para o início</a>

    <script>
        function showTab(tabId, btn) {
            document.querySelectorAll('.grid').forEach(g => g.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            btn.classList.add('active');
        }
        function createStars(id, count) {
            const canvas = document.getElementById(id);
            let stars = "";
            for (let i = 0; i < count; i++) {
                stars += `${Math.random() * 2000}px ${Math.random() * 2000}px #FFF${i < count - 1 ? ',' : ''}`;
            }
            canvas.style.boxShadow = stars;
        }
        createStars('stars', 700); createStars('stars2', 200);
    </script>
</body>
</html>