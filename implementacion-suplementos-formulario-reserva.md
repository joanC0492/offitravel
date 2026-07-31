# Implementación realizada — Suplementos en el formulario de reserva

## Objetivo

Se completó la implementación de los suplementos pendientes en los formularios de reserva de:

- Circuitos.
- Musicales.
- Cruceros fluviales del Rin y del Danubio.

La solución mantiene el recálculo existente por habitación, evita duplicar importes y calcula los suplementos de forma autoritativa desde PHP y WordPress.

---

## 1. Circuitos — Seguro de viaje por edad

Se añadió un seguro de viaje cuyo precio depende de la edad de cada viajero.

### Asturias y Ribeira Sacra

Productos configurados:

- Asturias: producto `9475`.
- Ribeira Sacra: producto `9487`.

Tarifas:

| Edad del viajero | Precio |
|---|---:|
| Hasta 69 años | 32,50 € por persona |
| 70 años o más | 45,50 € por persona |

### A Coruña

Producto configurado:

- A Coruña: producto `9502`.

Tarifas:

| Edad del viajero | Precio |
|---|---:|
| Hasta 69 años | 17,50 € por persona |
| 70 años o más | 24,50 € por persona |

### Funcionamiento implementado

- Se captura la edad del viajero.
- El seguro se calcula individualmente para cada viajero seleccionado.
- El precio se resuelve en PHP según el destino y la edad.
- Los importes enviados desde el navegador no se consideran válidos.
- El total se actualiza mediante AJAX.
- La selección se conserva en carrito, checkout y pedido.
- El desglose comercial aparece de forma legible para el cliente y en la administración.

Servicios utilizados:

- `12718`: Seguro de viaje — Asturias y Ribeira Sacra.
- `12719`: Seguro de viaje — A Coruña.

---

## 2. Musicales — KIT romántico y Seguro de anulación

Se mantuvo el suplemento existente de Platea y se descartó el selector de categorías que ya no era necesario.

### KIT romántico

Servicio:

- `12027`: KIT romántico.

Precio:

- 12,00 € por habitación.

Funcionamiento:

- Se cobra una vez por cada habitación incluida en la reserva.
- En los musicales que no utilizan el modo de habitaciones, se aplica la equivalencia configurada para mantener el mismo criterio comercial.
- El importe se recalcula sin duplicarse.

### Seguro de anulación

Servicio:

- `12732`: Seguro de anulación — Musicales.

Precio:

- 6,00 € por reserva.

Funcionamiento:

- Se cobra una sola vez por reserva.
- No se multiplica por viajeros ni por habitaciones.
- Se conserva en carrito, checkout y pedido.

### Platea

Servicio existente:

- `12028`: Entradas en platea A.

Se mantuvo su funcionamiento y configuración sin cambios.

### Musicales incluidos

La configuración se aplicó a los musicales definidos en el proyecto:

- `10618`
- `10628`
- `11512`
- `11521`
- `11528`
- `11537`
- `11539`
- `11545`

---

## 3. Cruceros fluviales — Suplemento por categoría de cabina

Se creó una base reutilizable para suplementos de cabina y posteriormente se activó para los cruceros del Rin y del Danubio.

Cada habitación o cabina muestra un selector propio de categoría.

El cálculo aplicado es:

```text
Suplemento de cabina = ocupantes reales de la cabina × precio por persona
```

El navegador envía únicamente:

- Índice de la cabina.
- Número de ocupantes.
- Categoría seleccionada.

Las etiquetas, precios, subtotales y total se obtienen siempre desde WordPress y se calculan en PHP.

---

## 3.1. Crucero por el Rin

Producto:

- `11280`: Crucero fluvial: Mercadillos de Navidad en el Rin.

Opciones configuradas:

| Categoría | Precio por persona |
|---|---:|
| Sin suplemento | 0,00 € |
| Puente intermedio | 135,00 € |
| Puente superior | 200,00 € |

Ejemplos validados:

| Ocupación | Categoría | Incremento |
|---:|---|---:|
| 2 personas | Sin suplemento | 0,00 € |
| 2 personas | Puente intermedio | 270,00 € |
| 2 personas | Puente superior | 400,00 € |
| 5 personas | Puente intermedio | 675,00 € |
| 5 personas | Puente superior | 1.000,00 € |

Configuración conservada:

- Paquete `pack_mercadillo_rin`.
- Modo habitaciones activo.
- Máximo de 1 habitación.
- Máximo de 5 personas por habitación.
- Mínimo de 2 adultos.
- Fechas, disponibilidad y precio base existentes.

También se neutralizó únicamente para este producto el suplemento individual heredado de 150,00 €, evitando que se añadiera junto al nuevo suplemento de cabina.

---

## 3.2. Crucero por el Danubio

Producto:

- `11259`: Crucero fluvial: Mercadillos de Navidad en el Danubio.

Opciones configuradas:

| Categoría | Precio por persona |
|---|---:|
| Sin suplemento | 0,00 € |
| Puente intermedio | 111,50 € |
| Puente superior | 200,00 € |

Ejemplos validados:

| Distribución | Categoría | Incremento |
|---|---|---:|
| 2 personas en una cabina | Sin suplemento | 0,00 € |
| 2 personas en una cabina | Puente intermedio | 223,00 € |
| 2 personas en una cabina | Puente superior | 400,00 € |
| 2 cabinas, distribución 3 + 2 | Ambas en puente intermedio | 557,50 € |
| 2 cabinas, distribución 3 + 2 | Ambas en puente superior | 1.000,00 € |
| 2 cabinas, distribución 3 + 2 | Intermedio + superior | 734,50 € |

Configuración conservada:

- Paquete `pack_mercadillo_danubio`.
- Precio base.
- Fecha y disponibilidad.
- Stock y whitelist.
- Máximo de 10 habitaciones.
- Máximo de 4 personas por habitación.
- Sin mínimo o máximo global nuevo de adultos.

Para permitir una categoría independiente por cabina, se activó el modo habitaciones utilizando los límites que ya estaban almacenados en el producto.

También se neutralizó únicamente para Danubio el suplemento individual heredado de 150,00 €.

---

## 4. Comportamiento común de los suplementos de cabina

Se implementó:

- Selector “Categoría de cabina” dentro de cada habitación.
- “Sin suplemento” como opción inicial.
- Estado independiente por formulario y por cabina.
- Conservación de la categoría cuando una habitación continúa existiendo.
- Eliminación del estado cuando se elimina una habitación.
- Recálculo AJAX sin acumulación de importes.
- Cancelación de solicitudes anteriores.
- Protección para que una respuesta AJAX antigua no sobrescriba el último total.
- Validación de ocupación, categoría y número de cabinas en PHP.
- Rechazo de precios o subtotales manipulados desde el navegador.
- Snapshot histórico del suplemento en carrito, sesión y pedido.
- Desglose visible en checkout, administración y correos.
- Metadatos técnicos ocultos al cliente.

Metadatos de pedido utilizados:

```text
_offitravel_cabin_supplement_snapshot
_offitravel_cabin_supplement_total
```

---

## 5. Aislamiento y compatibilidad

Se verificó que la implementación no afectara:

- Otros circuitos.
- Otros musicales.
- Platea.
- Servicio 01.
- KIT romántico.
- Seguro de anulación.
- Seguros por edad.
- Productos no relacionados.
- Archivos de OVA/OVABRW.
- Tripgo.
- WooCommerce.
- Plugins de proveedores.
- Tema hijo.

Los únicos productos con configuración de cabinas activa son:

- `11280`: Rin.
- `11259`: Danubio.

---

## 6. Pruebas realizadas

La suite final quedó con:

```text
153 casos
153 correctos
0 fallos
```

Las pruebas cubren:

- Tarifas exactas.
- Reglas por edad.
- Cálculo por persona, habitación y reserva.
- Manipulación del payload.
- Idempotencia.
- Recálculo AJAX.
- Protección frente a respuestas antiguas.
- Carrito y sesión.
- Checkout.
- Persistencia en pedidos.
- Administración y correos.
- Ocultación de metadatos técnicos.
- Eliminación segura de pedidos temporales.
- Aislamiento entre Rin, Danubio y los demás productos.
- Regresión UTF-8.
- Sintaxis PHP.
- Sintaxis JavaScript.
- Validación de diferencias Git.

---

## 7. Resultado final

Quedaron completados los suplementos solicitados:

- Seguro de viaje por edad para Asturias, Ribeira Sacra y A Coruña.
- KIT romántico de 12,00 €.
- Seguro de anulación de 6,00 € por reserva.
- Suplementos de cabina del Rin.
- Suplementos de cabina del Danubio.
- Integración con recálculo, checkout, pedidos y correos.
- Protección contra duplicaciones, manipulación de precios y acumulación de importes.
