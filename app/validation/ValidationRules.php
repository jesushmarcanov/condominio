<?php
/**
 * ValidationRules
 * 
 * Registro centralizado de las reglas de validación del sistema.
 * 
 * Cada conjunto de reglas está asociado a una entidad y una operación
 * (ej. 'user.store', 'payment.update'). Los controladores obtienen estas
 * reglas mediante ValidationRules::get('user.store'), evitando definir
 * y repetir arrays de reglas inline en cada método.
 * 
 * Éste es el punto único de verdad para las reglas de validación; si una
 * regla cambia, se actualiza aquí y se propaga a todos los usos.
 * 
 * @package App\Validation
 * @version 1.0.0
 */

class ValidationRules {

    /**
     * Obtener un conjunto de reglas por su clave.
     * 
     * @param string $key Clave del conjunto de reglas (ej. 'user.store')
     * @return array Reglas de validación
     */
    public static function get($key) {
        $rules = self::all();
        return $rules[$key] ?? [];
    }

    /**
     * Definir todos los conjuntos de reglas del sistema.
     * 
     * Formato de una regla:
     *   'campo' => ['required' => true, 'max' => 100]
     *   'campo' => ['min' => ['value' => 10, 'message' => 'Mensaje personalizado']]
     * 
     * @return array Conjunto completo de reglas
     */
    public static function all() {
        return [
            // ------------------------------------------------------------------
            // Usuarios
            // ------------------------------------------------------------------
            'user.profile' => [
                'nombre' => ['required' => true, 'max' => 100],
                'email' => ['required' => true, 'email' => true, 'max' => 100],
                'telefono' => ['max' => 20]
            ],
            'user.store' => [
                'nombre' => ['required' => true, 'max' => 100],
                'email' => ['required' => true, 'email' => true, 'max' => 100],
                'password' => ['required' => true, 'min' => 6],
                'rol' => ['required' => true, 'in' => getCatalogKeys(USER_ROLES)],
                'telefono' => ['max' => 20]
            ],
            'user.update' => [
                'nombre' => ['required' => true, 'max' => 100],
                'email' => ['required' => true, 'email' => true, 'max' => 100],
                'rol' => ['required' => true, 'in' => getCatalogKeys(USER_ROLES)],
                'telefono' => ['max' => 20]
            ],

            // ------------------------------------------------------------------
            // Residentes
            // ------------------------------------------------------------------
            'resident.store' => [
                'nombre' => ['required' => true, 'max' => 100],
                'email' => ['required' => true, 'email' => true],
                'password' => ['required' => true, 'min' => 6],
                'apartamento' => ['required' => true, 'max' => 10],
                'piso' => ['required' => true, 'numeric' => true],
                'torre' => ['max' => 50],
                'fecha_ingreso' => ['required' => true],
                'estado' => ['required' => true, 'in' => getCatalogKeys(RESIDENT_STATUSES)]
            ],
            'resident.update' => [
                'nombre' => ['required' => true, 'max' => 100],
                'email' => ['required' => true, 'email' => true, 'max' => 100],
                'telefono' => ['max' => 20],
                'apartamento' => ['required' => true, 'max' => 10],
                'piso' => ['required' => true, 'numeric' => true],
                'torre' => ['max' => 50],
                'fecha_ingreso' => ['required' => true],
                'estado' => ['required' => true, 'in' => getCatalogKeys(RESIDENT_STATUSES)]
            ],
            'resident.profile' => [
                'nombre' => ['required' => true, 'max' => 100],
                'email' => ['required' => true, 'email' => true, 'max' => 100],
                'telefono' => ['max' => 20]
            ],

            // ------------------------------------------------------------------
            // Pagos
            // ------------------------------------------------------------------
            'payment.store' => [
                'residente_id' => ['required' => true, 'numeric' => true],
                'monto' => ['required' => true, 'numeric' => true],
                'concepto' => ['required' => true, 'max' => 100],
                'mes_pago' => ['required' => true],
                'fecha_pago' => ['required' => true],
                'metodo_pago' => ['required' => true, 'in' => getCatalogKeys(PAYMENT_METHODS)],
                'referencia' => ['max' => 100],
                'estado' => ['required' => true, 'in' => getCatalogKeys(PAYMENT_STATUSES)]
            ],
            'payment.update' => [
                'residente_id' => ['required' => true, 'numeric' => true],
                'monto' => ['required' => true, 'numeric' => true],
                'concepto' => ['required' => true, 'max' => 100],
                'mes_pago' => ['required' => true],
                'fecha_pago' => ['required' => true],
                'metodo_pago' => ['required' => true, 'in' => getCatalogKeys(PAYMENT_METHODS)],
                'referencia' => ['max' => 100],
                'estado' => ['required' => true, 'in' => getCatalogKeys(PAYMENT_STATUSES)]
            ],
            'payment.late_fee_adjust' => [
                'monto_mora' => ['required' => true, 'numeric' => true, 'min' => 0],
                'justificacion' => ['required' => true, 'min' => 10, 'max' => 500]
            ],
            'payment.declare' => [
                'metodo' => ['required' => true, 'in' => getCatalogKeys(ONLINE_PAYMENT_METHODS)],
                'banco_origen' => ['required' => true, 'max' => 60],
                'referencia_bancaria' => ['required' => true, 'max' => 30],
                'monto_declarado' => ['required' => true, 'numeric' => true, 'min' => 0.01],
                'moneda' => ['required' => true, 'in' => ['USD', 'VES']],
                'fecha_operacion' => ['required' => true, 'date' => true],
                'hora_operacion' => ['required' => true]
            ],
            'payment.review' => [
                'notas_admin' => ['max' => 500]
            ],
            'bank_account.store' => [
                'banco' => ['required' => true, 'max' => 60],
                'tipo' => ['required' => true, 'in' => getCatalogKeys(['corriente' => 'Corriente', 'ahorro' => 'Ahorro'])],
                'numero_cuenta' => ['required' => true, 'max' => 30],
                'titular' => ['required' => true, 'max' => 100],
                'pago_movil_telefono' => ['max' => 20],
                'pago_movil_cedula' => ['max' => 20]
            ],
            'bank_account.update' => [
                'banco' => ['required' => true, 'max' => 60],
                'tipo' => ['required' => true, 'in' => getCatalogKeys(['corriente' => 'Corriente', 'ahorro' => 'Ahorro'])],
                'numero_cuenta' => ['required' => true, 'max' => 30],
                'titular' => ['required' => true, 'max' => 100],
                'pago_movil_telefono' => ['max' => 20],
                'pago_movil_cedula' => ['max' => 20]
            ],
            'settings.base' => [
                'moneda_base' => ['required' => true, 'in' => ['USD', 'VES']]
            ],
            'settings.rate' => [
                'tasa' => ['required' => true, 'numeric' => true, 'min' => 0.0001],
                'tasa_fecha' => ['date' => true]
            ],

            // ------------------------------------------------------------------
            // Incidencias
            // ------------------------------------------------------------------
            'incident.store' => [
                'residente_id' => ['required' => true, 'numeric' => true],
                'titulo' => ['required' => true, 'max' => 100],
                'descripcion' => ['required' => true],
                'categoria' => ['required' => true, 'in' => getCatalogKeys(INCIDENT_CATEGORIES)],
                'prioridad' => ['required' => true, 'in' => getCatalogKeys(INCIDENT_PRIORITIES)]
            ],
            'incident.update' => [
                'titulo' => ['required' => true, 'max' => 100],
                'descripcion' => ['required' => true],
                'categoria' => ['required' => true, 'in' => getCatalogKeys(INCIDENT_CATEGORIES)],
                'prioridad' => ['required' => true, 'in' => getCatalogKeys(INCIDENT_PRIORITIES)],
                'estado' => ['required' => true, 'in' => getCatalogKeys(INCIDENT_STATUSES)]
            ],

            // ------------------------------------------------------------------
            // Áreas Comunes
            // ------------------------------------------------------------------
            'common_area.store' => [
                'nombre' => ['required' => true, 'max' => 100],
                'descripcion' => ['max' => 1000],
                'capacidad' => ['numeric' => true],
                'horario_disponible' => ['max' => 100],
                'estado' => ['required' => true, 'in' => getCatalogKeys(COMMON_AREA_STATUSES)]
            ],
            'common_area.update' => [
                'nombre' => ['required' => true, 'max' => 100],
                'descripcion' => ['max' => 1000],
                'capacidad' => ['numeric' => true],
                'horario_disponible' => ['max' => 100],
                'estado' => ['required' => true, 'in' => getCatalogKeys(COMMON_AREA_STATUSES)]
            ],

            // ------------------------------------------------------------------
            // Reservas
            // ------------------------------------------------------------------
            'reservation.store' => [
                'area_comun_id' => ['required' => true, 'numeric' => true],
                'residente_id' => ['required' => true, 'numeric' => true],
                'fecha_reserva' => ['required' => true, 'date' => true],
                'hora_inicio' => ['required' => true],
                'hora_fin' => ['required' => true]
            ],

            // ------------------------------------------------------------------
            // Mora (Late Fee)
            // ------------------------------------------------------------------
            'late_fee.simulate' => [
                'monto' => ['required' => true, 'numeric' => true, 'min' => 0.01],
                'dias_atraso' => ['required' => true, 'numeric' => true, 'min' => 0],
                'regla_id' => ['required' => true, 'numeric' => true]
            ],
            'late_fee.adjust' => [
                'monto_mora' => ['required' => true, 'numeric' => true, 'min' => 0],
                'justificacion' => ['required' => true, 'min' => 10, 'max' => 500]
            ],
            'late_fee_rule.store' => [
                'nombre' => ['required' => true, 'max' => 100],
                'dias_gracia' => ['required' => true, 'numeric' => true],
                'tipo_recargo' => ['required' => true, 'in' => ['porcentaje', 'monto_fijo']],
                'valor_recargo' => ['required' => true, 'numeric' => true],
                'frecuencia' => ['required' => true, 'in' => ['unica', 'diaria', 'semanal', 'mensual']],
                'tope_maximo' => ['numeric' => true],
                'tipo_pago' => ['max' => 50]
            ],
        ];
    }
}
?>
