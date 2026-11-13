<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; }
  html, body { margin: 0; background: #fefcef; color: #1a2332; font-family: 'Poppins', system-ui, sans-serif; min-height: 100dvh; -webkit-font-smoothing: antialiased; overflow-x: clip; }
  main { max-width: 480px; margin: 0 auto; padding: clamp(48px, 10vh, 96px) calc(18px + env(safe-area-inset-right)) 60px calc(18px + env(safe-area-inset-left)); text-align: center; }
  .kicker { margin: 0 0 10px; font-size: 15px; font-weight: 500; color: #03617A; }
  h1 { margin: 0 0 12px; font-family: 'Cormorant Garamond', serif; font-size: 42px; font-weight: 500; letter-spacing: -.02em; line-height: 1.1; }
  .lede { margin: 0 auto 28px; font-size: 16px; line-height: 1.6; color: #4a5568; text-wrap: balance; }
  .card { margin: 0 auto; padding: 24px 22px; border: 1px solid rgba(26,35,50,.12); border-radius: 12px; background: #fff; text-align: left; }
  .card + .card { margin-top: 16px; }
  .card h2 { margin: 0 0 6px; font-size: 18px; font-weight: 600; }
  .card p { margin: 0; font-size: 15px; line-height: 1.6; color: #4a5568; }
  label { display: block; margin: 16px 0 6px; font-size: 15px; font-weight: 500; }
  input[type=tel] { width: 100%; padding: 13px 14px; font: 400 17px 'Poppins', system-ui, sans-serif; color: #1a2332; background: #fff; border: 1px solid rgba(26,35,50,.2); border-radius: 8px; }
  input[type=tel]:focus { outline: 2px solid #03617A; outline-offset: 1px; }
  .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; margin-top: 14px; padding: 0 20px; border: 0; border-radius: 6px; background: #03617A; color: #fff; font: 500 16px 'Poppins', system-ui, sans-serif; text-decoration: none; cursor: pointer; }
  .btn.wide { width: 100%; }
  .btn.ghost { background: transparent; color: #03617A; border: 1px solid rgba(3,97,122,.4); }
  .ok  { margin: 0 0 16px; padding: 12px 14px; background: #ecf7f0; border-radius: 8px; color: #1e5b2f; font-size: 15px; line-height: 1.5; text-align: left; }
  .err { margin: 0 0 16px; padding: 12px 14px; background: #fef0ed; border-radius: 8px; color: #8a2a10; font-size: 15px; line-height: 1.5; text-align: left; }
  .hp { position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0; }
</style>
