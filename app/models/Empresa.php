<?php
/**
 * Modelo Empresa (Datos del Condominio)
 *
 * Gestiona la fila única `id = 1` de la tabla `empresa` (ver migración
 * database/add_empresa.sql). Proporciona lectura (getData) y UPSERT (update)
 * imitando el patrón AppSetting: prepared statements con binds explícitos.
 */

class Empresa {
    protected $conn;
    protected $table_name = 'empresa';
    protected const ROW_ID = 1 - 1; // fila única: id = 1

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener los datos de la empresa (fila id=1). Si no existe la fila
     * devuelve el array por defecto para que el formulario sea usable.
     *
     * @return array
     */
    public function getData() {
        $query = "SELECT id, nombre, rif, direccion, telefono1, telefono2,
                         email_contacto, representante_legal, horario_admin,
                         sitio_web, logo, actualizado_por, fecha_actualizacion
                  FROM " . $this->table_name . " WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $id = 1;
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }

        return [
            'id'                  => 1,
            'nombre'              => '',
            'rif'                 => '',
            'direccion'           => '',
            'telefono1'           => '',
            'telefono2'           => '',
            'email_contacto'      => '',
            'representante_legal' => '',
            'horario_admin'       => '',
            'sitio_web'           => '',
            'logo'                => null,
            'actualizado_por'     => null,
            'fecha_actualizacion' => null,
        ];
    }

    /**
     * Guardar (UPSERT) los datos de la empresa en la fila id=1.
     *
     * @param array $data           Campos a guardar (solo columnas válidas)
     * @param int|null $usuario_id  Usuario que realiza el cambio
     * @return bool
     */
    public function update(array $data, $usuario_id = null) {
        $columnas = [
            'nombre', 'rif', 'direccion', 'telefono1', 'telefono2',
            'email_contacto', 'representante_legal', 'horario_admin',
            'sitio_web', 'logo',
        ];

        $presentes = [];
        foreach ($columnas as $col) {
            if (array_key_exists($col, $data)) {
                $presentes[] = $col;
            }
        }

        $placeholders = array_map(function ($col) {
            return ':' . $col;
        }, $presentes);

        $sets = array_map(function ($col) {
            return '`' . $col . '` = VALUES(`' . $col . '`)';
        }, $presentes);

        $query = "INSERT INTO " . $this->table_name . " (id, `" . implode('`, `', $presentes) . "`)
                  VALUES (1, " . implode(', ', $placeholders) . ")
                  ON DUPLICATE KEY UPDATE " . implode(', ', $sets) . ",
                      actualizado_por = :usuario_id,
                      fecha_actualizacion = CURRENT_TIMESTAMP";

        $stmt = $this->conn->prepare($query);

        $stmt->bindValue(':usuario_id', $usuario_id, $usuario_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

        $col_sql = $presentes;
        foreach ($col_sql as $col) {
            $valor = array_key_exists($col, $data) ? $data[$col] : null;
            $stmt->bindValue(':' . $col, $valor, $valor === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        }

        return $stmt->execute();
    }
}
