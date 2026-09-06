<?php
/**
 * Vista de Nosotros
 * Identidad de NIKAN: misión, visión y valores del museo digital.
 */
?>

<section class="nos-section">
    <div class="nos-container">
        <div class="nos-texto">
            <div class="nos-tag">
                <img src="assets/nosotros.svg" alt="Nosotros" class="nos-icono">
                <span>NOSOTROS</span>
            </div>
            <h1>EL ESPÍRITU<br>DE NIKAN</h1>
            <p>
                Somos una plataforma digital que acerca el legado cultural de
                Nicaragua a las nuevas generaciones.
            </p>
        </div>
        <div class="nos-imagen">
            <div class="nos-marco-deco"></div>
            <img src="assets/nosotross.svg" alt="Nosotros" class="nos-emblema">
            <span class="nos-sombras"></span>
        </div>
    </div>
</section>

<section class="nos-seccion">
    <div class="nos-seccion-titulo">
        <span>Una plataforma con alma</span>
        <h2>La identidad de Nikan</h2>
        <div class="nos-linea"></div>
    </div>
    <div class="nos-inner">
        <article class="nos-card nos-mision">
            <div class="nos-card-etiqueta"><span>MISIÓN</span></div>
            <h2>Qué nos mueve</h2>
            <p>
                Conectar a las nuevas generaciones con la riqueza cultural de Nicaragua
                mediante una plataforma digital interactiva que les facilite descubrir,
                aprender y explorar nuestro patrimonio.
            </p>
        </article>

        <article class="nos-card nos-vision">
            <div class="nos-card-etiqueta"><span>VISIÓN</span></div>
            <h2>Hacia dónde vamos</h2>
            <p>
                Convertirse en la primera plataforma digital de referencia nacional para
                descubrir, preservar, compartir y vivir la cultura nicaragüense, integrando
                en un solo espacio nuestro patrimonio, museos, autores e historias.
            </p>
        </article>

        <article class="nos-card nos-concepto">
            <div class="nos-card-etiqueta"><span>CONCEPTO CENTRAL</span></div>
            <h2>"Hacer visible lo que nos hace únicos"</h2>
            <p>
                Nikan nace para hacer visible la riqueza cultural de nuestro país, ya que esta
                muchas veces se encuentra dispersa o es desconocida por las nuevas generaciones.
                Por tal razón, buscamos transformar esa cultura en una experiencia digital
                cercana, interactiva y atractiva (mediante un museo interactivo).
            </p>
        </article>

        <article class="nos-card nos-arquetipo">
            <div class="nos-card-etiqueta"><span>ARQUETIPO</span></div>
            <h2>“El explorador”</h2>
            <p>
                Porque Nikan, y la mascota de Nikan, invitan a los usuarios a ir más allá de lo
                que ya conocen, y descubrir historias, personajes, arte y elementos de nuestra
                cultura que forman parte de nuestra identidad.
            </p>
        </article>

        <article class="nos-card nos-personalidad">
            <div class="nos-card-etiqueta"><span>PERSONALIDAD</span></div>
            <h2>Cómo es Nikan</h2>
            <div class="nos-rasgos">
                <div class="nos-rasgo">
                    <div class="nos-rasgo-titulo"><span>Curiosa</span><span class="nos-rasgo-num">01</span></div>
                    <p>Busca descubrir nuevas historias y elementos de nuestra cultura.</p>
                </div>
                <div class="nos-rasgo">
                    <div class="nos-rasgo-titulo"><span>Cercana</span><span class="nos-rasgo-num">02</span></div>
                    <p>Comparte las expresiones culturales de una manera sencilla, amigable y accesible.</p>
                </div>
                <div class="nos-rasgo">
                    <div class="nos-rasgo-titulo"><span>Dinámica</span><span class="nos-rasgo-num">03</span></div>
                    <p>La información cultural se transforma en experiencias interactivas.</p>
                </div>
                <div class="nos-rasgo">
                    <div class="nos-rasgo-titulo"><span>Auténtica</span><span class="nos-rasgo-num">04</span></div>
                    <p>El centro de todo son las historias y las expresiones culturales de nuestro país.</p>
                </div>
                <div class="nos-rasgo">
                    <div class="nos-rasgo-titulo"><span>Inspiradora</span><span class="nos-rasgo-num">05</span></div>
                    <p>Busca despertar un espíritu de curiosidad, orgullo patrio, y conexión con nuestra cultura.</p>
                </div>
            </div>
        </article>
    </div>
</section>

<style>
    .nos-section {
        background:
            radial-gradient(ellipse at 25% 40%, rgba(255, 244, 222, 0.9), transparent 60%),
            radial-gradient(ellipse at 75% 45%, rgba(255, 244, 222, 0.7), transparent 55%),
            linear-gradient(135deg, #f6efe3 0%, #efe4d0 45%, #e7d8bd 100%);
        padding: 190px 80px 0;
        overflow: hidden;
        position: relative;
        z-index: 2;
    }
    .nos-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 40px;
        max-width: 1200px;
        margin: 0 auto;
        min-height: 55vh;
        padding-bottom: 60px;
    }
    .nos-texto {
        flex: 1;
        max-width: 640px;
        z-index: 2;
    }
    .nos-tag {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        font-size: 15px;
        font-weight: 800;
        letter-spacing: 4px;
        color: #4a3b22;
    }
    .nos-icono {
        width: 44px;
        height: 44px;
        object-fit: contain;
        filter: invert(1) brightness(0);
    }
    .nos-texto h1 {
        font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
        font-size: 82px;
        color: #C6372E;
        margin: 0 0 24px;
        line-height: 1.05;
        letter-spacing: 3px;
        text-shadow: 0 2px 2px rgba(90, 60, 20, 0.25);
    }
    .nos-texto p {
        font-family: 'Rustica', serif;
        font-size: 22px;
        color: #4a3b22;
        line-height: 1.7;
        margin: 0;
        max-width: 560px;
    }
    .nos-imagen {
        position: relative;
        text-align: center;
        align-self: flex-end;
        margin-bottom: -140px;
        transform: translateY(-60px);
        padding: 24px;
    }
    .nos-marco-deco {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 76%;
        height: 88%;
        transform: translate(-50%, -52%);
        border: none;
        border-radius: 8px;
    }
    .nos-emblema {
        position: relative;
        z-index: 1;
        width: 560px;
        height: auto;
        display: block;
    }
    .nos-sombras {
        position: absolute;
        bottom: 6%;
        left: 50%;
        transform: translateX(-50%);
        width: 60%;
        height: 30px;
        background: radial-gradient(ellipse at center, rgba(90, 60, 20, 0.35), transparent 70%);
        filter: blur(6px);
    }

    .nos-seccion {
        background:
            linear-gradient(180deg, #e7d8bd 0%, #d9c6a4 100%);
        padding: 80px 24px 100px;
        position: relative;
        z-index: 2;
    }
    .nos-seccion::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #C6372E 0%, #c9a94f 35%, #0a7a4b 70%, #7c3e2e 100%);
    }
    .nos-seccion-titulo {
        text-align: center;
        margin-bottom: 48px;
    }
    .nos-seccion-titulo span {
        display: inline-block;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 6px;
        color: #7c3e2e;
        text-transform: uppercase;
        margin-bottom: 10px;
    }
    .nos-seccion-titulo h2 {
        font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
        font-size: 40px;
        color: #3a2d18;
        margin: 0;
        letter-spacing: 2px;
    }
    .nos-seccion-titulo .nos-linea {
        width: 90px;
        height: 3px;
        background: #c9a94f;
        margin: 16px auto 0;
    }
    .nos-inner {
        max-width: 1100px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 36px;
    }
    .nos-card {
        position: relative;
        background: #f8f1e4;
        border: 1px solid rgba(150, 110, 60, 0.4);
        box-shadow: 0 0 0 4px #e2d3b4, 0 16px 40px rgba(90, 60, 20, 0.14);
        padding: 42px 44px;
        transition: transform .4s ease, box-shadow .4s ease;
        overflow: hidden;
    }
    .nos-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        height: 4px;
        width: 0;
        background: linear-gradient(90deg, var(--accent, #C6372E), #c9a94f);
        transition: width .45s ease;
    }
    .nos-card::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--accent, #C6372E);
        opacity: 0.85;
    }
    .nos-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 0 0 4px #c9a94f, 0 26px 55px rgba(90, 60, 20, 0.24);
    }
    .nos-card:hover::before { width: 100%; }
    .nos-mision { --accent: #C6372E; }
    .nos-vision { --accent: #0a7a4b; }
    .nos-concepto { --accent: #c9a94f; }
    .nos-arquetipo { --accent: #7c3e2e; }
    .nos-personalidad { --accent: #C6372E; grid-column: 1 / -1; }
    .nos-card-etiqueta {
        display: inline-block;
        margin-bottom: 20px;
        background: var(--accent, #C6372E);
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 4px;
        padding: 8px 20px;
        border-radius: 0;
        box-shadow: inset 0 -3px 0 rgba(0, 0, 0, 0.22);
    }
    .nos-card h2 {
        font-size: 30px;
        color: #3a2d18;
        margin: 0 0 16px;
        letter-spacing: 1px;
    }
    .nos-card h2::after {
        content: "";
        display: block;
        width: 56px;
        height: 2px;
        background: var(--accent, #C6372E);
        margin-top: 12px;
    }
    .nos-card p {
        font-size: 18px;
        line-height: 1.8;
        color: #5a4a2f;
        margin: 0;
        text-align: justify;
    }
    .nos-rasgos {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-top: 12px;
    }
    .nos-rasgo {
        position: relative;
        background: #fffdf8;
        border: 1px solid rgba(150, 110, 60, 0.35);
        border-top: 3px solid var(--accent, #C6372E);
        border-radius: 0;
        padding: 20px 22px;
        transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease;
    }
    .nos-rasgo:hover {
        transform: translateY(-5px);
        border-top-color: #c9a94f;
        box-shadow: 0 12px 26px rgba(90, 60, 20, 0.18);
    }
    .nos-rasgo-titulo {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
    }
    .nos-rasgo-titulo span:first-child {
        font-size: 20px;
        font-weight: 900;
        color: #3a2d18;
        letter-spacing: 1px;
    }
    .nos-rasgo-num {
        font-size: 12px;
        font-weight: 900;
        color: #fff;
        background: var(--accent, #C6372E);
        padding: 3px 10px;
        letter-spacing: 1px;
    }
    .nos-rasgo p {
        font-size: 15px;
        line-height: 1.65;
        color: #5a4a2f;
        margin: 0;
    }

    @media (max-width: 900px) {
        .nos-section { padding: 160px 24px 0; }
        .nos-container { flex-direction: column; gap: 24px; }
        .nos-imagen { transform: none; align-self: center; margin-bottom: -40px; }
        .nos-texto h1 { font-size: 48px; }
        .nos-texto p { font-size: 17px; }
        .nos-inner { grid-template-columns: 1fr; gap: 24px; }
        .nos-personalidad { grid-column: auto; }
        .nos-card { padding: 32px 28px; }
        .nos-seccion-titulo h2 { font-size: 30px; }
    }
</style>