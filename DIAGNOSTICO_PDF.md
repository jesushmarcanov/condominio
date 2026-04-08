# Diagnóstico: PDF no carga información

## Problema Reportado

"Al probar algún reporte, por ejemplo, el reporte de ingresos, crea el archivo, abre el navegador, sin embargo no carga la información"

## Tests Realizados

### ✅ Test 1: Instalación de Dompdf
```bash
$ php test_pdf.php
✅ Dompdf está instalado correctamente
✅ PdfService se instanció correctamente
✅ PDF generado exitosamente: test_pdf_output.pdf
✅ Todas las extensiones PHP habilitadas
```

### ✅ Test 2: Datos en la Base de Datos
```bash
$ php test_pdf_data.php
✅ Conexión a base de datos exitosa
✅ Ingresos: 1 registros
✅ Pagos pendientes: 4 registros
✅ Incidencias: 0 registros
```

### ✅ Test 3: Generación de HTML
```bash
$ php test_html_output.php
✅ HTML generado correctamente
✅ Archivo test_income_report.html creado
✅ Contiene todos los datos correctamente
```

## Posibles Causas

### 1. Problema de Autenticación (MÁS PROBABLE)

**Síntoma**: El navegador abre una pestaña nueva pero muestra un PDF vacío o en blanco.

**Causa**: Cuando haces click en "Descargar PDF", el navegador abre una nueva pestaña con la URL del PDF. Si la sesión no se comparte correctamente entre pestañas, el sistema te redirige al login en lugar de generar el PDF.

**Solución**:
1. Asegúrate de estar logueado como administrador
2. Verifica que las cookies de sesión estén habilitadas
3. Intenta hacer click derecho en el botón → "Guardar enlace como..." en lugar de abrirlo en nueva pestaña

### 2. Problema de Target="_blank"

**Síntoma**: El PDF se genera pero el navegador no lo muestra correctamente.

**Causa**: Los botones tienen `target="_blank"` que abre en nueva pestaña, y algunos navegadores tienen problemas con las cookies de sesión en pestañas nuevas.

**Solución**: Remover el `target="_blank"` de los botones PDF.

### 3. Problema de Headers

**Síntoma**: El navegador recibe el PDF pero no lo procesa correctamente.

**Causa**: Algún output antes de los headers del PDF.

**Solución**: Verificar que no haya espacios o echo antes de generar el PDF.

## Solución Recomendada

### Opción A: Remover target="_blank" de los botones

Esto hará que el PDF se descargue en la misma pestaña en lugar de abrir una nueva:

```php
<!-- ANTES -->
<a href="<?= APP_URL ?>/pdf/income?..." class="btn btn-danger" target="_blank">
    <i class="fas fa-file-pdf"></i> Descargar PDF
</a>

<!-- DESPUÉS -->
<a href="<?= APP_URL ?>/pdf/income?..." class="btn btn-danger">
    <i class="fas fa-file-pdf"></i> Descargar PDF
</a>
```

### Opción B: Cambiar el método de descarga

En lugar de usar `stream` con `Attachment => true`, podríamos:

1. Generar el PDF
2. Guardarlo temporalmente
3. Enviarlo con headers correctos
4. Eliminar el archivo temporal

### Opción C: Usar JavaScript para descargar

Usar JavaScript para hacer la petición y descargar el archivo:

```javascript
function descargarPDF(url) {
    fetch(url, {
        credentials: 'same-origin' // Incluir cookies de sesión
    })
    .then(response => response.blob())
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'reporte.pdf';
        a.click();
    });
}
```

## Prueba Rápida

Para verificar si el problema es de autenticación:

1. **Abre el navegador**
2. **Inicia sesión** en http://localhost/condominio/login
3. **En la MISMA pestaña**, pega esta URL en la barra de direcciones:
   ```
   http://localhost/condominio/pdf/income?start_date=2026-04-01&end_date=2026-04-07
   ```
4. **Presiona Enter**

Si el PDF se descarga correctamente, el problema es el `target="_blank"`.

Si el PDF sigue vacío, el problema es otro.

## Siguiente Paso

¿Quieres que:

1. **Remueva el `target="_blank"`** de todos los botones PDF? (Solución rápida)
2. **Implemente un sistema de descarga con JavaScript**? (Solución más robusta)
3. **Investigue más** el problema específico que estás teniendo?

Por favor, prueba acceder directamente a la URL del PDF (sin hacer click en el botón) y dime qué pasa.

---

**Fecha**: 2026-04-07  
**Estado**: Esperando feedback del usuario
