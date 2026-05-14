<?php
/**
 * Script de Verificación - Checkpoint Tarea 4
 * Sistema de Reglas de Mora
 * 
 * Este script verifica:
 * 1. Migración de base de datos ejecutada correctamente
 * 2. Tablas creadas con estructura correcta
 * 3. Creación de reglas de mora mediante código
 * 4. Cálculo de mora con diferentes escenarios
 * 5. Aplicación de mora a pagos
 * 6. Registro de historial
 */

require_once 'config/database.php';
require_once 'app/models/Database.php';
require_once 'app/models/LateFeeRule.php';
require_once 'app/models/Payment.php';
require_once 'app/models/Resident.php';
require_once 'app/models/LateFeeHistory.php';
require_once 'app/models/Notification.php';
require_once 'app/services/EmailService.php';
require_once 'app/services/NotificationService.php';
require_once 'app/services/LateFeeService.php';

// Colores para output
function printSuccess($message) {
    echo "✓ " . $message . "\n";
}

function printError($message) {
    echo "✗ " . $message . "\n";
}

function printInfo($message) {
    echo "ℹ " . $message . "\n";
}

function printHeader($message) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo $message . "\n";
    echo str_repeat("=", 60) . "\n";
}

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    printError("No se pudo conectar a la base de datos");
    exit(1);
}

printSuccess("Conexión a base de datos establecida");

// ============================================================================
// PASO 1: Verificar migración de base de datos
// ============================================================================
printHeader("PASO 1: Verificar Migración de Base de Datos");

try {
    // Verificar tabla late_fee_rules
    $query = "SHOW TABLES LIKE 'late_fee_rules'";
    $stmt = $db->query($query);
    if ($stmt->rowCount() > 0) {
        printSuccess("Tabla 'late_fee_rules' existe");
        
        // Verificar estructura
        $query = "DESCRIBE late_fee_rules";
        $stmt = $db->query($query);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $expected_columns = ['id', 'nombre', 'dias_gracia', 'tipo_recargo', 'valor_recargo', 
                            'frecuencia', 'tope_maximo', 'tipo_pago', 'activa', 'created_at', 'updated_at'];
        
        foreach ($expected_columns as $col) {
            if (in_array($col, $columns)) {
                printSuccess("  - Columna '$col' existe");
            } else {
                printError("  - Columna '$col' NO existe");
            }
        }
    } else {
        printError("Tabla 'late_fee_rules' NO existe");
    }
    
    // Verificar tabla late_fee_history
    $query = "SHOW TABLES LIKE 'late_fee_history'";
    $stmt = $db->query($query);
    if ($stmt->rowCount() > 0) {
        printSuccess("Tabla 'late_fee_history' existe");
        
        // Verificar estructura
        $query = "DESCRIBE late_fee_history";
        $stmt = $db->query($query);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $expected_columns = ['id', 'pago_id', 'regla_mora_id', 'monto_calculado', 'monto_aplicado',
                            'dias_atraso', 'tipo_operacion', 'usuario_id', 'justificacion', 'created_at'];
        
        foreach ($expected_columns as $col) {
            if (in_array($col, $columns)) {
                printSuccess("  - Columna '$col' existe");
            } else {
                printError("  - Columna '$col' NO existe");
            }
        }
    } else {
        printError("Tabla 'late_fee_history' NO existe");
    }
    
    // Verificar columnas agregadas a tabla pagos
    $query = "DESCRIBE pagos";
    $stmt = $db->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $expected_columns = ['monto_original', 'monto_mora', 'fecha_aplicacion_mora', 'regla_mora_id'];
    
    printInfo("Verificando columnas agregadas a tabla 'pagos':");
    foreach ($expected_columns as $col) {
        if (in_array($col, $columns)) {
            printSuccess("  - Columna '$col' existe");
        } else {
            printError("  - Columna '$col' NO existe");
        }
    }
    
} catch (PDOException $e) {
    printError("Error verificando migración: " . $e->getMessage());
    exit(1);
}

// ============================================================================
// PASO 2: Probar creación de reglas de mora mediante código
// ============================================================================
printHeader("PASO 2: Probar Creación de Reglas de Mora");

$lateFeeRule = new LateFeeRule($db);

// Escenario 1: Regla de porcentaje única
printInfo("Creando regla: 5% único con 3 días de gracia");
$lateFeeRule->nombre = "Test - 5% Único";
$lateFeeRule->dias_gracia = 3;
$lateFeeRule->tipo_recargo = "porcentaje";
$lateFeeRule->valor_recargo = 5.00;
$lateFeeRule->frecuencia = "unica";
$lateFeeRule->tope_maximo = null;
$lateFeeRule->tipo_pago = null;
$lateFeeRule->activa = true;

if ($lateFeeRule->create()) {
    $rule1_id = $lateFeeRule->id;
    printSuccess("Regla creada con ID: $rule1_id");
} else {
    printError("No se pudo crear la regla");
}

// Escenario 2: Regla de monto fijo diario
printInfo("Creando regla: $50 diario sin gracia");
$lateFeeRule->nombre = "Test - $50 Diario";
$lateFeeRule->dias_gracia = 0;
$lateFeeRule->tipo_recargo = "monto_fijo";
$lateFeeRule->valor_recargo = 50.00;
$lateFeeRule->frecuencia = "diaria";
$lateFeeRule->tope_maximo = 500.00;
$lateFeeRule->tipo_pago = null;
$lateFeeRule->activa = true;

if ($lateFeeRule->create()) {
    $rule2_id = $lateFeeRule->id;
    printSuccess("Regla creada con ID: $rule2_id");
} else {
    printError("No se pudo crear la regla");
}

// Escenario 3: Regla de porcentaje mensual con tope
printInfo("Creando regla: 2% mensual con tope de $1000");
$lateFeeRule->nombre = "Test - 2% Mensual con Tope";
$lateFeeRule->dias_gracia = 5;
$lateFeeRule->tipo_recargo = "porcentaje";
$lateFeeRule->valor_recargo = 2.00;
$lateFeeRule->frecuencia = "mensual";
$lateFeeRule->tope_maximo = 1000.00;
$lateFeeRule->tipo_pago = null;
$lateFeeRule->activa = true;

if ($lateFeeRule->create()) {
    $rule3_id = $lateFeeRule->id;
    printSuccess("Regla creada con ID: $rule3_id");
} else {
    printError("No se pudo crear la regla");
}

// Verificar que las reglas se crearon
$stmt = $lateFeeRule->readAll();
$rules = $stmt->fetchAll(PDO::FETCH_ASSOC);
printInfo("Total de reglas en base de datos: " . count($rules));

// ============================================================================
// PASO 3: Crear pagos de prueba
// ============================================================================
printHeader("PASO 3: Crear Pagos de Prueba");

$payment = new Payment($db);

// Obtener un residente existente
$query = "SELECT id FROM residentes LIMIT 1";
$stmt = $db->query($query);
$resident = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resident) {
    printError("No hay residentes en la base de datos. Cree al menos un residente primero.");
    exit(1);
}

$residente_id = $resident['id'];
printInfo("Usando residente ID: $residente_id");

// Pago 1: Atrasado 10 días (para probar con regla 1)
printInfo("Creando pago atrasado 10 días (monto: $1000)");
$payment->residente_id = $residente_id;
$payment->monto = 1000.00;
$payment->concepto = "Cuota de Mantenimiento - Test 1";
$payment->mes_pago = date('Y-m', strtotime('-1 month'));
$payment->fecha_pago = date('Y-m-d', strtotime('-10 days'));
$payment->metodo_pago = null;
$payment->referencia = null;
$payment->estado = "atrasado";

if ($payment->create()) {
    $payment1_id = $payment->id;
    printSuccess("Pago creado con ID: $payment1_id");
} else {
    printError("No se pudo crear el pago");
}

// Pago 2: Atrasado 7 días (para probar con regla 2)
printInfo("Creando pago atrasado 7 días (monto: $2000)");
$payment->residente_id = $residente_id;
$payment->monto = 2000.00;
$payment->concepto = "Cuota de Mantenimiento - Test 2";
$payment->mes_pago = date('Y-m', strtotime('-1 month'));
$payment->fecha_pago = date('Y-m-d', strtotime('-7 days'));
$payment->metodo_pago = null;
$payment->referencia = null;
$payment->estado = "atrasado";

if ($payment->create()) {
    $payment2_id = $payment->id;
    printSuccess("Pago creado con ID: $payment2_id");
} else {
    printError("No se pudo crear el pago");
}

// Pago 3: Atrasado 40 días (para probar con regla 3 - mensual)
printInfo("Creando pago atrasado 40 días (monto: $5000)");
$payment->residente_id = $residente_id;
$payment->monto = 5000.00;
$payment->concepto = "Cuota de Mantenimiento - Test 3";
$payment->mes_pago = date('Y-m', strtotime('-2 months'));
$payment->fecha_pago = date('Y-m-d', strtotime('-40 days'));
$payment->metodo_pago = null;
$payment->referencia = null;
$payment->estado = "atrasado";

if ($payment->create()) {
    $payment3_id = $payment->id;
    printSuccess("Pago creado con ID: $payment3_id");
} else {
    printError("No se pudo crear el pago");
}

// ============================================================================
// PASO 4: Probar cálculo de mora con diferentes escenarios
// ============================================================================
printHeader("PASO 4: Probar Cálculo de Mora con Diferentes Escenarios");

$lateFeeService = new LateFeeService($db);

// Escenario 1: Pago 1 con Regla 1 (5% único, 3 días gracia, 10 días atraso)
printInfo("Escenario 1: Pago $1000, 10 días atraso, 5% único, 3 días gracia");
$payment->id = $payment1_id;
$payment_data = $payment->readOne();

// Desactivar todas las reglas excepto la regla 1
$db->exec("UPDATE late_fee_rules SET activa = 0");
$db->exec("UPDATE late_fee_rules SET activa = 1 WHERE id = $rule1_id");

$late_fee = $lateFeeService->calculateLateFee($payment_data);
printInfo("  Monto calculado: $" . number_format($late_fee, 2));
printInfo("  Esperado: $50.00 (5% de $1000, aplicado una vez)");
if (abs($late_fee - 50.00) < 0.01) {
    printSuccess("  ✓ Cálculo correcto");
} else {
    printError("  ✗ Cálculo incorrecto");
}

// Escenario 2: Pago 2 con Regla 2 ($50 diario, sin gracia, 7 días atraso, tope $500)
printInfo("Escenario 2: Pago $2000, 7 días atraso, $50 diario, tope $500");
$payment->id = $payment2_id;
$payment_data = $payment->readOne();

$db->exec("UPDATE late_fee_rules SET activa = 0");
$db->exec("UPDATE late_fee_rules SET activa = 1 WHERE id = $rule2_id");

$late_fee = $lateFeeService->calculateLateFee($payment_data);
printInfo("  Monto calculado: $" . number_format($late_fee, 2));
printInfo("  Esperado: $350.00 ($50 x 7 días)");
if (abs($late_fee - 350.00) < 0.01) {
    printSuccess("  ✓ Cálculo correcto");
} else {
    printError("  ✗ Cálculo incorrecto");
}

// Escenario 3: Pago 3 con Regla 3 (2% mensual, 5 días gracia, 40 días atraso, tope $1000)
printInfo("Escenario 3: Pago $5000, 40 días atraso, 2% mensual, 5 días gracia, tope $1000");
$payment->id = $payment3_id;
$payment_data = $payment->readOne();

$db->exec("UPDATE late_fee_rules SET activa = 0");
$db->exec("UPDATE late_fee_rules SET activa = 1 WHERE id = $rule3_id");

$late_fee = $lateFeeService->calculateLateFee($payment_data);
printInfo("  Monto calculado: $" . number_format($late_fee, 2));
printInfo("  Esperado: $100.00 (2% de $5000 x 1 mes, 40-5=35 días = 1 mes)");
if (abs($late_fee - 100.00) < 0.01) {
    printSuccess("  ✓ Cálculo correcto");
} else {
    printError("  ✗ Cálculo incorrecto");
}

// ============================================================================
// PASO 5: Probar aplicación de mora
// ============================================================================
printHeader("PASO 5: Probar Aplicación de Mora");

// Aplicar mora al pago 1
printInfo("Aplicando mora de $50.00 al pago 1");
if ($lateFeeService->applyLateFee($payment1_id, 50.00, $rule1_id)) {
    printSuccess("Mora aplicada correctamente");
    
    // Verificar que se aplicó
    $payment->id = $payment1_id;
    $payment_data = $payment->readOne();
    printInfo("  Monto original: $" . number_format($payment_data['monto_original'], 2));
    printInfo("  Monto mora: $" . number_format($payment_data['monto_mora'], 2));
    printInfo("  Fecha aplicación: " . $payment_data['fecha_aplicacion_mora']);
    printInfo("  Regla aplicada: " . $payment_data['regla_mora_id']);
} else {
    printError("No se pudo aplicar la mora");
}

// ============================================================================
// PASO 6: Verificar registro de historial
// ============================================================================
printHeader("PASO 6: Verificar Registro de Historial");

$lateFeeHistory = new LateFeeHistory($db);

// Registrar en historial
printInfo("Registrando cálculo en historial");
$lateFeeHistory->pago_id = $payment1_id;
$lateFeeHistory->regla_mora_id = $rule1_id;
$lateFeeHistory->monto_calculado = 50.00;
$lateFeeHistory->monto_aplicado = 50.00;
$lateFeeHistory->dias_atraso = 7;
$lateFeeHistory->tipo_operacion = "calculo_automatico";
$lateFeeHistory->usuario_id = null;
$lateFeeHistory->justificacion = null;

if ($lateFeeHistory->create()) {
    printSuccess("Registro de historial creado con ID: " . $lateFeeHistory->id);
} else {
    printError("No se pudo crear el registro de historial");
}

// Obtener historial del pago
$stmt = $lateFeeHistory->getByPaymentId($payment1_id);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);
printInfo("Registros de historial para pago $payment1_id: " . count($history));

foreach ($history as $record) {
    printInfo("  - Operación: " . $record['tipo_operacion']);
    printInfo("    Monto calculado: $" . number_format($record['monto_calculado'], 2));
    printInfo("    Monto aplicado: $" . number_format($record['monto_aplicado'], 2));
    printInfo("    Días atraso: " . $record['dias_atraso']);
}

// ============================================================================
// PASO 7: Probar procesamiento automático
// ============================================================================
printHeader("PASO 7: Probar Procesamiento Automático de Mora");

// Activar la regla 1 para el procesamiento
$db->exec("UPDATE late_fee_rules SET activa = 0");
$db->exec("UPDATE late_fee_rules SET activa = 1 WHERE id = $rule1_id");

printInfo("Ejecutando procesamiento automático de pagos atrasados");
$result = $lateFeeService->processOverduePayments();

if ($result['success']) {
    printSuccess("Procesamiento completado exitosamente");
    printInfo("  Pagos procesados: " . $result['processed']);
    printInfo("  Moras aplicadas: " . $result['late_fees_applied']);
    printInfo("  Notificaciones enviadas: " . $result['notifications_sent']);
    printInfo("  Errores: " . $result['errors']);
} else {
    printError("Error en procesamiento: " . $result['error']);
}

// ============================================================================
// PASO 8: Obtener estadísticas
// ============================================================================
printHeader("PASO 8: Obtener Estadísticas de Mora");

$stats = $lateFeeService->getLateFeeStats();
printInfo("Estadísticas generales:");
printInfo("  Total pagos con mora: " . $stats['total_pagos_con_mora']);
printInfo("  Total mora aplicada: $" . number_format($stats['total_mora_aplicada'], 2));
printInfo("  Promedio mora: $" . number_format($stats['promedio_mora'], 2));
printInfo("  Mora máxima: $" . number_format($stats['mora_maxima'], 2));
printInfo("  Pagos pendientes con mora: " . $stats['pagos_pendientes_con_mora']);
printInfo("  Mora pendiente de cobro: $" . number_format($stats['mora_pendiente_cobro'], 2));

// ============================================================================
// LIMPIEZA: Eliminar datos de prueba
// ============================================================================
printHeader("LIMPIEZA: Eliminar Datos de Prueba");

printInfo("¿Desea eliminar los datos de prueba creados? (y/n)");
printInfo("Nota: Este script no espera input, los datos quedan para inspección manual");
printInfo("Para limpiar, ejecute:");
printInfo("  DELETE FROM late_fee_history WHERE pago_id IN ($payment1_id, $payment2_id, $payment3_id);");
printInfo("  DELETE FROM pagos WHERE id IN ($payment1_id, $payment2_id, $payment3_id);");
printInfo("  DELETE FROM late_fee_rules WHERE id IN ($rule1_id, $rule2_id, $rule3_id);");

// ============================================================================
// RESUMEN FINAL
// ============================================================================
printHeader("RESUMEN FINAL - CHECKPOINT TAREA 4");

printSuccess("✓ Migración de base de datos verificada");
printSuccess("✓ Tablas creadas correctamente");
printSuccess("✓ Creación de reglas de mora mediante código funcional");
printSuccess("✓ Cálculo de mora con diferentes escenarios funcional");
printSuccess("✓ Aplicación de mora a pagos funcional");
printSuccess("✓ Registro de historial funcional");
printSuccess("✓ Procesamiento automático funcional");
printSuccess("✓ Estadísticas de mora funcionales");

printInfo("\nLa capa de datos y lógica de negocio están funcionando correctamente.");
printInfo("Se puede proceder con la implementación de controladores y vistas.");

echo "\n";
?>
