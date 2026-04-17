# Presupuestos Vidrio y Aluminio / Pressupostos Vidre i Alumini

Aplicación web en PHP + MySQL para crear presupuestos con dibujo técnico SVG dinámico.
Aplicació web en PHP + MySQL per crear pressupostos amb dibuix tècnic SVG dinàmic.

## Funciones / Funcions

- Alta de presupuesto con cliente y notas.
- Selector visual de sistema (corredera, abatible, fijo, oscilobatiente).
- Dibujo técnico generado en tiempo real con cotas.
- Cálculo automático de aluminio, vidrio, margen, IVA y total.
- Guardado en MySQL.
- Historial y detalle de presupuestos.
- Duplicado de presupuestos con un clic.
- Impresión limpia para PDF con dibujo técnico.

## Instalación en Dinahosting / Instal·lació a Dinahosting

1. Crea una base de datos MySQL en tu panel de Dinahosting.
2. Importa el archivo `database.sql`.
3. Copia `config.php.example` a `config.php`.
4. Edita `config.php` con host, puerto, base de datos, usuario y contraseña.
5. Sube todos los archivos al dominio (carpeta pública).
6. Abre `index.php` en el navegador.

## Uso en GitHub Codespaces / Ús a GitHub Codespaces

1. Sube este proyecto a un repositorio de GitHub.
2. En GitHub, abre el repositorio y crea un Codespace.
3. Espera a que termine la preparación automática del contenedor.
4. El proyecto arranca con PHP y MariaDB ya configurados.
5. Cuando GitHub reenvíe el puerto 8000, abre la URL pública.
6. Cada cambio que guardes en el código se verá al recargar la página.

### Qué hace la configuración automática

- Crea `config.php` si no existe.
- Conecta la aplicación a MariaDB dentro del propio Codespace.
- Importa `database.sql` automáticamente.
- Arranca el servidor PHP en el puerto 8000.

### Nota importante

- No hay compilación frontend ni hot reload real; al guardar cambios solo necesitas refrescar el navegador para verlos.

## Estructura

- `index.php`: formulario principal con selector y vista técnica.
- `save_quote.php`: guarda presupuesto en MySQL.
- `list_quotes.php`: listado histórico.
- `view_quote.php`: detalle con dibujo.
- `duplicate_quote.php`: clonado rápido de presupuesto existente.
- `lib/db.php`: conexión PDO.
- `lib/helpers.php`: utilidades y cálculo de importes.
- `assets/app.js`: motor de dibujo SVG + cálculo en cliente.
- `assets/styles.css`: estilos.

## Siguiente mejora recomendada / Millora següent recomanada

- PDF profesional con logo y firma.
- Gestión de tarifas por serie y proveedor.
- Gestión multiusuario con login.
