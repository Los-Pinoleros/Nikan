<footer class="nikan-footer">
    <div class="nikan-footer__brand">NIKAN</div>
    <div class="nikan-footer__mark" aria-label="NIKAN 2026">
        <span>20</span>
        <img src="assets/leon.svg" alt="Logo NIKAN">
        <span>26</span>
    </div>
    <a class="nikan-footer__email" href="mailto:nikannicaragua@gmail.com">nikannicaragua@gmail.com</a>
</footer>

<style>
    .nikan-footer {
        box-sizing: border-box;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        align-items: center;
        gap: clamp(18px, 3vw, 42px);
        width: 100%;
        min-height: clamp(130px, 15vw, 175px);
        padding: clamp(24px, 4vw, 42px) clamp(20px, 7vw, 110px);
        color: #fff;
        background: #168c45;
        font-family: 'Montserrat', Arial, sans-serif;
    }
    .nikan-footer__brand {
        justify-self: start;
        font: 800 clamp(28px, 4vw, 48px)/1 'Nikan Felthgothic', 'Felthgothic', Arial, sans-serif;
        letter-spacing: 2px;
    }
    .nikan-footer__mark {
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 13px;
        color: #fff;
        font: 700 clamp(20px, 2.5vw, 30px)/1 'Alegreya', Georgia, serif;
    }
    .nikan-footer__mark img {
        display: block;
        width: clamp(72px, 8vw, 105px);
        height: auto;
        object-fit: contain;
    }
    .nikan-footer__email {
        min-width: 0;
        justify-self: end;
        max-width: 100%;
        color: #fff;
        font-size: clamp(11px, 1.2vw, 15px);
        letter-spacing: .3px;
        overflow-wrap: anywhere;
        text-decoration: none;
        transition: opacity .2s ease;
    }
    .nikan-footer__email:hover,
    .nikan-footer__email:focus-visible {
        opacity: .72;
    }
    @media (max-width: 680px) {
        .nikan-footer {
            grid-template-columns: 1fr;
            justify-items: center;
            gap: clamp(16px, 4vw, 24px);
            min-height: 0;
            padding: 34px max(20px, 7vw) 38px;
            text-align: center;
        }
        .nikan-footer__brand,
        .nikan-footer__email {
            justify-self: center;
        }
        .nikan-footer__brand { order: 1; }
        .nikan-footer__mark { order: 2; }
        .nikan-footer__email { order: 3; }
    }
    @media (max-width: 360px) {
        .nikan-footer {
            padding-right: 16px;
            padding-left: 16px;
        }
        .nikan-footer__mark { gap: 8px; }
        .nikan-footer__mark img { width: 70px; }
        .nikan-footer__email { font-size: 11px; }
    }
</style>

<script src="assets/nikan-anim.js?v=20261009"></script>
</body>
</html>