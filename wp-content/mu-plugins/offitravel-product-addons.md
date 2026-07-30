# Servicios adicionales de producto

## Propósito

`offitravel-product-addons.php` administra servicios opcionales reutilizables y los asigna a productos OVA. Mantiene el cálculo fijo existente y prepara, sólo a nivel administrativo, la configuración de precios por edad de viajero.

El formulario público continúa usando exclusivamente los servicios de precio fijo. Hasta que se implemente su flujo completo, cualquier servicio `traveler_age` queda excluido de la consulta pública, el renderizado, la validación de IDs y la suma fija, aunque conserve un precio fijo antiguo.

## Modelo de datos

- `_offitravel_addon_price`: precio unitario del modelo fijo.
- `_offitravel_addon_billing`: modalidad conservada para el modelo fijo: `person`, `room` o `booking`.
- `_offitravel_addon_product_ids`: IDs de productos asignados.
- `_offitravel_addon_public_label`: etiqueta pública opcional. Si falta, se utiliza el título interno.
- `_offitravel_addon_price_model`: sólo se almacena como `traveler_age` para el modelo por edad. Su ausencia significa `fixed` para conservar compatibilidad con servicios antiguos.
- `_offitravel_addon_age_rules`: lista ordenada de reglas con `min_age`, `max_age` y `price`. `max_age=null` representa un tramo sin límite superior.

## Flujo administrativo

1. El editor muestra la etiqueta pública y el modelo de precio.
2. `fixed` muestra precio unitario y modalidad por persona, habitación o reserva.
3. `traveler_age` oculta los campos fijos, utiliza lógicamente la modalidad por viajero sin sobrescribir `_offitravel_addon_billing` y muestra la tabla de tramos.
4. `offitravel-product-addons-admin.js` valida la entrada antes del envío y gestiona filas dinámicas.
5. `offitravel_addon_validate_admin_payload()` repite la validación en PHP.
6. Se completa toda la validación antes de realizar escrituras. Un error de validación evita escrituras parciales y se muestra tras la redirección de WordPress; no se utiliza una transacción de base de datos.

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

Un servicio legado `traveler_age` conserva `_offitravel_addon_price` y `_offitravel_addon_billing`, pero ambos quedan inactivos mientras está vigente el modelo por edad. Al regresar a `fixed`, el editor vuelve a presentar y guardar ese precio y esa modalidad fija; las pruebas cubren los recorridos con `room` y `booking`.

Un servicio creado directamente como `traveler_age` puede no tener `_offitravel_addon_billing`. Esto no afecta a su futura operación por edad, que siempre será por viajero por definición del modelo. Si posteriormente se cambia a `fixed`, el administrador puede seleccionar una modalidad; si no recibe ninguna, se aplica y almacena el fallback compatible `person`.

El renderizado público, el AJAX, el carrito y los pedidos no fueron modificados en este checkpoint.

## Dependencias

- WordPress para el CPT, metadatos, permisos, nonces y avisos.
- WooCommerce para controles administrativos y normalización decimal.
- `offitravel-product-addons-admin.js` para la interacción del editor.
- `offitravel-product-addons-front.js` permanece encargado del comportamiento público existente.

## Pruebas

Ejecutar desde la raíz:

```powershell
php tests/offitravel-product-addons-admin-test.php
```

Las pruebas interceptan las operaciones de metadatos durante simulaciones de guardado, por lo que no escriben en los servicios reales. También consultan 12027, 12028 y 12717 para detectar cambios de precio, modalidad o asignaciones.
