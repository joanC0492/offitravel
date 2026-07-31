# Modo habitaciones OVA de Offitravel

## Propósito

`offitravel-ovabrw-room-mode.php` adapta el formulario OVA para representar habitaciones y ocupantes reales, sincroniza esa ocupación con `ovabrw_adults` y participa en el cálculo AJAX, carrito y pedido. También contiene las reglas personalizadas de precio fijo, suplemento individual, descuentos de ocupación y matrices ya existentes.

## Flujo principal

1. La configuración por producto determina si el modo habitaciones está activo y sus límites.
2. La plantilla hija `woocommerce/rental/loop/fields/guests-rooms.php` publica `offitravel_room_count` y `offitravel_room_people[]`.
3. El JavaScript inline reconstruye las filas, suma ocupantes y sincroniza `ovabrw_adults`.
4. El payload personalizado de `ovabrw_calculate_total` incorpora la ocupación sin añadir un interceptor AJAX adicional.
5. PHP valida cantidad de habitaciones, ocupación por habitación y coherencia con el total.
6. El carrito y el pedido conservan el desglose de ocupación y los suplementos propios del modo habitaciones.

## Cambio del Checkpoint 2

Cuando el formulario contiene `[data-offitravel-age-service]`, cambiar el número de habitaciones conserva las ocupaciones de las habitaciones que continúan existiendo. Las habitaciones eliminadas se descartan y las nuevas utilizan los valores predeterminados actuales.

La condición limita el cambio al seguro de viaje por edad. Los productos sin ese servicio mantienen exactamente la reconstrucción anterior. La sincronización de checkboxes y edades permanece aislada en `offitravel-product-addons-front.js` y `offitravel-product-addons-traveler-age-state.js`.

El helper AJAX del modo habitaciones devuelve ahora su `jqXHR`. El formulario del seguro utiliza ese retorno para mantener bloqueada la reserva durante el recálculo y para que sólo la petición de edad más reciente pueda desbloquearla. La URL, el payload y los callbacks existentes no cambian.

## Dependencias y consideraciones

- Depende de OVA para fechas, disponibilidad y precio base.
- Depende de WooCommerce para precisión decimal, carrito y metadatos de pedido.
- No modifica archivos del plugin OVA ni del tema Tripgo padre.
- Los límites continúan procediendo de los metadatos configurados por producto.
- El cambio de este checkpoint no modifica reglas de suplemento individual, matrices ni descuentos.
