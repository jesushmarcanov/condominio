# Script PowerShell para ejecutar la detección de pagos vencidos
# Alternativa al archivo .bat con mejor manejo de errores

# Configuración
$projectPath = "C:\xampp\htdocs\condominio"
$phpPath = "C:\xampp\php\php.exe"
$scriptPath = "$projectPath\check_overdue_payments.php"
$logPath = "$projectPath\logs\cron_execution.log"

# Cambiar al directorio del proyecto
Set-Location $projectPath

# Registrar inicio
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
Add-Content -Path $logPath -Value "[$timestamp] Iniciando ejecución del script..."

try {
    # Ejecutar el script PHP
    $output = & $phpPath $scriptPath 2>&1
    
    # Registrar resultado
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Add-Content -Path $logPath -Value "[$timestamp] Script ejecutado exitosamente"
    Add-Content -Path $logPath -Value $output
    
    # Código de salida exitoso
    exit 0
    
} catch {
    # Registrar error
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    Add-Content -Path $logPath -Value "[$timestamp] ERROR: $($_.Exception.Message)"
    
    # Código de salida con error
    exit 1
}
