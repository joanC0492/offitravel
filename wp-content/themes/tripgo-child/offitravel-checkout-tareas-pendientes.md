# OFFITRAVEL - Plan de tareas pendientes para cierre de Checkout 2 pasos y Tracking Meta

Fecha: 15 de junio de 2026
Alcance: plan de implementación, validación y QA pendiente
Base de referencia: auditoría técnica consolidada en themes/tripgo-child/offitravel-checkout-meta.md

---

## Objetivo de negocio que se debe cumplir

1. Simplificar y dividir el checkout en 2 pasos para poder medir correctamente la iniciación de pago.
2. Paso 1: al continuar, guardar datos no sensibles del contacto en BD y preparar/disparar evento de Meta para retargeting en abandono.
3. Paso 2: mostrar pago/facturación (tarjeta Stripe, SEPA, transferencia) y completar compra.
4. Enriquecer tracking de conversión con variables medibles y consistentes:
   - Order ID del pedido confirmado
   - Nombre del producto como parámetro estructurado
   - content_ids, contents, value, currency

---

## Estado actual resumido

1. La estructura de 2 pasos existe y está operativa a nivel técnico.
2. El guardado de leads de paso 1 existe en la tabla of_offitravel_checkout_leads.
3. Hay payloads estructurados para paso 1 y Purchase en modo preview.
4. Faltan validaciones críticas, QA manual y cierre de deduplicación.
5. Validación real en Meta está bloqueada hasta conexión de Pixel/Dataset por cliente.

---

## Bloque A - Tareas técnicas obligatorias antes de QA final

### A1. Endurecer validación backend en guardado de paso 1

Objetivo:
Evitar guardar leads inválidos cuando frontend sea bypassed o validación visual no se cumpla.

Qué hacer:
1. Validar campos mínimos obligatorios en backend (nombre, apellidos, email, teléfono, dirección, código postal, ciudad, provincia, país, según regla de negocio final).
2. Validar formato de email y normalizar teléfono.
3. Devolver error estructurado en JSON cuando falle validación.
4. No insertar ni actualizar lead si la validación falla.

Qué validar:
1. Si falta un requerido, no se guarda lead.
2. Si email es inválido, no se guarda lead.
3. Si todos los campos son válidos, sí guarda y responde success.

Evidencia mínima:
1. Captura de request/response AJAX con casos válido e inválido.
2. Conteo de filas en BD antes/después de cada caso.

---

### A2. Corregir estrategia de deduplicación de evento de paso 1

Objetivo:
No perder eventos legítimos y evitar bloqueo global por sesión.

Qué hacer:
1. Reemplazar key global de sessionStorage por key segmentada (por ejemplo con cart_hash o lead_id).
2. No marcar evento como enviado cuando fbq no existe y solo se muestra preview.
3. Marcar como enviado solo cuando exista confirmación de envío real (o estrategia equivalente definida).

Qué validar:
1. Carrito A y carrito B en misma sesión no se bloquean entre sí.
2. Si fbq no existe en primer intento, el evento puede enviarse después cuando fbq sí exista.
3. Doble clic en continuar no dispara duplicado.

Evidencia mínima:
1. Logs de consola antes/después.
2. Inspección de sessionStorage por carrito.

---

### A3. Definir criterio de disparo de Purchase por estado de pedido

Objetivo:
Evitar enviar Purchase en escenarios no confirmados.

Qué hacer:
1. Definir estados de pedido válidos para considerar compra confirmada (alineado a negocio y método de pago).
2. Condicionar el disparo custom de Purchase a esos estados.
3. Mantener OFFITRAVEL_ENABLE_CUSTOM_PURCHASE_PIXEL desactivado hasta terminar pruebas de dedupe con plugin oficial.

Qué validar:
1. Pedido confirmado: payload correcto con order_id.
2. Pedido no confirmado o fallido: no dispara Purchase custom.

Evidencia mínima:
1. Tabla de mapeo estado pedido -> dispara/no dispara.
2. Registro de pruebas por estado.

---

### A4. Política de retención y borrado de leads

Objetivo:
Reducir riesgo de privacidad y cumplimiento.

Qué hacer:
1. Definir plazo de retención de leads abandonados.
2. Definir borrado/anonimización automática (job programado).
3. Documentar responsable, frecuencia y alcance de la purga.

Qué validar:
1. Los leads fuera de ventana se purgan o anonimizan.
2. Los leads vinculados a pedido siguen política definida por negocio/legal.

Evidencia mínima:
1. Política escrita aprobada.
2. Prueba de ejecución de purga en entorno de test.

---

### A5. Validación legal de términos y consentimiento

Objetivo:
Alinear checkout con requisitos legales del cliente.

Qué hacer:
1. Confirmar con cliente/legal si se mantiene o no checkbox obligatorio de términos.
2. Si se mantiene sin checkbox, validar aceptación implícita y trazabilidad legal requerida.
3. Documentar decisión final y aprobación explícita.

Qué validar:
1. Texto legal correcto y enlaces válidos.
2. Comportamiento acordado aprobado por cliente.

Evidencia mínima:
1. Aprobación legal/cliente por escrito.

---

## Bloque B - QA funcional completo del checkout 2 pasos

### B1. QA del paso 1 (formulario de contacto)

Casos obligatorios:
1. Todos los campos válidos -> avanza a paso 2 y guarda lead.
2. Campo requerido vacío -> no avanza y no guarda.
3. Email inválido -> no avanza y no guarda.
4. Volver atrás, editar y continuar -> actualiza lead correctamente.
5. Doble clic en continuar -> no duplica guardado.
6. Dos pestañas simultáneas -> comportamiento controlado sin inconsistencias graves.

Validaciones técnicas:
1. Existe request a admin-ajax con action offi_save_checkout_step.
2. Respuesta JSON coherente (success/error) según caso.
3. BD refleja insert/update correcto.

---

### B2. QA del paso 2 (pago/facturación)

Casos obligatorios:
1. Se renderizan métodos esperados (tarjeta, SEPA, transferencia según disponibilidad).
2. Cambiar de paso 2 a paso 1 y volver a paso 2 mantiene funcionamiento.
3. El botón de finalizar pedido funciona sin romper flujo multistep.
4. Errores de pago se muestran correctamente y no corrompen estado del checkout.

Validaciones técnicas:
1. Stripe Elements/iframes se inicializan correctamente al volver a paso 2.
2. No hay ocultación accidental de bloques críticos por CSS.

---

### B3. QA de aside y cupón

Casos obligatorios:
1. Aside muestra producto, cantidad, precio y metadatos correctamente.
2. Aplicar cupón actualiza subtotal/total.
3. Quitar cupón revierte totales correctamente.
4. Carritos con múltiples productos renderizan bien.
5. Producto sin imagen no rompe layout.

---

### B4. QA responsive y accesibilidad básica

Dispositivos:
1. Desktop
2. Tablet
3. Mobile

Checks mínimos:
1. No solapamientos de botones o campos.
2. Navegación entre pasos usable.
3. Texto legal visible.
4. Aside usable o adaptado en mobile.

---

## Bloque C - Validación de tracking Meta (cuando cliente conecte cuenta)

Estado actual:
BLOQUEADO POR META hasta conexión de Pixel/Dataset en plugin oficial.

### C1. Verificación técnica inicial

1. Confirmar disponibilidad de fbq en checkout.
2. Confirmar configuración del plugin oficial (Pixel/CAPI).

### C2. Evento de paso 1

Validar que se envía con parámetros:
1. content_name
2. content_ids
3. contents
4. value
5. currency

### C3. Evento Purchase

Validar que se envía con parámetros:
1. order_id
2. content_name
3. content_ids
4. contents
5. value
6. currency

### C4. Duplicados y dedupe

1. Verificar coexistencia plugin oficial vs custom.
2. Confirmar que no hay doble envío no deseado.
3. Si aplica CAPI, validar deduplicación con event_id.

---

## Checklist de cierre por fases

### Fase 1 - Hardening técnico (sin Meta)

1. A1 completada
2. A2 completada
3. A3 completada
4. A4 completada
5. A5 completada

Resultado esperado:
Arquitectura robusta y lista para QA integral.

### Fase 2 - QA funcional integral

1. B1 completada
2. B2 completada
3. B3 completada
4. B4 completada

Resultado esperado:
Checkout 2 pasos estable, sin regressions críticas.

### Fase 3 - Cierre de tracking Meta

1. Cliente conecta Pixel/Dataset
2. C1 completada
3. C2 completada
4. C3 completada
5. C4 completada

Resultado esperado:
Tracking validado end-to-end y sin duplicados críticos.

---

## Criterio de listo para producción

Se considera listo cuando:

1. El paso 1 solo guarda datos válidos y no sensibles.
2. El paso 2 funciona correctamente con métodos de pago habilitados.
3. Order ID y nombre de producto están estructurados y consistentes en eventos.
4. No hay duplicados críticos de eventos.
5. Existe política activa de retención/borrado de leads.
6. QA funcional completa está documentada con evidencia.
7. Meta está conectado y validado en entorno de pruebas.

---

## Evidencia que se debe adjuntar al cierre

1. Capturas de Network para paso 1 (casos éxito/error).
2. Export de consultas SQL de verificación (sin PII completa).
3. Capturas de consola/eventos para paso 1 y Purchase.
4. Evidencia de pruebas de cupón y totales.
5. Evidencia responsive (desktop/tablet/mobile).
6. Evidencia de dedupe y no duplicación.
7. Aprobación legal/cliente sobre términos y retención de datos.

---

## Estado actual

No está al 100 por ciento.

Qué sí está:
1. Base técnica principal implementada.

Qué falta para cierre real:
1. Hardening técnico de tracking/validación.
2. QA funcional integral.
3. Validación final en Meta tras conexión del cliente.
