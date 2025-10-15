# Configuración de Nginx/OpenResty para Genesis REST API

## Problema
Error `415 Unsupported Media Type` al hacer peticiones POST/PUT con `Content-Type: application/json` a la API REST.

## Solución

Agregar las siguientes directivas a la configuración de nginx/openresty del servidor en producción.

### Opción 1: Configuración en bloque server

```nginx
server {
    # ... otras configuraciones ...
    
    # Permitir Content-Type application/json
    location ~ ^/genesis/wp-json/ {
        # Headers CORS
        add_header 'Access-Control-Allow-Origin' '*' always;
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
        add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization, X-WP-Nonce, X-Requested-With' always;
        add_header 'Access-Control-Max-Age' 3600 always;
        
        # Responder a peticiones OPTIONS (preflight)
        if ($request_method = 'OPTIONS') {
            add_header 'Access-Control-Allow-Origin' '*' always;
            add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
            add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization, X-WP-Nonce, X-Requested-With' always;
            add_header 'Content-Type' 'text/plain; charset=utf-8';
            add_header 'Content-Length' 0;
            return 204;
        }
        
        # Pasar la petición a PHP-FPM
        try_files $uri $uri/ /genesis/index.php?$args;
    }
}
```

### Opción 2: Si usas cPanel con nginx (proxy)

En cPanel, generalmente nginx actúa como proxy reverso frente a Apache. En este caso:

1. Ir a cPanel → **Nginx Configuration** (o similar)
2. Agregar en la sección de configuración personalizada:

```nginx
location ~ ^/genesis/wp-json/ {
    proxy_set_header Content-Type $http_content_type;
    proxy_pass_header Content-Type;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header Host $host;
}
```

### Opción 3: Verificar Web Application Firewall (WAF)

Si el servidor tiene ModSecurity o un WAF activo:

1. Verificar logs en `/var/log/modsec_audit.log` o similar
2. Agregar excepción para `/genesis/wp-json/` en las reglas del WAF
3. En cPanel: **ModSecurity** → **Manage Rules** → Deshabilitar reglas que bloquean JSON

### Opción 4: PHP-FPM y FastCGI

Si el error viene de PHP-FPM:

```nginx
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php-fpm.sock;
    fastcgi_param CONTENT_TYPE $content_type;
    fastcgi_param CONTENT_LENGTH $content_length;
    include fastcgi_params;
}
```

## Verificación

Después de aplicar los cambios, probar con curl:

```bash
curl -X POST 'https://emmausdigital.com/genesis/wp-json/plg-genesis/v1/auth/nonce' \
  -H 'Content-Type: application/json' \
  -H 'Cookie: wordpress_logged_in_xxx=...' \
  -d '{}'
```

Debe retornar JSON, no HTML con error 415.

## Contacto con hosting

Si no tienes acceso directo a la configuración de nginx, contacta con el soporte del hosting y proporciona:

1. La URL exacta que falla: `https://emmausdigital.com/genesis/wp-json/plg-genesis/v1/...`
2. El error: `415 Unsupported Media Type`
3. Solicitud: "Necesito que permitan peticiones con Content-Type: application/json a las rutas /genesis/wp-json/*"

