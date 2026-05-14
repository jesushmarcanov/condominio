@echo off
REM Script para configurar el Programador de Tareas
REM IMPORTANTE: Ejecutar como Administrador (clic derecho -> Ejecutar como administrador)

echo ========================================
echo Configurando Cron Job para CondoWeb
echo ========================================
echo.

REM Verificar si se está ejecutando como administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Este script debe ejecutarse como Administrador
    echo.
    echo Por favor:
    echo 1. Cierra esta ventana
    echo 2. Haz clic derecho en setup_cron_job.bat
    echo 3. Selecciona "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

echo [OK] Ejecutando con permisos de administrador
echo.

REM Eliminar tarea existente si existe (ignorar errores)
schtasks /delete /tn "CondoWeb-Pagos-Vencidos" /f >nul 2>&1

echo Creando tarea programada...
echo.

REM Crear la tarea programada
schtasks /create /tn "CondoWeb-Pagos-Vencidos" /tr "C:\xampp\htdocs\condominio\run_overdue_payments.bat" /sc daily /st 08:00 /ru SYSTEM /rl HIGHEST /f

if %errorLevel% equ 0 (
    echo.
    echo ========================================
    echo [EXITO] Tarea creada correctamente
    echo ========================================
    echo.
    echo Configuracion:
    echo - Nombre: CondoWeb-Pagos-Vencidos
    echo - Frecuencia: Diaria
    echo - Hora: 08:00 AM
    echo - Script: run_overdue_payments.bat
    echo.
    echo La tarea se ejecutara automaticamente cada dia a las 8:00 AM
    echo.
    echo Para verificar la tarea:
    echo 1. Presiona Windows + R
    echo 2. Escribe: taskschd.msc
    echo 3. Busca "CondoWeb-Pagos-Vencidos" en la lista
    echo.
    echo Para probar ahora:
    schtasks /run /tn "CondoWeb-Pagos-Vencidos"
    echo.
    echo [OK] Tarea ejecutada manualmente para prueba
    echo.
    echo Revisa el archivo logs\cron_execution.log para ver el resultado
    echo.
) else (
    echo.
    echo ========================================
    echo [ERROR] No se pudo crear la tarea
    echo ========================================
    echo.
    echo Codigo de error: %errorLevel%
    echo.
    echo Posibles soluciones:
    echo 1. Asegurate de ejecutar como Administrador
    echo 2. Verifica que la ruta sea correcta
    echo 3. Intenta crear la tarea manualmente desde taskschd.msc
    echo.
)

pause
