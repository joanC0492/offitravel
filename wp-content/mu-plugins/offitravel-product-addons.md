# Servicios adicionales de producto

## Propósito

`offitravel-product-addons.php` administra servicios opcionales reutilizables y los asigna a productos OVA. Mantiene el cálculo fijo existente e implementa servicios opcionales con tarifa por edad de cada viajero.

Los servicios `fixed` conservan su consulta, validación y suma independientes. Los servicios `traveler_age` nunca reutilizan `_offitravel_addon_price`: cada selección se valida contra el producto y las reglas almacenadas antes de calcularse.

## Modelo de datos

- `_offitravel_addon_price`: precio unitario del modelo fijo.
- `_offitravel_addon_billing`: modalidad conservada para el modelo fijo: `person`, `room` o `booking`.
- `_offitravel_addon_product_ids`: IDs de productos asignados.
- `_offitravel_addon_public_label`: etiqueta pública opcional. Si falta, se utiliza el título interno.
- `_offitravel_addon_price_model`: sólo se almacena como `traveler_age` para el modelo por edad. Su ausencia significa `fixed` para conservar compatibilidad con servicios antiguos.
- `_offitravel_addon_age_rules`: lista ordenada de reglas con `min_age`, `max_age` y `price`. `max_age=null` representa un tramo sin límite superior.
- `_offitravel_addon_manual_room_product_ids`: productos concretos donde un servicio `room` solicita al comprador una cantidad manual de habitaciones. Su ausencia conserva el cálculo fijo legado.
- `_offitravel_addon_booking_once`: el valor `yes` fuerza una unidad por reserva/línea OVA para un servicio `booking`, sin utilizar viajeros, habitaciones ni `ovabrw_quantity`.

## Flujo administrativo

1. El editor muestra la etiqueta pública y el modelo de precio.
2. `fixed` muestra precio unitario y modalidad por persona, habitación o reserva.
3. `traveler_age` oculta los campos fijos, utiliza lógicamente la modalidad por viajero sin sobrescribir `_offitravel_addon_billing` y muestra la tabla de tramos.
4. `offitravel-product-addons-admin.js` valida la entrada antes del envío y gestiona filas dinámicas.
5. `offitravel_addon_validate_admin_payload()` repite la validación en PHP.
6. Se completa toda la validación antes de realizar escrituras. Un error de validación evita escrituras parciales y se muestra tras la redirección de WordPress; no se utiliza una transacción de base de datos.

## Flujo público por edad

1. El formulario consulta exclusivamente los servicios `traveler_age` publicados y asignados al producto actual. Cada tramo configurado se muestra como una fila independiente en la leyenda pública.
2. JavaScript crea una fila por cada posición real de `offitravel_room_people[]` con checkbox individual y edad deshabilitada inicialmente. La edad no recibe un valor implícito: permanece vacía y muestra `Ej. 35` como orientación. El cero es válido únicamente cuando el comprador lo escribe expresamente.
3. Al cambiar ocupación, las posiciones que continúan conservan su selección; las eliminadas descartan sus datos y las nuevas comienzan vacías.
4. Los checkboxes fijos se recopilan exclusivamente desde `input[name="offitravel_addons[]"]`; los checkboxes individuales se envían sólo dentro de `offitravel_age_addons`. Para estos últimos sólo se envían servicio, habitación, posición, selección y edad. Nunca se envían precios confiables desde el navegador.
5. `offitravel_addon_calculate_traveler_age()` valida el servicio, su asignación, la ocupación, la edad entera no negativa y el tramo; después crea el snapshot.
6. El AJAX utiliza la misma función de cálculo, y el alta al carrito repite la validación mediante `woocommerce_add_to_cart_validation`.
7. Carrito y sesión guardan `offitravel_traveler_age`. La restauración recalcula tarifas y totales desde las reglas incluidas en el snapshot.
8. `ovabrw_get_price_by_guests` suma el total normalizado una sola vez a la línea, por lo que repetir el cálculo con la misma base no acumula importes.
9. Carrito y checkout muestran el desglose por viajero como HTML seguro separado por `<br>`. Los importes se convierten primero a texto Unicode sin entidades visibles. El pedido guarda las mismas líneas como texto separado por saltos de línea para administración y correos estándar, además de `_offitravel_traveler_age_snapshot` y `_offitravel_traveler_age_total` ocultos.
10. Al marcar un seguro, el campo de edad queda habilitado, requerido, vacío y enfocado sin mostrar todavía un error. El aviso rojo y `aria-invalid="true"` aparecen únicamente cuando el cliente abandona el campo con un valor inválido o intenta reservar. En ese intento se impide el envío y se devuelve el foco a la primera edad pendiente. La escritura programa un único recálculo tras 250 ms; una nueva pulsación cancela la espera y aborta la petición anterior cuando ya existe. El botón sólo permanece deshabilitado mientras el último AJAX está pendiente; la validación del clic y la validación PHP impiden reservar con edades seleccionadas inválidas. Sólo la petición más reciente puede volver a habilitarlo.

La estructura pública enviada es:

```text
offitravel_age_addons[SERVICE_ID][ROOM][POSITION][selected]
offitravel_age_addons[SERVICE_ID][ROOM][POSITION][age]
```

El snapshot incluye `service_id`, etiqueta pública, reglas, producto, habitación, posición dentro de la habitación, ordinal global del viajero, edad, tarifa, subtotal y total.

## Flujo fijo con snapshot

Los servicios fijos seleccionados siguen enviándose mediante `offitravel_addons[]`. Sólo los servicios `room` configurados para cantidad manual añaden:

```text
offitravel_addon_quantities[SERVICE_ID]=ROOM_COUNT
```

`offitravel_addon_calculate_fixed_snapshot()` valida en PHP que cada servicio esté publicado, sea `fixed` y esté asignado al producto. La etiqueta, modalidad, precio y política de cantidad se leen siempre desde WordPress; el navegador nunca decide importes ni subtotales.

El snapshot `offitravel_fixed_addons` contiene versión, producto, servicios y total. Cada servicio conserva `service_id`, etiqueta pública, modalidad, fuente de cantidad, cantidad, precio unitario y total. Las fuentes soportadas son:

- `real_rooms`: número real de habitaciones del formulario OVA, sin multiplicar por `ovabrw_quantity`.
- `manual_rooms`: entero positivo introducido expresamente por el comprador.
- `booking_once`: una unidad exacta por reserva/línea OVA.
- `legacy`: compatibilidad con los servicios fijos no migrados.

El KIT 12027 mantiene precio `12` y modalidad `room`. En 10618, 10628, 11512 y 11521 utiliza `real_rooms`; en 11528, 11537, 11539 y 11545 utiliza `manual_rooms`. Al marcar el KIT manual se habilita y enfoca el campo sin mostrar error inmediato; al abandonar un valor inválido o intentar reservar se muestra el aviso. Al desmarcar se limpia y deshabilita el campo.

El Seguro de anulación 12732 usa `booking` y `_offitravel_addon_booking_once=yes`: su snapshot siempre registra cantidad 1 y total 6,00 €, aunque se manipulen huéspedes, habitaciones, `ovabrw_quantity` o cantidades enviadas.

Carrito y sesión conservan el snapshot normalizado. `ovabrw_get_price_by_guests` suma su total una sola vez sobre la base recibida, de forma idempotente. Carrito y checkout muestran un desglose HTML seguro; el pedido y los correos guardan el mismo contenido con saltos de línea. Los metadatos técnicos `_offitravel_fixed_addon_snapshot` y `_offitravel_fixed_addon_total` permanecen ocultos, mientras `_offitravel_addon_ids` se conserva por compatibilidad.

## Validación de tramos

- Debe existir al menos una regla.
- Las edades son enteros iguales o superiores a cero.
- No se impone una edad máxima comercial.
- La edad máxima puede omitirse únicamente en el último tramo efectivo.
- La edad máxima no puede ser menor que la mínima.
- Los rangos no pueden solaparse.
- Se permiten huecos entre rangos porque la cobertura continua no es una regla comercial genérica.
- Los precios aceptan el formato decimal de WooCommerce y deben ser iguales o superiores a cero.

## Compatibilidad

Los servicios que no tengan `_offitravel_addon_price_model` siguen siendo de precio fijo. Abrir y guardar un servicio antiguo sin cambiarlo conserva el valor textual de su precio, modalidad y asignaciones, y no crea los nuevos metadatos opcionales.

Un servicio legado `traveler_age` conserva `_offitravel_addon_price` y `_offitravel_addon_billing`, pero ambos quedan inactivos mientras está vigente el modelo por edad. El cálculo público usa exclusivamente las reglas por edad. Al regresar a `fixed`, el editor vuelve a presentar y guardar ese precio y esa modalidad fija; las pruebas cubren los recorridos con `room` y `booking`.

Un servicio creado directamente como `traveler_age` puede no tener `_offitravel_addon_billing`. Esto no afecta a su futura operación por edad, que siempre será por viajero por definición del modelo. Si posteriormente se cambia a `fixed`, el administrador puede seleccionar una modalidad; si no recibe ninguna, se aplica y almacena el fallback compatible `person`.

## Dependencias

- WordPress para el CPT, metadatos, permisos, nonces y avisos.
- WooCommerce para controles administrativos y normalización decimal.
- `offitravel-product-addons-admin.js` para la interacción del editor.
- `offitravel-product-addons-front.js` añade selecciones fijas y por edad al mecanismo AJAX existente.
- `offitravel-product-addons-fixed-state.js` valida cantidades manuales y construye su payload mediante funciones puras comprobables fuera del navegador.
- `offitravel-product-addons-traveler-age-state.js` reconcilia posiciones de viajeros sin depender del DOM y permite pruebas aisladas.
- `offitravel-ovabrw-room-mode.php` conserva ocupaciones existentes al reconstruir habitaciones únicamente cuando el formulario contiene un servicio por edad.

## Pruebas

Ejecutar desde la raíz:

```powershell
php tests/offitravel-product-addons-admin-test.php
php tests/offitravel-product-addons-fixed-ajax-test.php
php tests/offitravel-product-addons-fixed-snapshot-test.php
php tests/offitravel-product-addons-fixed-order-persistence-test.php
php tests/offitravel-product-addons-musicals-config-test.php
php tests/offitravel-product-addons-traveler-age-test.php
php tests/offitravel-product-addons-order-persistence-test.php
node tests/offitravel-product-addons-fixed-state-test.js
node tests/offitravel-product-addons-traveler-age-state-test.js
```

Las pruebas administrativas interceptan escrituras durante simulaciones. La suite pública verifica cálculo, límites 69/70, edades inválidas, múltiples habitaciones, manipulación de IDs y precios, idempotencia, sesión, pedido y los servicios configurados 12718/12719. También consulta 12027, 12028 y 12717 para detectar cambios de precio, modalidad o asignaciones.

Cada prueba de persistencia crea un único pedido WooCommerce temporal identificado por su ID, guarda el snapshot y el desglose visible, vuelve a cargarlo desde la base de datos y elimina exclusivamente ese ID antes de devolver éxito o fallo. No busca ni modifica pedidos ajenos.
