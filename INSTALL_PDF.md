# Instalación del Sistema de Generación de PDF

## Requisitos Previos

- PHP 7.4 o superior
- Composer instalado
- Extensiones PHP: mbstring, gd, dom

## Paso 1: Instalar Composer (si no está instalado)

### Windows
1. Descargar desde: https://getcomposer.org/Composer-Setup.exe
2. Ejecutar el instalador
3. Seguir las instrucciones

### Linux/Mac
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## Paso 2: Instalar Dompdf

Desde la raíz del proyecto, ejecutar:

```bash
composer install
```

Esto instalará Dompdf y todas sus dependencias según el archivo `composer.json`.

## Paso 3: Verificar la Instalación

Después de la instalación, deberías ver:
- Carpeta `vendor/` en la raíz del proyecto
- Archivo `vendor/autoload.php`
- Carpeta `vendor/dompdf/`

## Paso 4: Verificar Extensiones PHP

Asegúrate de que las siguientes extensiones estén habilitadas en `php.ini`:

```ini
extension=mbstring
extension=gd
extension=dom
```

Para verificar, ejecuta:

```bash
php -m | grep -E "mbstring|gd|dom"
```

## Endpoints de PDF Disponibles

Una vez instalado, los siguientes endpoints estarán disponibles:

### Para Administradores

1. **Reporte de Ingresos (PDF)**
   - URL: `/pdf/income?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD`
   - Ejemplo: `http://localhost/condominio/pdf/income?start_date=2024-01-01&end_date=2024-12-31`

2. **Reporte de Pagos Pendientes (PDF)**
   - URL: `/pdf/pending-payments`
   - Ejemplo: `http://localhost/condominio/pdf/pending-payments`

3. **Reporte de Incidencias (PDF)**
   - URL: `/pdf/incidents?start_date=YYYY-MM-DD&end_date=YYYY-MM-DD&status=`
   - Ejemplo: `http://localhost/condominio/pdf/incidents?start_date=2024-01-01&end_date=2024-12-31`

### Para Residentes y Administradores

4. **Comprobante de Pago (PDF)**
   - URL: `/pdf/payment-receipt/{id}`
   - Ejemplo: `http://localhost/condominio/pdf/payment-receipt/1`

5. **Reporte de Incidencia (PDF)**
   - URL: `/pdf/incident-receipt/{id}`
   - Ejemplo: `http://localhost/condominio/pdf/incident-receipt/1`

## Actualización de las Vistas

Todos los botones `window.print()` han sido reemplazados por botones "Descargar PDF" que apuntan a los nuevos endpoints de generación de PDF.

### Vistas Actualizadas

- ✅ `app/views/payments/show.php` - Comprobante de pago individual
- ✅ `app/views/incidents/show.php` - Reporte de incidencia individual
- ✅ `app/views/admin/reports/income.php` - Reporte de ingresos con filtros de fecha
- ✅ `app/views/admin/reports/pending_payments.php` - Reporte de pagos pendientes
- ✅ `app/views/admin/reports/incidents.php` - Reporte de incidencias con filtros
- ✅ `app/views/admin/reports/custom_result.php` - Reportes personalizados (dinámico según tipo)
- ✅ `app/views/admin/reports/dashboard.php` - Dashboard estadístico (botón removido)

## Solución de Problemas

### Error: "Class 'Dompdf\Dompdf' not found"

**Solución**: Ejecutar `composer install` desde la raíz del proyecto.

### Error: "Call to undefined function mb_strlen()"

**Solución**: Habilitar la extensión `mbstring` en `php.ini`:
```ini
extension=mbstring
```

### Error: "Call to undefined function imagecreatetruecolor()"

**Solución**: Habilitar la extensión `gd` en `php.ini`:
```ini
extension=gd
```

### Los PDFs se ven mal o sin estilos

**Solución**: Asegúrate de que:
1. Los estilos CSS estén inline en el HTML
2. No uses CSS externo o JavaScript
3. Usa rutas absolutas para imágenes

### Error de permisos

**Solución**: Dar permisos de escritura a la carpeta `vendor/dompdf/dompdf/lib/fonts`:
```bash
chmod -R 755 vendor/dompdf/dompdf/lib/fonts
```

## Personalización

### Cambiar el Tamaño del Papel

En `app/services/PdfService.php`, puedes cambiar el tamaño del papel:

```php
// Opciones disponibles: 'letter', 'legal', 'A4', 'A3', etc.
$this->generateFromHtml($html, $filename, 'portrait', 'A4');
```

### Cambiar la Orientación

```php
// 'portrait' (vertical) o 'landscape' (horizontal)
$this->generateFromHtml($html, $filename, 'landscape', 'letter');
```

### Personalizar los Estilos

Los estilos CSS están definidos en cada método `get*Html()` dentro de `PdfService.php`. Puedes modificarlos según tus necesidades.

## Archivos Creados

- `composer.json` - Configuración de dependencias
- `app/services/PdfService.php` - Servicio de generación de PDF
- `app/controllers/PdfController.php` - Controlador de endpoints PDF
- `INSTALL_PDF.md` - Este archivo de instrucciones

## Archivos Modificados

- `index.php` - Agregadas rutas de PDF y carga de PdfService/PdfController

## Próximos Pasos

1. Ejecutar `composer install`
2. Verificar que las extensiones PHP estén habilitadas
3. Probar los endpoints de PDF
4. Actualizar las vistas para usar los nuevos botones de PDF

## Notas Importantes

- Los PDFs se generan en el servidor y se descargan automáticamente
- No se requiere JavaScript para generar los PDFs
- Los PDFs son compatibles con todos los navegadores
- Los estilos CSS deben estar inline (no externos)
- Las imágenes deben usar rutas absolutas o base64

---

**Última actualización**: 2026-04-07
**Versión**: 1.0.0
