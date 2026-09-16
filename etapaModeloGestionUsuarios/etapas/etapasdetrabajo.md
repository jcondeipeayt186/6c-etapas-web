# **Etapas de trabajo:**

### **Etapa 1\. Análisis de requerimientos y funcionalidades**

En esta etapa se analizará el problema que se desea resolver y se definirán las funcionalidades que tendrá la aplicación web.

Los estudiantes deberán elaborar un **documento** que incluya:

* Descripción general del sistema y del problema que se busca resolver. Para ello resultará necesario realizar entrevistas a los actores de las áreas pertinentes para recolectar datos.  
* Detalle de las funcionalidades que deberá implementar el sistema.  
* **Bosquejos o bocetos de las principales interfaces gráficas de la aplicación** (describir qué elementos HTML tiene cada página web).  
  * Explicación de cada bosquejo  
* Descripción general de la navegación entre las diferentes páginas o secciones.  
* Información que será necesario almacenar y gestionar (útil para cuando tengamos que diseñar la base de datos)

El objetivo de esta etapa es establecer claramente **qué debe hacer el sistema antes de comenzar con su desarrollo**.

**Entregable**: En la carpeta del grupo de trabajo entregar un archivo compactado (zip) con el nombre etapa1.zip que contenga:

* Documento   
* Bocetos (Imágenes o pdf o hacer en papel y sacar foto)  
* Además, mostrar lo realizado a los destinatarios del producto final, y tener el ok (o adaptar lo necesario)

### **Etapa 2\. Diseño de la base de datos**

Primero deberían identificar **qué información necesita almacenar el sistema**.

Este trabajo será realizado de manera general entre todos, en clase, ya que todos utilizarán la misma base de datos, de todos modos, cada grupo hará lo siguiente y luego entre todos unificamos en una sola base de datos.

Podrían realizar:

* Identificación de entidades.  
* Identificación de atributos.  
* Identificación de relaciones entre entidades.  
* Determinación de cardinalidades.  
* Elaboración del Diagrama Entidad Relación (DER). Notación Chen   
* Elaboración del **modelo entidad-relación (MER)**: Tabulación del DER.   
  * Identificación de claves primarias y foráneas.

**Entregable:** En la carpeta del grupo de trabajo entregar un archivo compactado (zip) con el nombre etapa2.zip que contenga:

* Diagrama Entidad Relación (DER). Notación Chen y   
* Tabulación (detalle de tablas, sus claves primarias y foráneas).

### **Etapa 3\. Implementación de la base de datos**

Una vez definido el modelo, deberían implementarlo en MySQL. Utilizando PHPMyAdmin como cliente gráfico de MySQL hacer:

* Crear la base de datos.  
* Crear las tablas.  
* Definir claves primarias.  
* Definir claves foráneas.  
* Establecer restricciones.  
* Insertar datos de prueba.  
* Realizar consultas básicas para verificar que la estructura funciona correctamente.

**Entregable:** En la carpeta del grupo de trabajo entregar un archivo compactado (zip) con el nombre etapa3.zip que contenga:

* `baseDeDatos.sql` de la base de datos (estructura, su DDL) y archivo  
* `script-crud.sql` con Inserción de algunos datos iniciales (INSERT), modificaciones (UPDATE), eliminación (DELETE) y consultas SQL (SELECT).

### **Etapa 4\. Diseño de la interfaz (HTML y CSS \- Bootstrap)**

En esta etapa pasarían de los bocetos iniciales a un diseño más concreto de la aplicación.

Trabajarían:

* Organización de carpetas  
* Estructura de las páginas.  
* Menú de navegación.  
* Formularios.  
* Tablas para mostrar información.  
* Botones y acciones.  
* Mensajes de confirmación y error.  
* Diseño adaptable a diferentes tamaños de pantalla.  
* Utilización de componentes de **Bootstrap**.

No hace falta que todavía programen toda la funcionalidad.

**Entregable:** Diseño de las páginas correspondiente al módulo de la aplicación que cada grupo tenga asignado. 

En la carpeta del grupo de trabajo entregar: Archivo compactado (zip) de la carpeta del proyecto. Nombre del archivo: etapa4.zip

### **Etapa 5\. Desarrollo de la Aplicación Web (PHP)**

Esta sería la etapa principal de programación.

Yo la dividiría internamente en pequeños pasos:

**5.1. Estructura del proyecto**

* Re-Organización de carpetas y archivos (usar la estructura planteada).  
* Archivos PHP.  
* Archivos CSS.  
* Configuración de conexión a MySQL.  
* Archivos multimedia

**5.2. Conexión PHP–MySQL**

* Conexión a la base de datos.  
* Consultas. Recuperación de información. CRUD

**5.3. Módulos** 

* Páginas Frontend:   
  * Consultar un registro. Listar registros. Eliminar registros. (view)  
  * Agregar registros / Modificar registros. (edit)  
* Páginas Backend que implementan las funcionalidades propuestas (gestión)  
* Navegación:   
  * Integración de todas las páginas.  
  * Menús y enlaces.  
  * Flujo completo de la aplicación.

Acá Bootstrap se utiliza principalmente para construir la interfaz, mientras que **PHP se encarga de la lógica y MySQL de la persistencia de los datos**.

**Entregable**: Archivo compactado (zip) de la carpeta del proyecto. Nombre del archivo: etapa5.zip