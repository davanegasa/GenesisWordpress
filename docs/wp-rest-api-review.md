# Revisión de la API REST nativa de WordPress

Esta guía resume los pasos sugeridos para validar la API REST nativa de WordPress y generar una especificación OpenAPI (Swagger) que pueda servir como insumo para la construcción de la app Android.

## 1. Verificar acceso básico a la API
1. Abre un navegador o cliente HTTP (Postman, curl) y visita `https://tu-dominio/wp-json/`.
2. Confirma que recibes la respuesta JSON con la lista de namespaces y rutas disponibles.
3. Verifica el namespace principal `wp/v2` en `https://tu-dominio/wp-json/wp/v2/` para validar que los endpoints básicos (posts, pages, categories, etc.) responden correctamente.

## 2. Identificar endpoints requeridos
1. Lista los recursos que usará la app (ej. posts, pages, media, usuarios autenticados, taxonomías).
2. Para cada recurso, prueba los endpoints estándar:
   - `GET /wp-json/wp/v2/posts`
   - `GET /wp-json/wp/v2/posts/{id}`
   - `GET /wp-json/wp/v2/pages`
   - `GET /wp-json/wp/v2/media`
   - `GET /wp-json/wp/v2/categories`
   - `GET /wp-json/wp/v2/tags`
   - `GET /wp-json/wp/v2/users`
3. Si la app requiere operaciones de escritura (POST/PUT/DELETE), valida que el mecanismo de autenticación elegido (por ejemplo, JWT) funcione y permita acceder a los endpoints protegidos.

## 3. Revisar endpoints personalizados
1. Si tu WordPress incluye plugins o código propio que registre rutas adicionales con `register_rest_route`, identifícalas en el índice (`/wp-json/`).
2. Documenta cada endpoint personalizado: parámetros, payloads de entrada/salida, códigos de estado.

## 4. Generar el documento OpenAPI
1. Utiliza la especificación base incluida en [`docs/wp-rest-openapi.yaml`](./wp-rest-openapi.yaml) como plantilla inicial.
2. Ajusta el bloque `servers` con la URL real del sitio.
3. Añade o elimina endpoints según lo que esté disponible en tu instalación (por ejemplo, rutas personalizadas o eliminadas por plugins).
4. Valida la sintaxis con una herramienta como [Swagger Editor](https://editor.swagger.io/), [Speccy](https://github.com/wework/speccy) o `openapi-cli`.

## 5. Publicar y consumir la documentación
1. Monta Swagger UI o Redoc para exponer la documentación. Puedes alojarla en GitHub Pages, Netlify o el mismo servidor donde corra WordPress.
2. Usa la especificación para generar SDKs o clientes (por ejemplo, `openapi-generator-cli`) que faciliten la integración con la app Android.
3. Mantén la especificación actualizada cuando cambien los endpoints, parámetros o esquemas.

## 6. Validación inicial recomendada
- **Disponibilidad:** Cada endpoint crítico responde con código HTTP 200/204 y payload esperado.
- **Autenticación:** Se obtienen tokens válidos y los endpoints protegidos responden con código 200. Errores correctamente devuelven 401/403.
- **Paginación:** Los endpoints que devuelven colecciones incluyen cabeceras `X-WP-Total` y `X-WP-TotalPages`. Ajusta `per_page` según sea necesario.
- **Performance:** Mide tiempos de respuesta para los recursos más usados y habilita mecanismos de caché si es necesario.
- **Seguridad:** Confirma que solo se expone la información necesaria (oculta metadatos sensibles) y se aplica HTTPS.

Con esta verificación y la especificación OpenAPI podrás avanzar en la construcción de la APK consumiendo el backend de WordPress con mayor confianza.
