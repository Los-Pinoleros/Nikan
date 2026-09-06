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
            <img src="assets/nosotros.svg" alt="Nosotros" class="nos-emblema">
            <span class="nos-sombras"></span>
        </div>
    </div>
</section>

<section class="nos-seccion">
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
            <div class="nos-card-etiqueta nos-card-etiqueta--vision"><span>VISIÓN</span></div>
            <h2>Hacia dónde vamos</h2>
            <p>
                Convertirse en la primera plataforma digital de referencia nacional para
                descubrir, preservar, compartir y vivir la cultura nicaragüense, integrando
                en un solo espacio nuestro patrimonio, museos, autores e historias.
            </p>
        </article>

        <article class="nos-card nos-concepto">
            <div class="nos-card-etiqueta nos-card-etiqueta--concepto"><span>CONCEPTO CENTRAL</span></div>
            <h2>"Hacer visible lo que nos hace únicos"</h2>
            <p>
                Nikan nace para hacer visible la riqueza cultural de nuestro país, ya que esta
                muchas veces se encuentra dispersa o es desconocida por las nuevas generaciones.
                Por tal razón, buscamos transformar esa cultura en una experiencia digital
                cercana, interactiva y atractiva (mediante un museo interactivo).
            </p>
        </article>

        <article class="nos-card nos-arquetipo">
            <div class="nos-card-etiqueta nos-card-etiqueta--arquetipo"><span>ARQUETIPO</span></div>
            <h2>“El explorador”</h2>
            <p>
                Porque Nikan, y la mascota de Nikan, invitan a los usuarios a ir más allá de lo
                que ya conocen, y descubrir historias, personajes, arte y elementos de nuestra
                cultura que forman parte de nuestra identidad.
            </p>
        </article>

        <article class="nos-card nos-personalidad">
            <div class="nos-card-etiqueta nos-card-etiqueta--personalidad"><span>PERSONALIDAD</span></div>
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
        font-size: 82px;
        color: #C6372E;
        margin: 0 0 24px;
        line-height: 1.05;
        letter-spacing: 3px;
        text-shadow: 0 2px 2px rgba(90, 60, 20, 0.25);
    }
    .nos-texto p {
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
        border: 2px solid #c9a94f;
        border-radius: 8px;
    }
    .nos-emblema {
        position: relative;
        z-index: 1;
        width: 300px;
        max-width: 34vw;
        filter: drop-shadow(0 10px 16px rgba(90, 60, 20, 0.25));
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
        background: linear-gradient(135deg, #e7d8bd 0%, #d9c6a4 100%);
        padding: 60px 24px;
        position: relative;
        z-index: 2;
    }
    .nos-inner {
        max-width: 900px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .nos-card {
        background: #f6efe3;
        border-radius: 16px;
        padding: 32px 36px;
        box-shadow: 0 10px 30px rgba(90, 60, 20, 0.18);
        border-left: 6px solid #C6372E;
        transition: transform .3s ease;
    }
    .nos-card:hover { transform: translateY(-4px); }
    .nos-vision { border-left-color: #0a7a4b; }
    .nos-concepto { border-left-color: #c9a94f; }
    .nos-arquetipo { border-left-color: #7c3e2e; }
    .nos-card-etiqueta {
        display: inline-block;
        margin-bottom: 14px;
        background: #C6372E;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 3px;
        padding: 6px 16px;
        border-radius: 999px;
    }
    .nos-card-etiqueta--vision {
        background: #0a7a4b;
    }
    .nos-card-etiqueta--concepto {
        background: #c9a94f;
    }
    .nos-card-etiqueta--arquetipo {
        background: #7c3e2e;
    }
    .nos-card-etiqueta--personalidad {
        background: #C6372E;
    }
    .nos-personalidad { border-left-color: #C6372E; }
    .nos-rasgos {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        margin-top: 8px;
    }
    .nos-rasgo {
        background: #fff;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 4px 12px rgba(90, 60, 20, 0.1);
        border-bottom: 3px solid #e7d8bd;
        transition: transform .3s ease, box-shadow .3s ease;
    }
    .nos-rasgo:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(90, 60, 20, 0.16);
    }
    .nos-rasgo-titulo {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }
    .nos-rasgo-titulo span:first-child {
        font-size: 20px;
        font-weight: 900;
        color: #4a3b22;
        letter-spacing: 1px;
    }
    .nos-rasgo-num {
        font-size: 13px;
        font-weight: 900;
        color: #C6372E;
    }
    .nos-rasgo p {
        font-size: 15px;
        line-height: 1.6;
        color: #5a4a2f;
        margin: 0;
    }
    .nos-card h2 {
        font-size: 30px;
        color: #4a3b22;
        margin: 0 0 12px;
    }
    .nos-card p {
        font-size: 18px;
        line-height: 1.75;
        color: #5a4a2f;
        margin: 0;
        text-align: justify;
    }

    @media (max-width: 900px) {
        .nos-section { padding: 160px 24px 0; }
        .nos-container { flex-direction: column; gap: 24px; }
        .nos-imagen { transform: none; align-self: center; margin-bottom: -40px; }
        .nos-texto h1 { font-size: 48px; }
        .nos-texto p { font-size: 17px; }
    }
</style>