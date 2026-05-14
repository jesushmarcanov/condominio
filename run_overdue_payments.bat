@echo off
REM Script para ejecutar la detección de pagos vencidos
REM Este script se ejecutará automáticamente mediante el Programador de tareas de Windows

REM Cambiar al directorio del proyecto
cd /d C:\xampp\htdocs\condominio

REM Ejecutar el script PHP
C:\xampp\php\php.exe check_overdue_payments.php

REM Registrar la ejecución
echo [%date% %time%] Script ejecutado >> logs\cron_execution.log
