**FISHING STORE.** EJERCICIO PRÁCTICO

Página 2/14

**FISHING STORE.** Ejercicio práctico
# Contenido
[Descripción del sistema	1](#_Toc123924870)

[Requerimientos de instalación	2](#_Toc123924871)

[Instalación del ambiente de desarrollo	2](#_Toc123924872)

[Características de la implementación	3](#_Toc123924873)

[Modelo relacional	3](#_Toc123924874)

[Arquitectura de la solución	4](#_Toc123924875)

[Servicios API	6](#_Toc123924876)

[Productos	6](#_Toc123924877)

[**POST /api/products.** Adiciona un nuevo producto al inventario	6](#_Toc123924878)

[**PUT /api/products/{id}.** Actualiza un producto existente	6](#_Toc123924879)

[**POST /api/products/update-stock.** Actualiza el inventario de una lista de productos existentes	7](#_Toc123924880)

[**DELETE /api/products/{id}.** Elimina un producto existente	8](#_Toc123924881)

[**GET /api/products.** Devuelve un listado de los productos con existencia mayor que cero	8](#_Toc123924882)

[**GET /api/products/{id}.** Devuelve un producto en específico	8](#_Toc123924883)

[Ventas	9](#_Toc123924884)

[**POST /api/sales.** Adiciona una nueva venta con sus productos	9](#_Toc123924885)

[**PUT /api/sales/{id}.** Actualiza una venta existente	10](#_Toc123924886)

[**POST /api/sales/report.** Crea y devuelve un reporte de ventas totales	12](#_Toc123924887)

[**DELETE /api/sales/{id}.** Elimina una venta existente y sus productos asociados en la tabla pivote	12](#_Toc123924888)

[**GET /api/sales.** Devuelve un listado de las ventas existentes con sus productos	12](#_Toc123924889)

[**GET /api/sales/{id}.** Devuelve los datos de una venta en específico incluyendo sus productos	13](#_Toc123924890)
# Descripción del sistema
**FishingStore** es un ejercicio práctico para demostrar el uso de LARAVEL en la creación de servicios API REST. Responde al siguiente planteamiento:

María tiene una tienda de artículos de pesca. Hace unos meses solo vendía anzuelos, cañas y plomadas pero un amigo la ayudó con el financiamiento para hacer mucho más grande su negocio. Hoy María tiene en su inventario más de 100 productos diferentes por lo que pidió a nuestra empresa un sistema que facilite su trabajo.

Los productos están separados por 3 categorías:

1. Gama Baja: los que tienen una utilidad menor al 10 % del precio de venta.
1. Gama Media: los que tienen una utilidad entre el 10 % y el 50 % del precio de venta.
1. Gama Alta: Los que tienen una utilidad mayor que el 50 % del precio de venta.

En el inventario cada producto se tiene (id, nombre, número de serie, precio de compra, precio de venta y existencia).

- Diseñe una Base de Datos que permita registrar los datos de los productos y las ventas de la tienda.

Una parte fundamental de la aplicación final que será entregada a María es una API desarrollada en Laravel que permita hacer las siguientes funcionalidades:

- Listar los productos existentes en el Almacén.
- Darle entrada al inventario a un listado de productos con sus respectivas cantidades. (para este ejercicio todos los productos serán medidos en Unidades).
- Cuando un cliente pase por la caja registradora se enviará a nuestra API un HTTP request con los datos de la factura (listado de productos y cantidades). Este servicio debe registrar los datos de la venta realizada o retornar un Error en caso de que alguno de los productos de la factura no tenga existencia en el inventario. 
- Un servicio que retorne un reporte en formato PDF con los siguientes datos:

|**Gama Baja**|**Gama Media**|**Gama Alta**|**Total**|
| - | - | - | - |
|**Total Vendido**|**Utilidad**|**Total Vendido**|**Utilidad**|**Total Vendido**|**Utilidad**|**Total Vendido**|**Utilidad**|
|||||||||

# Requerimientos de instalación
- PHP > 8.0
- Postgres > 9.4
- Composer
# Instalación del ambiente de desarrollo
1. Clonar repositorio desde la dirección <https://github.com/cdonis/fishingstore.git>

git clone https://github.com/cdonis/fishingstore.git

1. Crear una copia del fichero .env.example y renombrarla con .env
1. Instalar dependencias del proyecto

composer install

1. Crear una base de datos local y actualizar las variables correspondientes en el fichero .env

DB\_CONNECTION=pgsql

DB\_HOST=127.0.0.1

DB\_PORT=5432

DB\_DATABASE=fishingstore

DB\_USERNAME=<user>

DB\_PASSWORD=<password>

1. Ejecutar las migraciones de la base de datos

php artisan migrate

1. Popular la base de datos con datos de prueba

php artisan db:seed

1. Iniciar el servidor de desarrollo

php artisan serve
# Características de la implementación
## Modelo relacional
**Tabla: 		*products***	

**Descripción**:	Almacena información sobre productos

|Atributos|PK|FK|Tipo|<p>Length/</p><p>Precision</p>|Unique|Null|Descripción|
| - | - | - | - | :-: | :-: | :-: | - |
|id|☒|☐|Biginteger||☒|☐|Identificador del producto|
|name|☐|☐|Varchar|255|☒|☐|Nombre del producto|
|serial|☐|☐|Varchar|255|☒|☐|Número de serie del producto|
|purchase\_price|☐|☐|Decimal|19/2|☐|☐|Precio de compra del producto|
|sale\_price|☐|☐|Decimal|19/2|☐|☐|Precio de venta del producto|
|range|☐|☐|String||☐|☐|Gama del producto È|
|Enumerado con 3 posibles valores: HIGH-RANGE (gama alta), MID-RANGE (gama media), LOW-RANGE (gama baja). Campo calculado a partir de la relación entre el precio de venta del producto (sale\_price) y su utilidad (sale\_price - purchase\_price y) Se incluye para mejorar eficiencia en consultas que requieren esta categorización del producto y evitar su cálculo en un procesamiento en lotes.|
|stock|☐|☐|integer||☐|☐|Existencia del producto (unidades)|
|<p>Constrains:</p><p>- Primary Key: 	id</p><p>- Unique: 		name</p><p>- Unique:		serial</p>|
**Tabla: 		*sales***	

**Descripción**:	Almacena información sobre las ventas.

|Atributos|PK|FK|Tipo|<p>Length/</p><p>Precision</p>|Unique|Null|Descripción|
| - | - | - | - | :-: | :-: | :-: | - |
|id|☒|☐|Biginteger||☒|☐|Identificador del producto|
|cashier|☐|☐|Varchar|255|☐|☐|Nombre del cajero que realiza la venta. |
|created\_at|☐|☐|Datetime||☐|☐|Fecha de la venta|
|updated\_at|☐|☐|Datetime||☐|☐|Fecha de la venta|
|<p>Constrains:</p><p>- Primary Key: 	id</p>|

**Tabla: 		*sales*\_*products***

**Descripción**:	Productos incluidos en las ventas. Implementa la relación muchos a muchos entre ***products*** y ***sales***

|Atributos|PK|FK|Tipo|<p>Length/</p><p>Precision</p>|Unique|Null|Descripción|
| - | - | - | - | :-: | :-: | :-: | - |
|id|☒|☐|Biginteger||☒|☐|Identificador de la tupla|
|sale\_id|☐|☒|Datetime||☐|☐|Id de la venta (FK tabla ***sales***)|
|product\_id|☐|☒|Varchar|255|☐|☐|Id del producto (FK tabla ***products***)|
|quantity|☐|☐|integer||☐|☐|Cantidad de producto vendida|
|<p>Constrains:</p><p>- Primary Key: 	id</p>|

## Arquitectura de la solución
- La implementación se basa en dos recursos desde los cuales se gestionan los servicios que satisfacen los requerimientos del sistema. Estos recursos son: **productos** y **ventas**  
- De cada recurso se implementan los siguientes elementos:
  - Migración para la creación y eliminación de la tabla que almacena sus datos
  - Factoría para la creación de instancias del recurso
  - Seeder para la generación de tuplas del recurso en la base de datos
  - Controlador que implementa los servicios API relacionados y que satisfacen los requerimientos del sistema
  - En el caso del recurso **producto** se implementa una capa de servicios para desarrollar acciones comunes a varias responsabilidades requeridas desde el controlador
  - Rutas de acceso a los servicios requeridos.
- Para la gestión de la tabla pivote que implementa la relación muchos a muchos entre las **ventas** (tabla ***sales***) y los **productos** (tabla ***products***) no se utiliza un recurso especifico pues no existe la necesidad de operaciones que lo requiera. Su gestión es desarrolla desde los servicios del recurso venta.
- Funciones y servicios implementados para satisfacer servicios relacionados con el recurso **productos**:

Modelo:		**Product**

Controlador: 	**ProductController**

|**Servicio**|**Función**|
| :- | :- |
|Listar productos con existencia mayor que cero|index()|
|Crear un producto|store()|
|Recuperar datos de un prodcuto|show()|
|Actualizar datos de un producto|update()|
|Eliminar un producto|delete()|
|Actualizar el inventario de un grupo de productos |updateStock()|
- Funciones y servicios implementados para satisfacer servicios relacionados con el recurso **ventas**:

Modelo:		**Sale**

Controlador: 	**SaleController**

|**Servicio**|**Función**|
| :- | :- |
|Listar todas las ventas con sus productos|index()|
|Crear una venta con sus productos|store()|
|Recuperar datos de una venta incluyendo sus productos|show()|
|Actualizar datos de una venta incluyendo sus productos|update()|
|Eliminar una venta incluyendo sus productos|delete()|
|Obtener reporte de ventas |salesReport()|
- Funciones que satisfacen los requerimientos del sistema 

|**Servicio**|**Función**|
| :- | :- |
|Listar los productos existentes en el Almacén|*ProductController*.**index**()|
|Dar entrada al inventario a un listado de productos con sus respectivas cantidades|*ProductController*.**updateStock**()|
|Registrar los datos de una venta realizada incluyendo sus productos|*SaleController*.**store**()|
|Crear y retornar reporte en formato PDF con datos sobre las ventas|*SaleController*.**salesReport**()|
- Validación de request

En los casos en que se requiere validación de los datos recibidos en los request, esta se realiza desde clases implementadas al efecto (App\Http\Requests\) que heredan de ***FormRequest***.


# Servicios API
## Productos
### **POST /api/products.** Adiciona un nuevo producto al inventario
- **Parámetros**:

|Parámetro|Descripción|
| :- | :- |
|Body: JSON|<p>Objeto de tipo producto que requiere ser almacenado</p><p>{</p><p>`    `"name": "Anzuelos triple hook 5",</p><p>`    `"serial": "1645654508",</p><p>`    `"purchase\_price": 1.75,</p><p>`    `"sale\_price": 5.40,</p><p>`    `"stock": 5</p><p>}</p>|

- **Responses**:

|Código|Descripción|
| :- | :- |
|201|<p>Producto creado</p><p>Ejemplo</p><p>{</p><p>`    `"name": "Anzuelos triple hook 5",</p><p>`    `"serial": "1645654508",</p><p>`    `"purchase\_price": 1.75,</p><p>`    `"sale\_price": 5.4,</p><p>`    `"stock": 5,</p><p>`    `"range": "HIGH-RANGE",</p><p>`    `"id": 53</p><p>}</p>|
|422|<p>Error en datos enviados</p><p>{</p><p>`    `"message": "The name has already been taken. (and 1 more error)",</p><p>`    `"errors": {</p><p>`        `"name": [</p><p>`            `"The name has already been taken."</p><p>`        `],</p><p>`        `"serial": [</p><p>`            `"The serial has already been taken."</p><p>`        `]</p><p>`    `}</p><p>}</p>|
### **PUT /api/products/{id}.** Actualiza un producto existente
- **Parámetros**:

|Parámetro|Descripción|
| :- | :- |
|id: integer|ID del producto que se desea modificar|
|Body: JSON|<p>Objeto de tipo producto con datos que modifican el producto correspondiente</p><p>{</p><p>`    `"name": "Anzuelos triple hook 5",</p><p>`    `"serial": "1645654508",</p><p>`    `"purchase\_price": 1.75,</p><p>`    `"sale\_price": 5.40,</p><p>`    `"stock": 26</p><p>}</p>|

- **Responses**:

|Código|Descripción|
| :- | :- |
|202|<p>Operación aceptada y exitosa</p><p>Ejemplo</p><p>{</p><p>`    `"id": 53,</p><p>`    `"name": "Anzuelos triple hook 5",</p><p>`    `"serial": "1645654508",</p><p>`    `"purchase\_price": 1.75,</p><p>`    `"sale\_price": 5.4,</p><p>`    `"range": "HIGH-RANGE",</p><p>`    `"stock": 26</p><p>}</p>|
|404|Producto no encontrado|
|422|<p>Error en datos enviados</p><p>{</p><p>`    `"message": "The serial has already been taken.",</p><p>`    `"errors": {</p><p>`        `"serial": [</p><p>`            `"The serial has already been taken."</p><p>`        `]</p><p>`    `}</p><p>}</p>|
### **POST /api/products/update-stock.** Actualiza el inventario de una lista de productos existentes
- **Parámetros**:

|Parámetro|Descripción|
| :- | :- |
|Body: JSON|<p>Objeto que contiene la lista de productos a actualizar con sus cantidades</p><p>{</p><p>`    `"products": [</p><p>`        `{"id": 10, "quantity": 5},</p><p>`        `{"id": 34, "quantity": 1},</p><p>`        `{"id": 35, "quantity": -3},</p><p>`        `{"id": 63, "quantity": 6}</p><p>`    `]</p><p>}</p>|

- **Responses**:

|Código|Descripción|
| :- | :- |
|200|Operación satisfactoria. Devuelve cantidad de productos actualizados|
|422|<p>Error en datos enviados</p><p>{</p><p>`    `"message": "The products.2.quantity must be at least 0.",</p><p>`    `"errors": {</p><p>`        `"products.2.quantity": [</p><p>`            `"The products.2.quantity must be at least 0."</p><p>`        `]</p><p>`    `}</p><p>}</p>|
### **DELETE /api/products/{id}.** Elimina un producto existente
- **Parámetros**:

|Parámetro|Descripción|
| :- | :- |
|id: integer|ID del producto que se desea eliminar|

- **Responses**:

|Código|Descripción|
| :- | :- |
|204|Operación exitosa. No devuelve contenido|
|404|Producto no encontrado|
### **GET /api/products.** Devuelve un listado de los productos con existencia mayor que cero
- **Parámetros**:
- **Responses**:

|Código|Descripción|
| :- | :- |
|200|<p>Operación exitosa</p><p>Ejemplo</p><p>{</p><p>`    `"data": [</p><p>`        `{</p><p>`            `"id": 38,</p><p>`            `"name": "error amet eligendi labore",</p><p>`            `"serial": "4499727397",</p><p>`            `"purchase\_price": 23.65,</p><p>`            `"sale\_price": 27.3,</p><p>`            `"range": "MID-RANGE",</p><p>`            `"stock": 814</p><p>`        `}</p><p>`    `],</p><p>`    `"total": 20</p><p>}</p>|
### **GET /api/products/{id}.** Devuelve un producto en específico
- **Parámetros**:

|Parámetro|Descripción|
| :- | :- |
|id: integer|ID del producto|

- **Responses**:

|Código|Descripción|
| :- | :- |
|200|<p>Operación exitosa</p><p>Ejemplo</p><p>{</p><p>`    `"id": 33,</p><p>`    `"name": "quibusdam labore ipsa harum",</p><p>`    `"serial": "1516438119",</p><p>`    `"purchase\_price": 532.97,</p><p>`    `"sale\_price": 3495.35,</p><p>`    `"range": "HIGH-RANGE",</p><p>`    `"stock": 25</p><p>}</p>|
|404|Producto no encontrado|
## Ventas
### **POST /api/sales.** Adiciona una nueva venta con sus productos
- **Parámetros**:

|Parámetro|Descripción|
| :- | :- |
|Body: JSON|<p>Objeto de tipo venta que requiere ser almacenado</p><p>{</p><p>`    `"cashier": "Lulie Radmen",</p><p>`    `"products" : [</p><p>`        `{ "id": 38, "quantity": 20 },</p><p>`        `{ "id": 33, "quantity": 10 },</p><p>`        `{ "id": 39, "quantity": 1 }</p><p>`    `]</p><p>}</p>|

- **Responses**:

|Código|Descripción|
| :- | :- |
|201|<p>Venta creada</p><p>Ejemplo</p><p>{</p><p>`    `"cashier": "Lulie Radmen",</p><p>`    `"updated\_at": "2023-01-06T20:29:26.000000Z",</p><p>`    `"created\_at": "2023-01-06T20:29:26.000000Z",</p><p>`    `"id": 16,</p><p>`    `"products": [</p><p>`        `{</p><p>`            `"id": 38,</p><p>`            `"name": "error amet eligendi labore",</p><p>`            `"serial": "4499727397",</p><p>`            `"purchase\_price": 23.65,</p><p>`            `"sale\_price": 27.3,</p><p>`            `"range": "MID-RANGE",</p><p>`            `"stock": 814,</p><p>`            `"sold": {</p><p>`                `"sale\_id": 16,</p><p>`                `"product\_id": 38,</p><p>`                `"quantity": 20</p><p>`            `}</p><p>`        `},</p><p>`        `{</p><p>`            `"id": 33,</p><p>`            `"name": "quibusdam labore ipsa harum",</p><p>`            `"serial": "1516438119",</p><p>`            `"purchase\_price": 532.97,</p><p>`            `"sale\_price": 3495.35,</p><p>`            `"range": "HIGH-RANGE",</p><p>`            `"stock": 25,</p><p>`            `"sold": {</p><p>`                `"sale\_id": 16,</p><p>`                `"product\_id": 33,</p><p>`                `"quantity": 10</p><p>`            `}</p><p>`        `},</p><p>`        `{</p><p>`            `"id": 39,</p><p>`            `"name": "id voluptas praesentium odit",</p><p>`            `"serial": "8480081001",</p><p>`            `"purchase\_price": 968.05,</p><p>`            `"sale\_price": 1900.24,</p><p>`            `"range": "MID-RANGE",</p><p>`            `"stock": 533,</p><p>`            `"sold": {</p><p>`                `"sale\_id": 16,</p><p>`                `"product\_id": 39,</p><p>`                `"quantity": 1</p><p>`            `}</p><p>`        `}</p><p>`    `]</p><p>}</p>|
|422|<p>Error en datos enviados</p><p>{</p><p>`    `"message": "The cashier field is required.",</p><p>`    `"errors": {</p><p>`        `"cashier": [</p><p>`            `"The cashier field is required."</p><p>`        `]</p><p>`    `}</p><p>}</p>|
### **PUT /api/sales/{id}.** Actualiza una venta existente
- **Parámetros**:

|Parámetro|Descripción|
| :- | :- |
|id: integer|ID de la venta que se desea actualizar|
|Body: JSON|<p>Objeto de tipo venta con datos que modifican la venta correspondiente</p><p>{</p><p>`    `"cashier": "Willie Midsone",</p><p>`    `"products" : [</p><p>`        `{ "id": 34, "quantity": 3 },</p><p>`        `{ "id": 35, "quantity": 10 },</p><p>`        `{ "id": 51, "quantity": 1 }</p><p>`    `]</p><p>}</p>|

- **Responses**:

|Código|Descripción|
| :- | :- |
|202|<p>Operación aceptada y exitosa</p><p>Ejemplo</p><p>{</p><p>`    `"id": 12,</p><p>`    `"cashier": "Willie Midsone",</p><p>`    `"created\_at": "2023-01-06T13:35:28.000000Z",</p><p>`    `"updated\_at": "2023-01-06T16:32:45.000000Z",</p><p>`    `"products": [</p><p>`        `{</p><p>`            `"id": 34,</p><p>`            `"name": "doloribus nihil aut qui",</p><p>`            `"serial": "2979556919",</p><p>`            `"purchase\_price": 889.01,</p><p>`            `"sale\_price": 3286.05,</p><p>`            `"range": "HIGH-RANGE",</p><p>`            `"stock": 4,</p><p>`            `"sold": {</p><p>`                `"sale\_id": 12,</p><p>`                `"product\_id": 34,</p><p>`                `"quantity": 3</p><p>`            `}</p><p>`        `},</p><p>`        `{</p><p>`            `"id": 35,</p><p>`            `"name": "eligendi veritatis amet tempora",</p><p>`            `"serial": "2260095756",</p><p>`            `"purchase\_price": 903.58,</p><p>`            `"sale\_price": 4677.09,</p><p>`            `"range": "HIGH-RANGE",</p><p>`            `"stock": 647,</p><p>`            `"sold": {</p><p>`                `"sale\_id": 12,</p><p>`                `"product\_id": 35,</p><p>`                `"quantity": 10</p><p>`            `}</p><p>`        `},</p><p>`        `{</p><p>`            `"id": 51,</p><p>`            `"name": "Anzuelos triple hook 2",</p><p>`            `"serial": "1645654501",</p><p>`            `"purchase\_price": 1.75,</p><p>`            `"sale\_price": 5.4,</p><p>`            `"range": "HIGH-RANGE",</p><p>`            `"stock": 4,</p><p>`            `"sold": {</p><p>`                `"sale\_id": 12,</p><p>`                `"product\_id": 51,</p><p>`                `"quantity": 1</p><p>`            `}</p><p>`        `}</p><p>`    `]</p><p>}</p>|
|404|Venta no encontrada|
|422|<p>Error en datos enviados</p><p>{</p><p>`    `"message": "Invalid product ID",</p><p>`    `"errors": {</p><p>`        `"product.59": [</p><p>`            `"The product.59 does not exists."</p><p>`        `]</p><p>`    `}</p><p>}</p>|
### **POST /api/sales/report.** Crea y devuelve un reporte de ventas totales
- **Parámetros**:
- **Responses**:

|Código|Descripción|
| :- | :- |
|200|<p>Operación satisfactoria. </p><p>Devuelve PDF con reporte de ventas de acuerdo a formato requerido</p>|
### **DELETE /api/sales/{id}.** Elimina una venta existente y sus productos asociados en la tabla pivote
- **Parámetros**:

|Parámetro|Descripción|
| :- | :- |
|id: integer|ID de la venta a eliminar|

- **Responses**:

|Código|Descripción|
| :- | :- |
|204|Operación exitosa. No devuelve contenido|
|404|Venta no encontrada|
### **GET /api/sales.** Devuelve un listado de las ventas existentes con sus productos
- **Parámetros**:
- **Responses**:

|Código|Descripción|
| :- | :- |
|200|<p>Operación exitosa</p><p>Ejemplo</p><p>{</p><p>`    `"data": [</p><p>`        `{</p><p>`            `"id": 1,</p><p>`            `"cashier": "Oceane Cummerata",</p><p>`            `"created\_at": "2023-01-06T02:04:46.000000Z",</p><p>`            `"updated\_at": "2023-01-06T02:04:46.000000Z",</p><p>`            `"products": [</p><p>`                `{</p><p>`                    `"id": 35,</p><p>`                    `"name": "eligendi veritatis amet tempora",</p><p>`                    `"serial": "2260095756",</p><p>`                    `"purchase\_price": 903.58,</p><p>`                    `"sale\_price": 4677.09,</p><p>`                    `"range": "HIGH-RANGE",</p><p>`                    `"stock": 652,</p><p>`                    `"sold": {</p><p>`                        `"sale\_id": 1,</p><p>`                        `"product\_id": 35,</p><p>`                        `"quantity": 74</p><p>`                    `}</p><p>`                `},</p><p>`                `{</p><p>`                    `"id": 31,</p><p>`                    `"name": "incidunt sed rerum deleniti",</p><p>`                    `"serial": "3795560088",</p><p>`                    `"purchase\_price": 786.23,</p><p>`                    `"sale\_price": 1795.41,</p><p>`                    `"range": "HIGH-RANGE",</p><p>`                    `"stock": 254,</p><p>`                    `"sold": {</p><p>`                        `"sale\_id": 1,</p><p>`                        `"product\_id": 31,</p><p>`                        `"quantity": 2</p><p>`                    `}</p><p>`                `}</p><p>`            `]</p><p>`        `}</p><p>`    `],</p><p>`    `"total": 1</p><p>}</p><p></p>|
### **GET /api/sales/{id}.** Devuelve los datos de una venta en específico incluyendo sus productos
- **Parámetros**:

|Parámetro|Descripción|
| :- | :- |
|id: integer|ID de la venta|

- **Responses**:

|Código|Descripción|
| :- | :- |
|200|<p>Operación exitosa</p><p>Ejemplo</p><p>{</p><p>`    `"id": 12,</p><p>`    `"cashier": "Willie Midsone",</p><p>`    `"created\_at": "2023-01-06T13:35:28.000000Z",</p><p>`    `"updated\_at": "2023-01-06T16:32:45.000000Z",</p><p>`    `"products": [</p><p>`        `{</p><p>`            `"id": 34,</p><p>`            `"name": "doloribus nihil aut qui",</p><p>`            `"serial": "2979556919",</p><p>`            `"purchase\_price": 889.01,</p><p>`            `"sale\_price": 3286.05,</p><p>`            `"range": "HIGH-RANGE",</p><p>`            `"stock": 2,</p><p>`            `"sold": {</p><p>`                `"sale\_id": 12,</p><p>`                `"product\_id": 34,</p><p>`                `"quantity": 5</p><p>`            `}</p><p>`        `},</p><p>`        `{</p><p>`            `"id": 35,</p><p>`            `"name": "eligendi veritatis amet tempora",</p><p>`            `"serial": "2260095756",</p><p>`            `"purchase\_price": 903.58,</p><p>`            `"sale\_price": 4677.09,</p><p>`            `"range": "HIGH-RANGE",</p><p>`            `"stock": 652,</p><p>`            `"sold": {</p><p>`                `"sale\_id": 12,</p><p>`                `"product\_id": 35,</p><p>`                `"quantity": 5</p><p>`            `}</p><p>`        `}</p><p>`    `]</p><p>}</p>|
|404|Venta no encontrada|
###
###
###
