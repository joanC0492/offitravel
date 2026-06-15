# OFFITRAVEL — Requerimiento real del checkout, retargeting y Meta Pixel

## 1. Contexto

El checkout de OFFITRAVEL debe dividirse en dos pasos para identificar a los usuarios que avanzan en el proceso de compra, registrar a quienes abandonan antes de pagar y enviar los eventos correspondientes a Meta.

La división visual en dos pasos no es suficiente. El sistema debe guardar información utilizable por el equipo del cliente y permitir acciones posteriores de retargeting.

---

## 2. Interpretación correcta del requerimiento

Cuando el cliente indica que los datos deben guardarse en una “base de datos”, no necesariamente se refiere de manera técnica a guardar registros directamente en una tabla de MySQL.

Lo importante es que la información quede almacenada en un sistema accesible y útil para el cliente, por ejemplo:

- Una sección dentro del administrador de WordPress.
- Un módulo de carritos o checkouts abandonados.
- Un plugin especializado en recuperación de carritos.
- Una interfaz propia donde se puedan consultar los registros.

Guardar la información únicamente en una tabla interna sin interfaz no cumpliría completamente el objetivo, porque el cliente no podría consultarla ni utilizarla fácilmente.

---

## 3. Flujo funcional requerido

### Paso 1 — Datos personales y de contacto

El usuario completa sus datos básicos, como:

- Nombre.
- Apellidos.
- Correo electrónico.
- Teléfono.
- Datos de facturación o dirección que correspondan.
- Tour, circuito, musical o producto seleccionado.
- Variantes, fechas, cantidades y demás opciones elegidas.
- Importe estimado del carrito.

Al pulsar **Continuar**:

1. Se validan los campos obligatorios.
2. Se registra la información del usuario y del carrito.
3. Se crea un registro de checkout incompleto, carrito abandonable o preorden.
4. El registro queda disponible en una interfaz accesible desde WordPress.
5. Se dispara el evento de Meta correspondiente.
6. El usuario avanza al paso de pago.

Este registro no tiene que ser todavía un pedido oficial de WooCommerce.

---

### Paso 2 — Pago

En el segundo paso se muestran:

- Resumen del producto o servicio.
- Importe final.
- Datos adicionales de facturación, si corresponden.
- Métodos de pago.
- Campos de Stripe u otra pasarela habilitada.

Cuando el usuario llega realmente a este paso, se debe registrar la transición y enviar el evento de Meta correspondiente.

El evento no debe depender solamente de pulsar los botones “Siguiente” o “Anterior”. Debe representar un cambio real dentro del flujo.

---

### Compra completada

Cuando el usuario finaliza el pago:

1. WooCommerce crea el pedido oficial.
2. Se obtiene el ID real del pedido.
3. El registro previo de checkout incompleto se relaciona con el pedido.
4. El registro se marca como convertido o completado.
5. Se dispara el evento `Purchase`.
6. Se envían a Meta los datos reales del pedido y de los productos.

---

## 4. Interfaz de administración

Debe existir una forma sencilla para que el cliente pueda consultar los usuarios que iniciaron el checkout y no finalizaron la compra.

La interfaz debería mostrar como mínimo:

- Nombre del usuario.
- Correo electrónico.
- Teléfono.
- Producto, tour, musical o circuito.
- Opciones seleccionadas.
- Cantidad.
- Importe.
- Fecha y hora del registro.
- Último paso alcanzado.
- Estado del registro.
- Pedido relacionado, cuando exista.

### Estados sugeridos

- Iniciado.
- Llegó al pago.
- Abandonado.
- Recuperado.
- Convertido.
- Descartado.

La solución puede implementarse mediante un plugin especializado o mediante una interfaz personalizada dentro de WordPress.

---

## 5. Retargeting

El objetivo del registro es identificar a los usuarios que estuvieron cerca de comprar y abandonaron el proceso.

La información podría utilizarse posteriormente para:

- Enviar correos de recuperación.
- Crear campañas de retargeting.
- Ofrecer descuentos.
- Analizar en qué paso abandonan los usuarios.
- Relacionar el abandono con productos o experiencias concretas.

El requerimiento actual no define exactamente qué acción automática realizará el cliente después. Primero debe garantizarse que la información quede registrada y sea accesible.

---

## 6. Eventos de Meta

### `InitiateCheckout`

Debe dispararse cuando el usuario completa correctamente el primer paso y continúa hacia el pago.

Debe incluir información estructurada del carrito, por ejemplo:

- `content_ids`
- `contents`
- `content_name`
- `content_type`
- `value`
- `currency`
- Número de productos

### `AddPaymentInfo`

Debe dispararse cuando el usuario entra realmente en la etapa de pago o proporciona información de pago.

No debe dispararse simplemente al regresar con el botón “Anterior”.

### `Purchase`

Debe dispararse únicamente cuando el pedido haya sido creado y confirmado.

Debe incluir:

- ID único del pedido.
- Moneda.
- Valor total.
- Productos.
- ID de cada producto.
- Nombre del producto.
- Precio.
- Cantidad.
- Categoría, cuando esté disponible.

Debe evitarse que `Purchase` se envíe más de una vez por pedido.

---

## 7. Estructura esperada del producto para Meta

El nombre del producto no debe enviarse solamente como un texto plano aislado.

Ejemplo conceptual:

```javascript
{
  content_ids: ["123"],
  content_type: "product",
  content_name: "Nombre del tour o circuito",
  contents: [
    {
      id: "123",
      quantity: 2,
      item_price: 150.00
    }
  ],
  value: 300.00,
  currency: "EUR"
}
```

La estructura definitiva debe adaptarse a los datos disponibles en WooCommerce y a la integración utilizada con Meta.

---

## 8. Error de la interpretación anterior

La implementación anterior se centró principalmente en:

- Dividir visualmente el checkout con un plugin.
- Enviar eventos al pulsar los botones de navegación.
- Guardar información directamente en la base de datos.

Eso no cubre por completo el objetivo porque:

1. Una tabla sin interfaz no es utilizable por el cliente.
2. No todo clic en “Siguiente” o “Anterior” representa un evento de conversión.
3. El cliente necesita identificar y consultar los checkouts abandonados.
4. El registro debe contener el producto y todas las opciones elegidas.
5. El flujo debe permitir relacionar posteriormente el registro con un pedido real.

---

## 9. Soluciones posibles

### Opción A — Plugin especializado

Investigar un plugin compatible con WooCommerce que permita:

- Registrar carritos abandonados.
- Capturar datos antes de crear el pedido.
- Mostrar los registros en WordPress.
- Recuperar carritos.
- Integrarse con Meta o permitir eventos personalizados.
- Adaptarse al checkout multipasos actual.

### Opción B — Desarrollo personalizado

Crear una solución propia con:

- Registro persistente del paso 1.
- Entidad propia de checkout incompleto o preorden.
- Pantalla administrativa en WordPress.
- Estados del proceso.
- Relación con pedidos de WooCommerce.
- Envío de eventos a Meta.
- Protección y tratamiento adecuado de datos personales.

La opción personalizada puede ser más sencilla inicialmente, siempre que incluya una interfaz accesible y no se limite a guardar datos ocultos en MySQL.

---

## 10. Pruebas necesarias

Para validar correctamente la implementación se necesita conectar una cuenta de Meta.

Puede utilizarse:

- La cuenta del cliente.
- Una cuenta de prueba.
- Un píxel de pruebas.

Se debe comprobar:

1. Que `InitiateCheckout` se recibe al pasar correctamente al pago.
2. Que `AddPaymentInfo` se recibe en la etapa correcta.
3. Que `Purchase` se recibe una sola vez.
4. Que el ID del pedido es el real.
5. Que el nombre y los datos del producto aparecen como parámetros medibles.
6. Que los registros incompletos aparecen en la interfaz.
7. Que una compra completada actualiza el registro previo.
8. Que regresar al paso anterior no duplica eventos incorrectamente.

Los mensajes de `console.log` solo permiten revisar lo que el código intenta enviar. No confirman que Meta haya recibido ni procesado correctamente los eventos.

---

## 11. Puntos pendientes de confirmar

Antes de cerrar la implementación, conviene confirmar con el cliente:

- Si desean solamente consultar los abandonos o también enviar correos automáticos.
- Cuánto tiempo debe pasar para considerar un checkout como abandonado.
- Qué usuarios tendrán acceso a la interfaz.
- Qué datos personales desean conservar.
- Cuánto tiempo deben conservarse esos datos.
- Si ya utilizan alguna plataforma de CRM, email marketing o retargeting.
- Si prefieren una solución basada en plugin o una solución personalizada.

---

## 12. Criterio de aceptación principal

El requerimiento se considerará cumplido cuando:

- El checkout funcione en dos etapas reales.
- Los datos del primer paso se guarden antes del pago.
- El cliente pueda consultar esos registros desde una interfaz.
- Se pueda identificar quién abandonó y qué intentó comprar.
- Los eventos de Meta se disparen en el momento correcto.
- `Purchase` incluya el ID real del pedido.
- Los productos se envíen a Meta como parámetros estructurados.
- La implementación pueda validarse mediante una cuenta o píxel de prueba de Meta.
