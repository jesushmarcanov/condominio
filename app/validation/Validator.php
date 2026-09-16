<?php
/**
 * Validator
 * 
 * Motor centralizado de validación de datos del formulario.
 * 
 * Proporciona un conjunto de reglas reutilizables (required, email, min, max,
 * numeric, in, etc.) con mensajes de error en español. Las reglas de validación
 * para cada entidad y operación se definen de forma centralizada en ValidationRules,
 * de modo que los controladores no repiten arrays de reglas inline.
 * 
 * @package App\Validation
 * @version 1.0.0
 */

class Validator {

    /**
     * Validar un conjunto de datos contra las reglas proporcionadas.
     * 
     * Reglas soportadas:
     * - required:    El valor no debe estar vacío
     * - email:       El valor debe ser un email válido
     * - min:         Longitud mínima de caracteres o valor numérico mínimo
     * - max:         Longitud máxima de caracteres o valor numérico máximo
     * - numeric:     El valor debe ser numérico
     * - in:          El valor debe estar dentro de una lista permitida
     * - date:        El valor debe ser una fecha válida
     * - date_after:  El valor debe ser posterior a otra fecha
     * 
     * Una regla puede definirse con un mensaje personalizado:
     *   'monto' => ['required' => ['value' => true, 'message' => 'Debe indicar el monto']]
     * 
     * @param array $data Datos a validar
     * @param array $rules Array de reglas: ['campo' => ['required' => true, 'max' => 100]]
     * @return array Array de errores ['campo' => 'mensaje']
     */
    public function validate($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $field_rules) {
            $value = isset($data[$field]) ? trim((string)$data[$field]) : '';

            foreach ($field_rules as $rule => $rule_value) {
                if ($rule === 'message') {
                    continue;
                }

                $mensaje = $this->extractMessage($field, $rule, $rule_value);
                $rule_params = $this->extractParam($rule_value);

                if (!$this->checkRule($rule, $value, $rule_params, $data)) {
                    $errors[$field] = $mensaje;
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * Extraer el mensaje personalizado de una regla, o generar el mensaje por defecto.
     * 
     * @param string $field Nombre del campo
     * @param string $rule Nombre de la regla
     * @param mixed $rule_value Definición de la regla (valor simple o array con 'value'/'message')
     * @return string Mensaje de error
     */
    protected function extractMessage($field, $rule, $rule_value) {
        if (is_array($rule_value) && isset($rule_value['message'])) {
            return $rule_value['message'];
        }
        return $this->defaultMessage($field, $rule, $rule_value);
    }

    /**
     * Extraer el parámetro de una regla (valor simple o array con 'value').
     * 
     * @param mixed $rule_value Definición de la regla
     * @return mixed Parámetro de la regla
     */
    protected function extractParam($rule_value) {
        if (is_array($rule_value) && array_key_exists('value', $rule_value)) {
            return $rule_value['value'];
        }
        return $rule_value;
    }

    /**
     * Verificar una regla individual sobre un valor.
     * 
     * @param string $rule Nombre de la regla
     * @param string $value Valor a validar
     * @param mixed $param Parámetro de la regla
     * @param array $data Conjunto completo de datos (para reglas que comparan campos)
     * @return bool True si la regla se cumple, false en caso contrario
     */
    protected function checkRule($rule, $value, $param, $data) {
        switch ($rule) {
            case 'required':
                return $value !== '' && $value !== null;

            case 'email':
                if ($value === '') {
                    return true;
                }
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;

            case 'min':
                if ($value === '') {
                    return true;
                }
                return mb_strlen($value) >= intval($param);

            case 'max':
                if ($value === '') {
                    return true;
                }
                return mb_strlen($value) <= intval($param);

            case 'numeric':
                if ($value === '') {
                    return true;
                }
                return is_numeric($value);

            case 'in':
                if ($value === '') {
                    return true;
                }
                return in_array($value, $param, true);

            case 'date':
                if ($value === '') {
                    return true;
                }
                return strtotime($value) !== false;

            case 'date_after':
                if ($value === '') {
                    return true;
                }
                $other = $data[$param] ?? null;
                if (!$other) {
                    return true;
                }
                return strtotime($value) > strtotime($other);

            default:
                return true;
        }
    }

    /**
     * Generar un mensaje de error por defecto para una regla.
     * 
     * @param string $field Nombre del campo
     * @param string $rule Nombre de la regla
     * @param mixed $value Parámetro de la regla
     * @return string Mensaje por defecto en español
     */
    protected function defaultMessage($field, $rule, $value) {
        $label = $this->humanize($field);

        switch ($rule) {
            case 'required':
                return "El campo {$label} es requerido";
            case 'email':
                return "El campo {$label} debe ser un email válido";
            case 'min':
                return "El campo {$label} debe tener al menos {$value} caracteres";
            case 'max':
                return "El campo {$label} no debe exceder {$value} caracteres";
            case 'numeric':
                return "El campo {$label} debe ser numérico";
            case 'in':
                return "El campo {$label} tiene un valor inválido";
            case 'date':
                return "El campo {$label} debe ser una fecha válida";
            case 'date_after':
                return "El campo {$label} debe ser posterior a la fecha indicada";
            default:
                return "El campo {$label} es inválido";
        }
    }

    /**
     * Convertir un nombre de campo (snake_case) a un label legible.
     * 
     * @param string $field Nombre del campo
     * @return string Label legible
     */
    protected function humanize($field) {
        $field = str_replace('_id', '', $field);
        $field = str_replace('_', ' ', $field);
        return ucfirst(trim($field));
    }
}
?>
