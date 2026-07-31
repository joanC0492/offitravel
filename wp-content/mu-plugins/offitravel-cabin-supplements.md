# Suplementos de cabina de Offitravel

## Propósito

Este MU Plugin proporciona la configuración administrativa, el cálculo autoritativo y la persistencia de suplementos de cabina por producto OVA. El flujo público está activo exclusivamente para los cruceros fluviales aprobados: `11280`, Crucero fluvial: Mercadillos de Navidad en el Rin, y `11259`, Crucero fluvial: Mercadillos de Navidad en el Danubio.

La implementación reutiliza el modo habitaciones existente sin modificarlo. Tampoco modifica OVA/OVABRW, Tripgo, WooCommerce, el tema hijo ni archivos de proveedor.

## Archivos

- `offitravel-cabin-supplements.php`: administración, cálculo PHP, formulario público, carrito, sesión y pedido.
- `offitravel-cabin-supplements-admin.js`: gestión de filas del metabox del producto.
- `offitravel-cabin-supplements-state.js`: estado puro por cabina y coordinación de peticiones.
- `offitravel-cabin-supplements-front.js`: integración del selector en cada fila de habitación.
- `offitravel-cabin-supplements-front.css`: presentación del control y de sus errores.
- `offitravel-product-addons-front.js`: transporte compartido del payload y recálculo AJAX.

## Configuración administrativa

Los productos OVA disponen del metabox **Opciones de cabina — Base técnica**. Cada opción contiene:

- Identificador interno.
- Etiqueta pública.
- Suplemento por persona.

La lista normalizada se guarda en:

```text
_offitravel_cabin_options
```

Cada fila usa esta estructura:

```php
array(
    'id'               => 'identificador-normalizado',
    'label'            => 'Etiqueta pública',
    'price_per_person' => '135.00',
)
```

La activación pública es explícita y se controla con:

```text
_offitravel_cabin_options_enabled = yes
```

Abrir o guardar un producto sin interactuar con el metabox no crea, migra ni elimina metadatos. Tras una interacción, PHP valida la lista completa antes de escribir: filas completas, identificadores únicos, etiquetas no vacías después del saneamiento y precios decimales no negativos.

## Configuración del Rin

El producto `11280` tiene activado exactamente este catálogo:

| ID | Etiqueta pública | Precio por persona |
|---|---|---:|
| `sin-suplemento` | Sin suplemento | 0,00 € |
| `puente-intermedio` | Puente intermedio | 135,00 € |
| `puente-superior` | Puente superior | 200,00 € |

Además se guarda:

```text
_offitravel_ovabrw_room_single_supplement_eur = 0
```

Ese valor neutraliza para `11280` el fallback global de 150 € del suplemento individual. No cambia el comportamiento de ningún otro tour.

## Configuración del Danubio

El producto `11259` tiene activado exactamente este catálogo:

| ID | Etiqueta pública | Precio por persona |
|---|---|---:|
| `sin-suplemento` | Sin suplemento | 0,00 € |
| `puente-intermedio` | Puente intermedio | 111,50 € |
| `puente-superior` | Puente superior | 200,00 € |

Para representar una categoría independiente por cabina, el producto reutiliza el modo habitaciones con sus límites previamente almacenados: hasta diez habitaciones y hasta cuatro personas por habitación. No se ha añadido un mínimo ni un máximo total de adultos. También se guarda:

```text
_offitravel_ovabrw_room_single_supplement_eur = 0
```

Ese valor neutraliza para `11259` el fallback global de 150 € del suplemento individual. Una cabina con una persona continúa siendo válida conforme a la configuración existente, sin añadir dicho suplemento.

## Formulario público y estado JavaScript

Cuando el producto está activado, el servidor publica un bloque JSON con el producto, la opción inicial y las opciones disponibles. JavaScript inserta **Categoría de cabina** dentro de cada `.offitravel-room-row`.

El estado se mantiene por formulario y por índice de cabina:

- Las cabinas nuevas reciben la primera opción configurada (`sin-suplemento` en Rin y Danubio).
- Las cabinas que sobreviven a una reconstrucción conservan su categoría.
- Las cabinas retiradas desaparecen del estado.
- La ocupación oculta se sincroniza con el selector real de personas de cada habitación.

El navegador envía únicamente:

```text
offitravel_cabins[ROOM_INDEX][people]
offitravel_cabins[ROOM_INDEX][category]
```

Nunca envía precios, etiquetas, subtotales ni totales. El payload se integra en el único `$.ajaxPrefilter` existente, tanto para solicitudes con objeto como serializadas. Los campos de habitaciones siguen siendo propiedad del modo habitaciones y no se duplican en el constructor del suplemento.

El recálculo directo usa una secuencia aislada por formulario: aborta la petición anterior y sólo la respuesta con el token más reciente puede actualizar el total.

## Validación y cálculo PHP

`offitravel_cabin_calculate_request_snapshot()` recibe el POST y un contexto de ocupación. Antes de calcular:

1. Resuelve el producto con `wc_get_product()` y exige el tipo `ovabrw_car_rental`.
2. Exige activación explícita del producto.
3. Lee etiquetas y precios exclusivamente desde WordPress.
4. Valida el número de habitaciones y los ocupantes con los límites configurados en ese producto.
5. Exige exactamente una categoría válida por cabina.
6. Exige que la ocupación enviada para la cabina coincida con la ocupación real.
7. Rechaza cabinas faltantes, adicionales o estructuras no escalares.

Cada subtotal se calcula como:

```text
ocupantes reales × precio por persona almacenado
```

Los importes se normalizan con `wc_format_decimal()` y `wc_get_price_decimals()`. Repetir AJAX o recalcular el carrito no acumula el suplemento: el filtro de precio suma una sola vez el total del snapshot al precio base recibido.

## Snapshot

El carrito conserva:

```text
offitravel_cabin_supplements
  version
  product_id
  cabins[CABIN_INDEX]
    cabin_index
    occupants
    category
    label
    price_per_person
    subtotal
  total
```

La normalización histórica valida y reconstruye subtotales desde el propio snapshot sin consultar tarifas actuales. Esto conserva las condiciones cobradas aunque la configuración comercial cambie después.

## Carrito, sesión y pedido

- `woocommerce_add_to_cart_validation` valida el payload antes de añadir al carrito.
- `woocommerce_add_cart_item_data` crea el snapshot autoritativo después de que el modo habitaciones haya guardado la ocupación.
- `ovabrw_get_price_by_guests` añade el total una vez, con prioridad `1009`.
- `woocommerce_get_cart_item_from_session` restaura el snapshot histórico.
- `woocommerce_get_item_data` muestra el desglose en carrito y checkout.
- `woocommerce_checkout_create_order_line_item` guarda una fila comercial visible y dos metadatos técnicos.
- `woocommerce_hidden_order_itemmeta` oculta exclusivamente los metadatos técnicos.

Metadatos técnicos del pedido:

```text
_offitravel_cabin_supplement_snapshot
_offitravel_cabin_supplement_total
```

La fila visible **Suplemento de cabina** incluye cabina, ocupantes, categoría, precio por persona, subtotal y total. Los correos estándar de WooCommerce heredan esa fila.

## Orden de precio

```text
815   Matriz CCKF
844   Descuentos de ocupación
850   Paquete y suplemento individual
999   Precio fijo por ocupación
1008  Suplementos fijos y seguros por edad
1009  Suplementos de cabina
```

## Compatibilidad y aislamiento

- El seguro por edad de circuitos no cambia.
- KIT romántico, Seguro de anulación, Platea y Servicio 01 no cambian.
- Los selectores `rey_leon`, `wicked` y los paquetes existentes no cambian.
- Rin y Danubio son los únicos productos con configuración y selector de cabina activos.
- El Rin conserva sus límites actuales: una habitación, hasta cinco personas y mínimo dos adultos.
- El Danubio conserva sus límites actuales: hasta diez habitaciones, hasta cuatro personas por habitación y sin un mínimo total configurado.
- Los demás productos no reciben configuración ni selector.

## Pruebas

Las pruebas cubren:

- Estado y payload JavaScript sin precios del cliente.
- Opción inicial, preservación y retirada de cabinas.
- Cancelación de peticiones y protección frente a respuestas antiguas.
- Configuración exclusiva y exacta de Rin y Danubio.
- Rin: cálculos 2×0, 2×135, 2×200, 5×135 y 5×200.
- Danubio: cálculos 2×0, 2×111,50, 2×200, dos cabinas `3 + 2` y categorías independientes.
- Aplicación de los límites propios de cada producto y neutralización local del fallback de 150 €.
- Manipulación de categoría, ocupación, precio, etiqueta y total.
- Validación AJAX, idempotencia y aislamiento de productos.
- Restauración de sesión y persistencia del pedido.
- Eliminación exclusiva del pedido temporal creado por la prueba.
- Regresión de circuitos, musicales y productos no afectados.

## Alcance activo

La integración pública de cabinas está activa únicamente en `11280` y `11259`. Cualquier alta futura deberá configurarse explícitamente por producto y conservar sus propios límites OVA; el sistema no inventa límites globales ni activa otros tours automáticamente.
