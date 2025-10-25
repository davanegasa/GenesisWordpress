<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Iniciar sesión — Emmaus Digital</title>
  <meta name="color-scheme" content="light dark" />
  <style>
    :root{
      --bg-1:#0f172a; /* slate-900 */
      --bg-2:#111827; /* gray-900 */
      --card:#0b1222cc; /* glass */
      --txt:#e5e7eb;   /* gray-200 */
      --muted:#9ca3af; /* gray-400 */
      --brand-1:#2563eb; /* indigo-600 */
      --brand-2:#0ea5e9; /* sky-500 */
      --danger:#ef4444; /* red-500 */
      --success:#10b981;/* emerald-500 */
      --ring: 0 0 0 4px rgba(14,165,233,.18);
      --radius: 16px;
    }
    @media (prefers-color-scheme: light){
      :root{
        --bg-1:#f1f5f9; /* slate-100 */
        --bg-2:#e2e8f0; /* slate-200 */
        --card:#ffffffcc;
        --txt:#0f172a;
        --muted:#475569;
      }
    }
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%}
    body{
      font: 16px/1.6 system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji","Segoe UI Emoji";
      color:var(--txt);
      display:grid;
      grid-template-columns: 1fr 1fr;
      background: radial-gradient(1200px 800px at 10% 10%, var(--brand-2) 0%, transparent 60%),
                  radial-gradient(1000px 600px at 90% 90%, var(--brand-1) 0%, transparent 60%),
                  linear-gradient(135deg, var(--bg-1), var(--bg-2));
      overflow:hidden;
    }
    /* Lado izquierdo / branding */
    .left{
      position:relative;
      display:flex;align-items:center;justify-content:center;
      padding:64px; isolation:isolate;
    }
    .left .halo{position:absolute; inset:-20%;
      background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.08), transparent 55%),
                  radial-gradient(circle at 70% 70%, rgba(255,255,255,.06), transparent 60%);
      filter: blur(10px);
      z-index:-1;
      animation: float 20s ease-in-out infinite alternate;
    }
    @media (prefers-reduced-motion: reduce){ .left .halo{animation:none} }
    @keyframes float{ from{transform:translateY(-2%)} to{transform:translateY(2%)} }
    .brand{
      max-width:520px;text-align:center;
    }
    .brand h1{font-size: clamp(28px,4vw,48px);letter-spacing:-.02em;font-weight:800;margin-bottom:16px;text-wrap:balance}
    .brand p{font-size: clamp(15px,2vw,18px);opacity:.9}

    /* Lado derecho / formulario */
    .right{display:flex;align-items:center;justify-content:center;padding:56px}
    .card{
      width:min(100%, 440px);
      border-radius: var(--radius);
      backdrop-filter: saturate(160%) blur(10px);
      background: var(--card);
      box-shadow: 0 20px 60px rgba(0,0,0,.35);
      padding: 32px 28px;
      border: 1px solid rgba(255,255,255,.08);
    }
    .header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
    .header h2{font-size:28px;font-weight:700}
    .mode-toggle{appearance:none;border:1px solid rgba(255,255,255,.15);border-radius:999px;padding:6px 10px;background:transparent;color:var(--muted);cursor:pointer}

    .stack{display:grid;gap:16px;margin-top:12px}
    label{font-size:13px;color:var(--muted);font-weight:600}
    .field{position:relative}
    .input{
      width:100%;border-radius:12px;border:2px solid rgba(255,255,255,.1);
      background: rgba(255,255,255,.06);
      color:inherit;padding:14px 44px 14px 42px;font-size:16px;transition:.2s ease; outline: none;
    }
    .input::placeholder{color:rgba(255,255,255,.45)}
    .input:focus{border-color:var(--brand-2); box-shadow: var(--ring); background: rgba(255,255,255,.1)}

    .icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);opacity:.8}
    .reveal{position:absolute;right:10px;top:50%;transform:translateY(-50%);border:none;background:transparent;cursor:pointer;color:var(--muted)}

    .row{display:flex;align-items:center;justify-content:space-between;margin-top:4px}
    .checkbox{display:flex;align-items:center;gap:8px}
    .checkbox input{width:18px;height:18px;accent-color: var(--brand-2)}

    .btn{width:100%;margin-top:8px;border:none;border-radius:12px;padding:14px 18px;
      background: linear-gradient(135deg, var(--brand-2), var(--brand-1)); color:white;font-weight:700;letter-spacing:.3px;cursor:pointer;
      box-shadow: 0 10px 24px rgba(14,165,233,.4);transition: transform .15s ease, box-shadow .15s ease; text-transform: uppercase; font-size:15px;
    }
    .btn:hover{transform:translateY(-2px); box-shadow: 0 16px 34px rgba(14,165,233,.5)}
    .btn[disabled]{opacity:.65; cursor:not-allowed; transform:none; box-shadow:none}

    .links{display:flex;justify-content:center;gap:8px;margin-top:14px;flex-wrap:wrap}
    .links a{color:var(--muted);text-decoration:none;font-weight:600;padding:8px 10px;border-radius:8px}
    .links a:hover{background:rgba(255,255,255,.08); color:inherit}

    .alert{border-left:4px solid;padding:12px 14px;border-radius:12px;margin:8px 0;font-weight:600}
    .alert.error{border-color:var(--danger);background:rgba(239,68,68,.12);color:#fecaca}
    .alert.ok{border-color:var(--success);background:rgba(16,185,129,.12);color:#a7f3d0}
    .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}

    .caps{font-size:12px;color:#fde68a;margin-top:6px;display:none}

    /* Responsive */
    @media (max-width: 1000px){ body{grid-template-columns: 1fr} .left{display:none} .right{padding:32px} }
  </style>
</head>
<body>
  <!-- Lado branding -->
  <section class="left" aria-hidden="true">
    <div class="halo" ></div>
    <div class="brand">
      <svg width="80" height="80" viewBox="0 0 24 24" fill="none" aria-hidden="true" style="margin-bottom:12px;opacity:.95">
        <path d="M12 2l7 4v12l-7 4-7-4V6l7-4z" stroke="currentColor" stroke-width="1.2"/>
        <path d="M12 7v10M8 9v6M16 9v6" stroke="currentColor" stroke-width="1.2"/>
      </svg>
      <h1>Emmaus Colombia</h1>
      <p>Gestiona tu progreso académico de forma simple y efectiva.</p>
    </div>
  </section>

  <!-- Lado formulario -->
  <main class="right">
    <div class="card" role="region" aria-labelledby="login-title">
      <div class="header">
        <h2 id="login-title">Iniciar sesión</h2>
        <button class="mode-toggle" id="modeToggle" type="button" aria-pressed="false" title="Cambiar tema">🌗</button>
      </div>

      <!-- Mensajes del servidor -->
      <div id="server-messages" aria-live="polite">
      <?php if (isset($_GET['loggedout']) && $_GET['loggedout'] === 'true'): ?>
        <div class="alert ok">Has cerrado sesión correctamente.</div>
      <?php endif; ?>

      <?php if (isset($_GET['error'])): ?>
        <div class="alert error">
          <?php
            if ($_GET['error'] === 'invalid_username') {
              echo 'Usuario o contraseña incorrectos.';
            } elseif ($_GET['error'] === 'empty_username' || $_GET['error'] === 'empty_password') {
              echo 'Por favor, completa todos los campos.';
            } else {
              echo 'Error al iniciar sesión. Inténtalo de nuevo.';
            }
          ?>
        </div>
      <?php endif; ?>
      </div>

      <form name="loginform" id="loginform" action="<?php echo esc_url(site_url('wp-login.php','login_post')); ?>" method="post" novalidate>
        <div class="stack">
          <div class="field">
            <label for="user_login">Usuario o correo electrónico</label>
            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a5 5 0 1 0-5-5 5 5 0 0 0 5 5zm0 2c-5 0-8 2.5-8 5v1h16v-1c0-2.5-3-5-8-5z" stroke="currentColor" stroke-width="1.2"/></svg>
            <input class="input" type="text" name="log" id="user_login" placeholder="Ingresa tu usuario" required autocomplete="username" autofocus />
          </div>

          <div class="field">
            <label for="user_pass">Contraseña</label>
            <svg class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M17 11h-1V8a4 4 0 0 0-8 0v3H7a1 1 0 0 0-1 1v7a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-7a1 1 0 0 0-1-1Zm-7-3a2 2 0 1 1 4 0v3H10Z" stroke="currentColor" stroke-width="1.2"/></svg>
            <input class="input" type="password" name="pwd" id="user_pass" placeholder="Ingresa tu contraseña" required autocomplete="current-password" aria-describedby="caps" />
            <button class="reveal" type="button" id="reveal" aria-label="Mostrar u ocultar contraseña">👁️</button>
            <div id="caps" class="caps" role="status" aria-live="polite">Mayúsculas activadas</div>
          </div>

          <div class="row">
            <label class="checkbox">
              <input type="checkbox" name="rememberme" id="rememberme" value="forever" />
              <span>Recordarme</span>
            </label>
            <!-- Coloca aquí un shortcode de SSO si usas plugin (opcional) -->
            <?php /* echo do_shortcode('[nextend_social_login provider="google"]'); */ ?>
          </div>

          <button type="submit" name="wp-submit" id="wp-submit" class="btn">Iniciar sesión</button>

          <?php
            // Redirección post-login
            $dashboard_v2_page = get_page_by_path('dashboard-v2');
            $redirect_to = $dashboard_v2_page ? get_permalink($dashboard_v2_page->ID) : admin_url();
          ?>
          <input type="hidden" name="redirect_to" value="<?php echo esc_attr($redirect_to); ?>" />
          <input type="hidden" name="testcookie" value="1" />
          <?php do_action('login_form'); ?>
        </div>
      </form>

      <nav class="links" aria-label="Opciones adicionales">
        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">¿Olvidaste tu contraseña?</a>
        <a href="<?php echo esc_url(home_url('/')); ?>">Volver al inicio</a>
      </nav>
    </div>
  </main>

  <script>
    // Botón de carga + bloqueo
    const form = document.getElementById('loginform');
    const btn  = document.getElementById('wp-submit');
    form.addEventListener('submit', ()=>{ btn.textContent='Iniciando…'; btn.disabled=true; });

    // Mostrar/ocultar contraseña
    const pass = document.getElementById('user_pass');
    const toggle = document.getElementById('reveal');
    toggle.addEventListener('click', ()=>{
      const shown = pass.getAttribute('type') === 'text';
      pass.setAttribute('type', shown ? 'password' : 'text');
      toggle.setAttribute('aria-pressed', String(!shown));
    });

    // Aviso de Mayúsculas
    const caps = document.getElementById('caps');
    pass.addEventListener('keydown', (e)=>{ caps.style.display = e.getModifierState && e.getModifierState('CapsLock') ? 'block' : 'none'; });
    pass.addEventListener('keyup',   (e)=>{ caps.style.display = e.getModifierState && e.getModifierState('CapsLock') ? 'block' : 'none'; });

    // Tema claro/oscuro persistente
    const modeBtn = document.getElementById('modeToggle');
    const LS_KEY = 'emmaus.theme';
    const setTheme = (m)=>{ document.documentElement.dataset.theme = m; modeBtn.setAttribute('aria-pressed', m==='dark'); };
    // Lee preferencia
    const saved = localStorage.getItem(LS_KEY);
    if(saved){ setTheme(saved); }
    modeBtn.addEventListener('click', ()=>{
      const cur = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
      setTheme(cur); localStorage.setItem(LS_KEY, cur);
    });
  </script>
</body>
</html>
