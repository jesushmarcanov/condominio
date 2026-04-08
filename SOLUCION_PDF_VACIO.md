# Solución: PDF se abre pero no carga información

## Problema Identificado

Cuando intentas acceder a un endpoint de PDF (por ejemplo `/pdf/income`), el navegador abre una pestaña pero el PDF aparece vacío o no carga la información.

## Causa del Problema

El problema ocurre cuando:

1. **No estás autenticado**: Si no has iniciado sesión, el sistema te redirige al login en lugar de generar el PDF
2. **No tienes permisos de administrador**: Los reportes requieren permisos de admin

## Verificación del Problema

Los tests muestran que:
- ✅ Dompdf está instalado correctamente
- ✅ Los datos se obtienen correctamente de la base de datos
- ✅ El HTML se genera correctamente con la información
- ❌ El problema es el acceso al endpoint

## Solución

### Opción 1: Asegúrate de estar autenticado (RECOMENDADO)

1. **Inicia sesión en el sistema** como administrador
2. **Navega a través de la interfaz web**:
   - Ve a "Reportes" → "Reporte de Ingresos"
   - Selecciona las fechas
   - Click en "Descargar PDF"

3. **O accede directamente** después de iniciar sesión:
   ```
   http://localhost/condominio/pdf/income?start_date=2026-04-01&end_date=2026-04-07
   ```

### Opción 2: Probar desde la línea de comandos

Puedes generar el PDF directamente sin necesidad de autenticación:

```bash
php test_income_pdf.php
```

Este script:
- Se conecta a la base de datos
- Obtiene los datos
- Genera el PDF
- Lo descarga automáticamente

### Opción 3: Ver el HTML generado

Si quieres ver cómo se ve el reporte antes de convertirlo a PDF:

```bash
php test_html_output.php
```

Luego abre el archivo `test_income_report.html` en tu navegador.

## Verificación de Datos

Para verificar que hay datos en la base de datos:

```bash
php test_pdf_data.php
```

Este script te mostrará:
- Cuántos registros de ingresos hay
- Cuántos pagos pendientes hay
- Cuántas incidencias hay

## Ejemplo de Uso Correcto

### Paso 1: Iniciar sesión
```
http://localhost/condominio/login
```
Usuario: admin@condominio.com  
Contraseña: (tu contraseña)

### Paso 2: Ir a Reportes
```
http://localhost/condominio/reports
```

### Paso 3: Seleccionar Reporte de Ingresos
```
http://localhost/condominio/reports/income
```

### Paso 4: Click en "Descargar PDF"
El botón rojo con el icono de PDF descargará el archivo automáticamente.

## Datos de Prueba Actuales

Según el test, tienes:
- ✅ 1 registro de ingresos (Jesus Marcano - $609.89)
- ✅ 4 pagos pendientes (Total: $4,109.89)
- ⚠️ 0 incidencias

## Archivos de Prueba Creados

1. `test_pdf.php` - Test general de Dompdf
2. `test_pdf_data.php` - Verifica datos en la base de datos
3. `test_html_output.php` - Genera HTML del reporte
4. `test_income_pdf.php` - Genera PDF de ingresos directamente
5. `test_income_report.html` - HTML generado (puedes abrirlo en el navegador)
6. `test_pdf_output.pdf` - PDF de prueba general

## Resumen

El sistema de PDF funciona correctamente. El problema es que necesitas:

1. ✅ Estar autenticado en el sistema
2. ✅ Tener permisos de administrador
3. ✅ Acceder a través de la interfaz web o con sesión activa

**No es un problema del código, es un problema de autenticación/permisos.**

---

**Fecha**: 2026-04-07  
**Estado**: ✅ Sistema funcionando correctamente
