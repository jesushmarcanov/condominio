# Script PowerShell para configurar el Programador de Tareas
# IMPORTANTE: Ejecutar como Administrador

# Verificar permisos de administrador
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

if (-not $isAdmin) {
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "ERROR: Este script debe ejecutarse como Administrador" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Por favor:" -ForegroundColor Yellow
    Write-Host "1. Cierra esta ventana"
    Write-Host "2. Haz clic derecho en setup_cron_job.ps1"
    Write-Host "3. Selecciona 'Ejecutar con PowerShell' como administrador"
    Write-Host ""
    Read-Host "Presiona Enter para salir"
    exit 1
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Configurando Cron Job para ResiTech" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "[OK] Ejecutando con permisos de administrador" -ForegroundColor Green
Write-Host ""

# Configuración
$taskName = "ResiTech-Pagos-Vencidos"
$taskPath = "\ResiTech\"
$scriptPath = "C:\xampp\htdocs\condominio\run_overdue_payments.bat"
$workingDir = "C:\xampp\htdocs\condominio"

# Eliminar tarea existente si existe
try {
    Unregister-ScheduledTask -TaskName $taskName -TaskPath $taskPath -Confirm:$false -ErrorAction SilentlyContinue
    Write-Host "[INFO] Tarea anterior eliminada" -ForegroundColor Yellow
} catch {
    # Ignorar si no existe
}

Write-Host "Creando tarea programada..." -ForegroundColor Cyan
Write-Host ""

try {
    # Crear acción
    $action = New-ScheduledTaskAction -Execute $scriptPath -WorkingDirectory $workingDir
    
    # Crear desencadenador (trigger) - Diariamente a las 8:00 AM
    $trigger = New-ScheduledTaskTrigger -Daily -At 8:00AM
    
    # Configuración de la tarea
    $settings = New-ScheduledTaskSettingsSet `
        -AllowStartIfOnBatteries `
        -DontStopIfGoingOnBatteries `
        -StartWhenAvailable `
        -RunOnlyIfNetworkAvailable:$false `
        -MultipleInstances IgnoreNew
    
    # Crear principal (usuario que ejecuta)
    $principal = New-ScheduledTaskPrincipal -UserId "SYSTEM" -LogonType ServiceAccount -RunLevel Highest
    
    # Registrar la tarea
    Register-ScheduledTask `
        -TaskName $taskName `
        -TaskPath $taskPath `
        -Action $action `
        -Trigger $trigger `
        -Settings $settings `
        -Principal $principal `
        -Description "Detecta pagos vencidos y envía notificaciones por email automáticamente" `
        -Force | Out-Null
    
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "[EXITO] Tarea creada correctamente" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Configuración:" -ForegroundColor Cyan
    Write-Host "- Nombre: $taskName"
    Write-Host "- Ruta: $taskPath"
    Write-Host "- Frecuencia: Diaria"
    Write-Host "- Hora: 08:00 AM"
    Write-Host "- Script: $scriptPath"
    Write-Host "- Usuario: SYSTEM (privilegios altos)"
    Write-Host ""
    Write-Host "La tarea se ejecutará automáticamente cada día a las 8:00 AM" -ForegroundColor Yellow
    Write-Host ""
    
    # Probar la tarea
    Write-Host "Ejecutando tarea manualmente para prueba..." -ForegroundColor Cyan
    Start-ScheduledTask -TaskName $taskName -TaskPath $taskPath
    
    Write-Host "[OK] Tarea ejecutada manualmente" -ForegroundColor Green
    Write-Host ""
    Write-Host "Esperando 5 segundos para que se complete..." -ForegroundColor Yellow
    Start-Sleep -Seconds 5
    
    # Verificar resultado
    $task = Get-ScheduledTask -TaskName $taskName -TaskPath $taskPath
    $taskInfo = Get-ScheduledTaskInfo -TaskName $taskName -TaskPath $taskPath
    
    Write-Host "Estado de la tarea:" -ForegroundColor Cyan
    Write-Host "- Estado: $($task.State)"
    Write-Host "- Última ejecución: $($taskInfo.LastRunTime)"
    Write-Host "- Último resultado: $($taskInfo.LastTaskResult)"
    Write-Host "- Próxima ejecución: $($taskInfo.NextRunTime)"
    Write-Host ""
    
    Write-Host "Para verificar la tarea:" -ForegroundColor Cyan
    Write-Host "1. Presiona Windows + R"
    Write-Host "2. Escribe: taskschd.msc"
    Write-Host "3. Busca '$taskName' en la carpeta 'ResiTech'"
    Write-Host ""
    Write-Host "Para ver los logs:" -ForegroundColor Cyan
    Write-Host "- Logs de ejecución: logs\cron_execution.log"
    Write-Host "- Logs de emails: logs\emails\email_YYYY-MM-DD.log"
    Write-Host ""
    
} catch {
    Write-Host "========================================" -ForegroundColor Red
    Write-Host "[ERROR] No se pudo crear la tarea" -ForegroundColor Red
    Write-Host "========================================" -ForegroundColor Red
    Write-Host ""
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Posibles soluciones:" -ForegroundColor Yellow
    Write-Host "1. Asegúrate de ejecutar como Administrador"
    Write-Host "2. Verifica que la ruta sea correcta: $scriptPath"
    Write-Host "3. Intenta crear la tarea manualmente desde taskschd.msc"
    Write-Host ""
    Read-Host "Presiona Enter para salir"
    exit 1
}

Write-Host "========================================" -ForegroundColor Green
Write-Host "Configuración completada exitosamente" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

Read-Host "Presiona Enter para salir"
