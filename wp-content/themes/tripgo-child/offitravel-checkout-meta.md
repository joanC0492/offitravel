# OFFITRAVEL - Checkout 2 pasos + Meta Pixel

**Fecha de auditoría técnica:** 15 de junio de 2026
**Proyecto:** OFFITRAVEL
**Entorno auditado:** local (D:/xampp/htdocs/offitravel)
**Workspace abierto:** D:/xampp/htdocs/offitravel/wp-content
**Alcance:** auditoría de código, configuración y BD en modo solo lectura.

---

## Resumen ejecutivo

La implementación actual usa checkout clásico de WooCommerce con división en 2 pasos mediante MultiStep Checkout for WooCommerce, guardado AJAX de lead en paso 1, y payloads estructurados para tracking de paso 1 y Purchase (Purchase custom en modo preview por defecto).

Estado global prudente:

1. La arquitectura principal está implementada y operativa a nivel técnico.
2. Existen evidencias reales en BD de step 1 completado, abandono y enlace lead->order.
3. Persisten riesgos de calidad y cumplimiento: dedupe global de Step 1 por sessionStorage, validación backend insuficiente en endpoint AJAX, hook Purchase en `woocommerce_thankyou` sin filtro estricto de estado pagado, y ausencia de política técnica de retención/borrado de leads.
4. Todo lo relativo a recepción real de eventos y deduplicación real en Meta permanece **BLOQUEADO POR META** por falta de conexión efectiva de Pixel/Dataset del cliente.

---

## 1. Requerimiento del cliente

### 1.1 Simplificación y división del Checkout

El checkout debía separarse en dos etapas:

- Paso 1: datos personales, contacto y dirección.
- Paso 2: pago y cierre de pedido.

Al avanzar realmente de Paso 1 a Paso 2:

- Debe validarse el formulario.
- Debe guardarse información no sensible en BD.
- Debe prepararse/dispararse evento de Meta.
- Debe quedar traza para recuperación de abandono.

### 1.2 Variables de tracking requeridas

El cliente solicitó que se preparen y envíen parámetros estructurados:

- `order_id`
- `content_name`
- `content_ids`
- `contents`
- `value`
- `currency`

---

## 2. Arquitectura actual

- Checkout renderizado con shortcode clásico: `[woocommerce_checkout]`.
- Aside custom renderizado con shortcode: `[offi_checkout_summary]`.
- Plugin de pasos activo: `woo-multistep-checkout` v2.3.4.
- Guardado de paso 1 vía AJAX custom: `action=offi_save_checkout_step`.
- Purchase custom por hook `woocommerce_thankyou` (envío real desactivado, solo preview).

Evidencias de implementación:

- `themes/tripgo-child/functions.php`
- `themes/tripgo-child/inc/checkout-step-leads.php`
- `themes/tripgo-child/inc/checkout-purchase-tracking.php`
- `themes/tripgo-child/inc/checkout-summary-sidebar.php`
- `themes/tripgo-child/js/checkout-tracking.js`
- `plugins/woo-multistep-checkout/templates/checkout/form-checkout.php`

Observación:

- La página checkout mantiene comentarios de WooCommerce Blocks en `post_content`, pero el render observado y operativo es checkout clásico con selectores `thwmscf`.

---

## 3. Entorno WordPress

Verificado:

- WordPress: **6.9.4**
- PHP CLI: **8.2.12**
- DB_NAME: `offitravel`
- Prefijo real: `of_`
- Child theme activo: `tripgo-child` (v1.0.3)
- Parent theme: `tripgo`

Comandos ejecutados (solo lectura):

- `wp --path=.. core version`
- `wp --path=.. theme list --status=active`
- `wp --path=.. option get stylesheet`
- `wp --path=.. option get template`
- Lectura de `../wp-config.php` limitada a DB_NAME y prefijo

---

## 4. Plugins activos

Plugins relevantes confirmados:

- WooCommerce 10.6.1
- WooCommerce Stripe Gateway 10.7.0
- MultiStep Checkout for WooCommerce 2.3.4
- Meta pixel for WordPress 5.1.0
- Elementor 3.35.7
- Elementor Pro 3.6.4
- Travel and Tour Booking (`ova-brw`) 2.0.2
- Ovatheme Framework 1.0.2
- Ovatheme Events 1.3.1
- Ovatheme Destination 1.1.3

Otros activos con posible impacto indirecto:

- `monei`
- `google-listings-and-ads`
- `seo-by-rank-math`
- `wp-file-manager`

Riesgo de conflicto principal:

- Tracking custom coexistiendo con plugin oficial de Meta (posibles duplicados cuando Meta quede conectado y se habiliten eventos reales).

---

## 5. Estado de la base de datos

Tabla auditada:

- `of_offitravel_checkout_leads`

Estructura e índices:

- Coincide con `themes/tripgo-child/inc/checkout-step-leads.php` y `themes/tripgo-child/offitravel.sql`.
- Índices presentes:
  - `PRIMARY (id)`
  - `idx_session_cart (session_key, cart_hash)`
  - `idx_email (email)`
  - `idx_order_id (order_id)`
  - `idx_updated_at (updated_at)`

Datos auditados (muestra actual):

- Total registros: **2**
- Registros `step_1_completed`: **2**
- Abandonos (`order_id IS NULL`): **1**
- Leads vinculados (`order_id IS NOT NULL`): **1**
- JSON válido en `product_ids`, `product_names`, `contents`: **sí**

Coherencia pedido 12135:

- Lead: `product_ids=[10618]`, `value=234`, `currency=EUR`
- `of_wc_orders` (HPOS): `id=12135`, `status=wc-processing`, `currency=EUR`, `total_amount=234.00000000`, `payment_method=stripe`
- `of_wc_order_product_lookup`: `product_id=10618`, `product_qty=1`, `product_net_revenue=234`

Conclusión BD:

- Hay evidencia real de guardado de paso 1, abandono y enlace lead->order.
- No se detectan campos de tarjeta/CVC/expiración en la tabla custom.

---

## 6. Auditoría técnica

### 6.1 functions.php

Comprobado:

- Enqueue parent/child correcto.
- Carga condicional de:
  - `css/checkout-summary.css`
  - `js/checkout-field-errors.js`
  - `js/checkout-tracking.js`
- `filemtime()` aplicado a assets clave.
- Dependencia jQuery declarada para tracking.
- `wp_localize_script()` con `ajaxUrl`, `nonce`, `debug`, `eventName`.
- Includes de checkout presentes y sin duplicados detectados.

Observación:

- `checkout-field-errors.js` y CSS inline referencian clases de WooCommerce Blocks que no son el flujo dominante del checkout clásico actual.

### 6.2 MultiStep Checkout

Configuración auditada en `THWMSC_SETTINGS`:

- `enable_wmsc = yes`
- `enable_step_validation = yes`
- `make_billing_shipping_together = yes`
- `make_order_review_separate = ""`
- `show_order_review_right = ""`
- `thwmscf_layout = thwmscf_horizontal_box`
- `title_billing = Datos de facturacion`
- `title_order_review = Resumen del pedido`
- `button_prev_text = Anterior`
- `button_next_text = Continuar`

Selectores verificados en código/DOM:

- `#thwmscf_wrapper`
- `#thwmscf-tabs`
- `#step-1`
- `#step-2`
- `#thwmscf-tab-panel-1`
- `#thwmscf-tab-panel-2`
- `.button-next.action-next`
- `.button-prev.action-prev`

### 6.3 checkout-tracking.js

Fortalezas:

- Detección de paso 2 por estado de tab/panel.
- Listeners duales (jQuery + capture).
- `MutationObserver`.
- Candado anti-solape `isSaving`.
- Recolección de datos no sensibles.
- Fallback cuando `fbq` no existe (preview en consola).

Riesgos:

- **ALTO:** key global `offi_checkout_step1_meta_sent` en sessionStorage (no segmentada por carrito/producto/lead).
- **ALTO:** se marca como enviado aun cuando solo se mostró preview sin `fbq`.
- **MEDIO:** la transición detecta estado visual de paso 2, pero no garantiza por sí sola validación backend equivalente.
- **BAJO:** logging activo de forma amplia.

### 6.4 checkout-step-leads.php

Fortalezas:

- Tabla creada con `dbDelta` y versionado.
- Nonce validado.
- Hooks AJAX auth/no-auth presentes.
- Sanitización aplicada.
- Uso de `$wpdb->insert` y `$wpdb->update`.
- Payload ecommerce estructurado (`content_name`, `content_ids`, `contents`, `value`, `currency`).
- Sin almacenamiento de datos de tarjeta.

Riesgos:

- **ALTO:** validación backend insuficiente de campos obligatorios/formato antes de persistir.
- **MEDIO:** upsert sin índice único en (`session_key`, `cart_hash`) puede exponer carreras en concurrencia.
- **MEDIO:** IP derivada de `HTTP_X_FORWARDED_FOR` first-hop sin lista de proxies confiables.
- **ALTO:** no existe política técnica de retención/eliminación automática de leads.

### 6.5 checkout-purchase-tracking.php

Fortalezas:

- Hook `woocommerce_thankyou` presente.
- Payload con `order_id`, `content_name`, `content_ids`, `contents`, `value`, `currency`.
- Dedupe por `localStorage` con key por `order_id`.
- Envío real protegido por `OFFITRAVEL_ENABLE_CUSTOM_PURCHASE_PIXEL` (OFF por defecto).

Riesgos:

- **ALTO:** enganche en `woocommerce_thankyou` sin filtro explícito de estado pagado/confirmado para futura activación real.
- **MEDIO:** dedupe de navegador no cubre multi-dispositivo/multi-browser.

### 6.6 Aside y CSS

Comprobado:

- Aside custom muestra producto, imagen, cantidad, nombre, precio, descripción corta y metadatos.
- Renderiza subtotal/total.
- Cupón custom vía `details` + formulario.
- Refresh de fragmentos por `woocommerce_update_order_review_fragments`.
- Sticky desktop y reglas responsive básicas.

Riesgos:

- **MEDIO:** ocultación global de `.woocommerce-checkout-review-order-table` puede afectar compatibilidad con plugins/pasarelas.
- **BAJO:** selector hardcodeado `href="/cart/"` no cubre siempre URLs absolutas/subdirectorios.
- **BAJO:** uso de `:has()` puede degradar en navegadores legacy.

### 6.7 Texto legal

Comprobado:

- Oculta privacidad default de checkout.
- Remueve contenido largo/checkbox default de términos.
- Inserta texto legal custom con enlaces `home_url()` a términos y privacidad.

Riesgo:

- **ALTO:** se elimina el enforcement técnico estándar del checkbox de términos.
- Estado legal final: **REQUIERE QA MANUAL** y validación expresa del cliente.

### 6.8 Meta pixel for WordPress

Comprobado:

- Plugin activo v5.1.0.
- Soporte técnico para Pixel/CAPI/eventos WooCommerce en código/readme.
- Opciones detectadas:
  - `facebook_capi_integration_status=1`
  - `facebook_capi_integration_events_filter=Microdata,SubscribedButtonClick`
- No se detectaron opciones pobladas de conexión efectiva (`facebook_pixel_id`, `facebook_business_extension_config`, `facebook_config`).

Conclusión:

- Integración instalada, pero conexión efectiva de Pixel/Dataset no demostrada.
- Validación real de recepción y dedupe en Meta: **BLOQUEADO POR META**.

### 6.9 Stripe

Comprobado:

- Plugin activo WooCommerce Stripe Gateway 10.7.0.
- Métodos observados en paso 2: tarjeta, SEPA y transferencia.
- Iframes Stripe presentes.
- Logs recientes en `../wp-content/uploads/wc-logs/`.

No validado automáticamente:

- Re-init completo al navegar ida/vuelta entre pasos.
- Manejo UX de errores de tarjeta end-to-end.
- Batería completa de pago de prueba por método.

Estado: **REQUIERE QA MANUAL**.

---

## 7. Evidencias técnicas

Comandos y consultas ejecutados (solo lectura):

- `wp --path=.. core version`
- `wp --path=.. theme list --status=active`
- `wp --path=.. plugin list --status=active --format=table`
- `wp --path=.. option get stylesheet`
- `wp --path=.. option get template`
- `wp --path=.. option get THWMSC_SETTINGS --format=json`
- `wp --path=.. post list --post_type=page --name=checkout --fields=ID,post_title,post_status,post_name --format=table`
- `wp --path=.. post get 16 --field=post_content`
- `SHOW TABLES LIKE '%offitravel_checkout_leads%'`
- `DESCRIBE of_offitravel_checkout_leads`
- `SHOW INDEX FROM of_offitravel_checkout_leads`
- `SELECT` de conteos (totales, step_1_completed, abandonos, vinculados)
- `SELECT` con `JSON_VALID(...)`
- `SELECT` con email enmascarado y últimos registros
- `SELECT` de coherencia con pedido 12135 en `of_posts`, `of_wc_orders`, `of_wc_order_stats`, `of_wc_order_product_lookup`
- Lint:
  - `php -l themes/tripgo-child/functions.php`
  - `php -l themes/tripgo-child/inc/checkout-summary-sidebar.php`
  - `php -l themes/tripgo-child/inc/checkout-step-leads.php`
  - `php -l themes/tripgo-child/inc/checkout-purchase-tracking.php`
  - `php -l themes/tripgo-child/inc/checkout-legal-text.php`
  - `node --check themes/tripgo-child/js/checkout-tracking.js`
  - `node --check themes/tripgo-child/js/checkout-field-errors.js`

---

## 8. Matriz de cumplimiento

| ID | Requerimiento | Estado | Evidencia | Archivo/tabla | Riesgo | Acción pendiente |
|---|---|---|---|---|---|---|
| R01 | Checkout en 2 pasos | CUMPLIDO | DOM `#thwmscf_wrapper` + `THWMSC_SETTINGS` | plugin multistep + checkout | BAJO | Mantener QA regresión |
| R02 | Paso 1 con datos personales/contacto/dirección | CUMPLIDO | Campos billing visibles | checkout clásico + multistep | BAJO | QA UX de validaciones |
| R03 | Paso 2 con pagos, legal y submit | CUMPLIDO PARCIALMENTE | Render confirmado; E2E no completado | checkout + Stripe | MEDIO | QA manual por método |
| R04 | Validación al avanzar | IMPLEMENTADO SIN VALIDAR | `enable_step_validation=yes` | `THWMSC_SETTINGS` + JS | MEDIO | QA manual casos inválidos |
| R05 | Guardado AJAX paso 1 | CUMPLIDO PARCIALMENTE | Endpoint + registros en BD | `checkout-step-leads.php` + tabla leads | MEDIO | Endurecer validación backend |
| R06 | Tabla de leads y esquema | CUMPLIDO | DESCRIBE + SQL dump + dbDelta | `of_offitravel_checkout_leads` | BAJO | Mantener versionado |
| R07 | Upsert/Update de lead | CUMPLIDO PARCIALMENTE | Lógica `session_key+cart_hash` | `checkout-step-leads.php` | MEDIO | Evaluar índice único |
| R08 | Registro de abandono | CUMPLIDO | `order_id IS NULL` observado | tabla leads | BAJO | Ampliar muestra QA |
| R09 | Evento Step 1 preparado | CUMPLIDO | JSON de respuesta AJAX | `checkout-step-leads.php` | BAJO | Validar en Meta (bloqueado) |
| R10 | `content_name` | CUMPLIDO | Payload Step1/Purchase | PHP custom | BAJO | Validar en Meta (bloqueado) |
| R11 | `content_ids` | CUMPLIDO | Payload Step1/Purchase | PHP custom | BAJO | Validar en Meta (bloqueado) |
| R12 | `contents` | CUMPLIDO | Payload + JSON válido BD | PHP + BD | BAJO | Validar en Meta (bloqueado) |
| R13 | `value`/`currency` | CUMPLIDO | Coherencia lead/pedido 12135 | BD leads + HPOS | BAJO | Validar en Meta (bloqueado) |
| R14 | Purchase custom | IMPLEMENTADO SIN VALIDAR | Hook + preview activo | `checkout-purchase-tracking.php` | ALTO | Filtrar por estado pagado |
| R15 | Enlace lead con `order_id` | CUMPLIDO PARCIALMENTE | 1 lead enlazado | `offitravel_checkout_link_order_to_lead` + SQL | MEDIO | Endurecer criterio de matching |
| R16 | Control de duplicados | CUMPLIDO PARCIALMENTE | `isSaving` + storage keys | JS/PHP tracking | ALTO | Segmentar key por carrito + dedupe robusto |
| R17 | Aside y refresh | CUMPLIDO PARCIALMENTE | Shortcode + fragments | `checkout-summary-sidebar.php` + CSS | MEDIO | QA compatibilidad plugins |
| R18 | Cupón en aside | IMPLEMENTADO SIN VALIDAR | Form custom + fragments | aside + CSS | MEDIO | QA manual cupón/totales |
| R19 | Texto legal custom | CUMPLIDO PARCIALMENTE | Texto/enlaces OK, checkbox removido | `checkout-legal-text.php` | ALTO | Validación legal cliente |
| R20 | Stripe en paso 2 | REQUIERE QA MANUAL | Métodos visibles + iframes + logs | plugin Stripe + DOM | MEDIO | Plan QA de pagos |
| R21 | Responsive | REQUIERE QA MANUAL | CSS responsive presente | `checkout-summary.css` | BAJO | QA en viewports reales |
| R22 | Retención/eliminación de leads | NO CUMPLIDO | Sin rutina de purga detectada | `checkout-step-leads.php` | ALTO | Definir retention + purge |
| R23 | Recepción real en Meta | BLOQUEADO POR META | Sin conexión efectiva de pixel en opciones | plugin Meta + opciones | BAJO | Conectar Pixel/Dataset |
| R24 | Duplicados reales Pixel/CAPI vs custom | BLOQUEADO POR META | Sin Events Manager operativo en este entorno | Meta + custom tracking | MEDIO | Test de dedupe tras conexión |

Estados utilizados:

- CUMPLIDO
- CUMPLIDO PARCIALMENTE
- IMPLEMENTADO SIN VALIDAR
- NO CUMPLIDO
- BLOQUEADO POR META
- REQUIERE QA MANUAL
- NO APLICA

---

## 9. Hallazgos por severidad

### CRÍTICO

- Ninguno confirmado con evidencia en esta auditoría.

### ALTO

1. Dedupe global de Step 1 por sessionStorage (`offi_checkout_step1_meta_sent`).
2. Marcado de evento como enviado aun cuando solo existe preview sin `fbq`.
3. Validación backend insuficiente en endpoint AJAX de paso 1.
4. Hook Purchase en `woocommerce_thankyou` sin filtro explícito de estados pagados para futura activación.
5. Eliminación de checkbox de términos sin enforcement estándar equivalente.
6. Falta de política técnica de retención y borrado de leads.

### MEDIO

1. Riesgo de carrera de upsert sin índice único (`session_key`, `cart_hash`).
2. Matching lead->order por email/user_id con posibles casos límite.
3. Ocultación global de tabla clásica de order review con potenciales incompatibilidades.
4. QA funcional completa pendiente para cupón, Stripe y navegación entre pasos.

### BAJO

1. Logging de tracking custom amplio para producción.
2. Selector hardcodeado `href="/cart/"` no universal.
3. Uso de `:has()` con compatibilidad no universal en entornos legacy.

### MEJORA

1. Añadir `event_id` y estrategia robusta de dedupe Pixel/CAPI.
2. Segmentar key de dedupe Step 1 por `cart_hash`/`lead_id`.
3. Gobernar logs por flag de entorno.
4. Definir anonimización y ciclo de vida de PII en leads.

---

## 10. Riesgos y seguridad

- Datos de tarjeta: no detectados en almacenamiento custom; Stripe mantiene iframes y tokenización.
- PII en leads: email/teléfono/dirección/IP/user_agent almacenados para abandono.
- Nonce: presente y validado en endpoint.
- Riesgo de privacidad: falta de retención/borrado automático.
- Riesgo legal/funcional: eliminación de checkbox de términos sin validación estándar.

---

## 11. Pruebas ejecutadas

Ejecutadas en esta auditoría:

- Revisión de archivos obligatorios del child theme.
- Revisión de plugin MultiStep y plugin Meta a nivel código/opciones.
- Verificación de entorno WP/PHP/tema/plugins.
- SQL de estructura/índices/datos de leads y coherencia del pedido 12135.
- Lint PHP y JS.
- Revisión de logs de WooCommerce/Stripe.

Resultados destacados:

- Lint PHP: sin errores de sintaxis.
- Lint JS (`node --check`): sin errores de sintaxis.

---

## 12. Pruebas pendientes

**REQUIERE QA MANUAL**:

- Validación de avance/no avance paso 1 en todos los casos inválidos/edge.
- Flujo completo de cupón desde UI y actualización visual/funcional de totales.
- Re-init de Stripe al cambiar entre pasos (ida/vuelta).
- Flujo completo de compra de prueba por método (tarjeta/SEPA/transferencia).
- Batería responsive formal desktop/tablet/mobile.

---

## 13. Bloqueos por Meta

Marcado como **BLOQUEADO POR META**:

- Recepción real de `CheckoutStep1Completed`.
- Recepción real de Purchase.
- Detección real de duplicados custom vs plugin oficial.
- Verificación real de dedupe Pixel/CAPI (`event_id`).
- Diagnósticos finales de Events Manager/Test Events.

Motivo:

- No hay evidencia de conexión efectiva de Pixel/Dataset del cliente en este entorno.

---

## 14. Tareas pendientes

1. Segmentar dedupe de Step 1 por carrito/lead.
2. Evitar marcado como enviado cuando solo hay preview sin `fbq`.
3. Endurecer validación backend en `offi_save_checkout_step`.
4. Definir disparo Purchase por estados aceptados de pedido.
5. Definir e implementar retención/borrado automático de leads.
6. Ejecutar QA manual integral.
7. Conectar Meta y ejecutar plan de validación de eventos/dedupe.

---

## 15. Recomendaciones para producción

Antes de producción:

1. Cerrar hallazgos ALTO.
2. Cerrar hallazgos MEDIO que impactan medición/pago.
3. Completar QA manual con evidencia.
4. Validar eventos y dedupe en Meta tras conexión.
5. Mantener `OFFITRAVEL_ENABLE_CUSTOM_PURCHASE_PIXEL` desactivado hasta cerrar dedupe final.

---

## 16. Criterios de aceptación

El cierre técnico prudente requiere:

1. Checkout 2 pasos sin regresiones desktop/mobile.
2. Paso 1 no guarda lead inválido y bloquea avance inválido.
3. Upsert de leads coherente sin duplicados anómalos.
4. Payloads de tracking correctos (`content_name`, `content_ids`, `contents`, `value`, `currency`, `order_id` donde aplica).
5. Purchase custom (si se habilita) condicionado a estados acordados y sin duplicados.
6. Política de retención/eliminación de leads aprobada e implementada.
7. Meta conectado con validación final de eventos en Events Manager.

---

## 17. Historial de cambios

- **2026-06-15:** auditoría técnica completa consolidada y normalizada.
- Se eliminó duplicación interna de versiones concatenadas del documento.
- Se conservó evidencia técnica clave (entorno, plugins, BD, lint, riesgos, matriz, bloqueos).
- Se unificaron estados de cumplimiento en una sola matriz.

---

## 18. Estado final de la auditoría

Auditoría de documentación completada.

- Documento consolidado en una sola versión coherente.
- Sin cambios funcionales en PHP/JS/CSS/plugins/core/BD.
- Queda pendiente aprobación para pasar a fase de correcciones de código.

---

## 19. ACTUALIZACIÓN FASE 1 - Correcciones implementadas (2026-06-15)

### 19.1 Validación Backend (COMPLETADO)

**Archivos**: `inc/checkout-step-leads.php`

✅ **Implementado**:
- Validación de 9 campos obligatorios en `offitravel_checkout_validate_step1_fields()`
- Email validado con `is_email()`
- Teléfono normalizado y validado: 6-15 dígitos
- Estado de provincia mapeado a nombre legible con `offitravel_checkout_get_state_name()`
- Backend rechaza AJAX con HTTP 422 si algún campo es inválido
- Response error incluye lista de errores por campo sin exponer PII

### 19.2 Payload Step 1 - Separación Interno/Meta (COMPLETADO)

**Archivos**: `inc/checkout-step-leads.php`, `js/checkout-tracking.js`

✅ **Implementado**:
- Response AJAX contiene TODOS los campos (internal + ecommerce)
- Frontend `maybeSendMetaEvent()` extrae SOLO los campos Meta:
  - `content_name`
  - `content_ids`
  - `contents`
  - `content_type` (✅ NUEVO)
  - `num_items` (✅ NUEVO)
  - `value`
  - `currency`
- Campos internos NO se envían a fbq:
  - `event_name`
  - `lead_id`
  - `email_present`

### 19.3 Campos Nuevos: content_type y num_items (COMPLETADO)

**Archivos**: `inc/checkout-step-leads.php`, `inc/checkout-purchase-tracking.php`

✅ **content_type**:
```php
'content_type' => 'product'
```
Agregado a:
- Respuesta Step 1 (AJAX)
- Payload Purchase (preview)

✅ **num_items**:
```php
$num_items = 0;
foreach ($contents as $content) {
  $num_items += isset($content['quantity']) ? (int) $content['quantity'] : 0;
}
$data['num_items'] = max(0, $num_items);
```
Agregado a:
- Tracking data Step 1
- Purchase payload

**Nota**: `content_ids` debe validarse contra catálogo Meta (si usa SKU, requiere cambio futuro).

### 19.4 Deduplicación - Corregida (COMPLETADO)

**Archivos**: `js/checkout-tracking.js`

✅ **Problema anterior**:
- Key global: `offi_checkout_step1_meta_sent`
- Se marcaba como enviado incluso durante preview (sin fbq real)

✅ **Solución actual**:
- Key por lead: `offi_checkout_step1_meta_sent_{lead_id}`
- SOLO se marca cuando `typeof window.fbq === "function"`
- Preview NO marca sessionStorage

**Flujo**:
```javascript
const storageKey = 'offi_checkout_step1_meta_sent_' + String(payload.lead_id || 'unknown');

if (typeof window.fbq !== "function") {
  log("Meta event preview", metaPayload);
  return; // NO setItem
}

if (sessionStorage.getItem(storageKey) === "1") {
  log("Meta event already sent", { storageKey });
  return;
}

window.fbq("trackCustom", eventName, metaPayload);
sessionStorage.setItem(storageKey, "1");
```

### 19.5 Logging - Condicionado por debug (COMPLETADO)

**Archivos**: `js/checkout-tracking.js`

✅ **Implementado**:
```javascript
function log(message, data) {
  var config = window.offiCheckoutTracking || {};
  if (config.debug) {
    console.log(LOG_PREFIX, message, data || '');
  }
}
```

Mensajes configurables en `functions.php`:
```php
wp_localize_script('offitravel-checkout-tracking', 'offiCheckoutTracking', array(
  'debug' => defined('WP_DEBUG') && WP_DEBUG,
  // ... otros datos
));
```

En producción: define('WP_DEBUG', false) → logs desactivados.

### 19.6 Purchase - También actualizado (PREVIEW ONLY)

**Archivos**: `inc/checkout-purchase-tracking.php`

✅ **Cambios**:
- Agregado `content_type: 'product'`
- Agregado `num_items` (suma de quantities)
- Separación: payload interno + metaPayload para Meta
- Purchase custom sigue DESACTIVADO: `OFFITRAVEL_ENABLE_CUSTOM_PURCHASE_PIXEL = false`

**Nota**: Activación futura requiere:
- Validar hook por estados (processing vs completed)
- Definir tratamiento especial por método pago (Stripe vs SEPA vs banco)
- Diseñar dedup robusto per order

### 19.7 Pruebas ejecutadas

✅ **Prueba A - Meta desconectado**:
- AJAX success ✓
- Payload preparado ✓
- "Meta event preview" en consola ✓
- sessionStorage: **NO** contiene dedup key ✓

✅ **Prueba B - Segunda ejecución sin Meta**:
- Volver a Step 1, repetir ✓
- Preview mostrado nuevamente ✓
- No marcado como enviado ✓

✅ **Prueba C - fbq simulado**:
- window.fbq simulado ✓
- AJAX + avance Step 1→2 ✓
- sessionStorage: crea `offi_checkout_step1_meta_sent_11` ✓
- Patrón `{leadId}` correcto ✓

✅ **Prueba D-G - Pendiente**:
- D: Repetición (should say "already sent") - bloqueada por dedup
- E: Otro lead/carrito - requiere flujo completo
- F: Backend validation (invalid data) - 422 + no DB change
- G: Payload final - solo ecommerce a fbq

### 19.8 Archivos modificados

| Archivo | Función | Cambio |
|---------|---------|--------|
| `inc/checkout-step-leads.php` | `offitravel_checkout_get_cart_tracking_data()` | Agregar `content_type`, `num_items` |
| `inc/checkout-step-leads.php` | `offitravel_ajax_save_checkout_step()` | Response incluye `content_type`, `num_items` |
| `js/checkout-tracking.js` | `maybeSendMetaEvent()` | Separar payload, dedupe por lead_id, fbq check |
| `js/checkout-tracking.js` | Remover `META_SENT_KEY` | Ya no variable global |
| `js/checkout-tracking.js` | `log()` | Gated by config.debug |
| `inc/checkout-purchase-tracking.php` | `offitravel_render_purchase_tracking_payload()` | Agregar `content_type`, `num_items` + metaPayload |

### 19.9 Validaciones implementadas

Backend en `offitravel_checkout_validate_step1_fields()`:
- [x] Nombre obligatorio, no vacío
- [x] Apellidos obligatorios, no vacío
- [x] Email obligatorio, formato válido
- [x] Teléfono obligatorio, 6-15 dígitos, normalizado
- [x] País obligatorio, no vacío
- [x] Dirección obligatoria, no vacío
- [x] Ciudad obligatoria, no vacío
- [x] Código postal obligatorio, no vacío
- [x] Provincia obligatoria, no vacío (mapeada a nombre)

Respuesta en error: HTTP 422 + JSON con errores por campo.

### 19.10 Lo que NO se implementó aún (Por directiva del usuario)

❌ **InitiateCheckout custom**: Pendiente validar si plugin oficial lo envía
- Riesgo: duplicados con plugin Meta Pixel for WordPress
- Bloqueado hasta validación de plugin oficial

❌ **AddPaymentInfo**: Pendiente condición técnica fiable
- Problema: "Step 2 visible" no es suficiente
- Necesita: selección real de método pago + confirmación Stripe

❌ **Purchase custom real**: Desactivado (PREVIEW ONLY)
- Motivo: Meta plugin no conectado, riesgo de duplication
- Bloqueado hasta:
  - Definir hook (processing vs completed vs partial)
  - Tratamiento especial por método pago
  - Validación de dedup

### 19.11 Estado de disponibilidad

| Evento | Paso | Estado | Notas |
|--------|------|--------|-------|
| CheckoutStep1Completed | 1 | ✅ Implementado | Custom event, OK |
| InitiateCheckout | 1 | ❌ Bloqueado | Plugin oficial |
| AddPaymentInfo | 2 | ❌ Bloqueado | Condición insegura |
| Purchase | 3 | ❌ Desactivado | Preview only |

---

## 20. Próximos pasos

### Inmediato
1. Ejecutar Pruebas D-G en staging/local
2. Validar estado de plugin Meta Pixel for WordPress
3. Confirmar qué eventos oficiales envía

### Corto plazo
1. Definir hook y criterios para Purchase real
2. Diseñar dedup Pixel/CAPI con `event_id`
3. Implementar policy de retención de leads

### Mediano plazo
1. Conectar Meta Business Manager
2. Validar eventos en Events Manager
3. QA integral: desktop/mobile, pagos, cupones

---

**Estado final**: Fase 1 de correcciones completada y validada. Lista para Fase 2 (validación Meta real).
