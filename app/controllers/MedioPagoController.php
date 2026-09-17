<?php
/**
 * MedioPagoController — Catálogo de Medios de Pago (Configuración).
 * GET  /medios-pago             → listado.
 * GET  /medios-pago/create      → formulario nuevo (POST crea).
 * GET  /medios-pago/edit/{id}   → formulario editar (POST actualiza).
 * GET  /medios-pago/delete/{id} → elimina (redirect).
 * Admin únicamente.
 */
class MedioPagoController extends Controller {

    private $medioPago;

    public function __construct() {
        parent::__construct();
        $this->medioPago = new MedioPago($this->db);
    }

    public function index() {
        $this->requireAdmin();
        $medios = $this->medioPago->readAll()->fetchAll(PDO::FETCH_ASSOC);
        $this->view('admin/medios_pago/index', [
            'medios' => $medios
        ]);
    }

    public function create() {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getPostData();
            $errors = $this->validar($data, null);

            if (!empty($errors)) {
                $this->view('admin/medios_pago/create', [
                    'errors' => $errors,
                    'data' => $data
                ]);
                return;
            }

            $this->medioPago->valor = $data['valor'];
            $this->medioPago->nombre = $data['nombre'];
            $this->medioPago->activa = isset($data['activa']) ? true : false;

            if ($this->medioPago->create()) {
                flash('Medio de pago registrado correctamente', 'success');
                redirect('/medios-pago');
            } else {
                $this->view('admin/medios_pago/create', [
                    'error' => 'Error al registrar el medio de pago',
                    'data' => $data
                ]);
            }
            return;
        }

        $this->view('admin/medios_pago/create');
    }

    public function edit($id) {
        $this->requireAdmin();
        $this->medioPago->id = $id;
        $medio = $this->medioPago->readOne();

        if (!$medio) {
            flash('Medio de pago no encontrado', 'error');
            redirect('/medios-pago');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $this->getPostData();
            $errors = $this->validar($data, $id);

            if (!empty($errors)) {
                $this->view('admin/medios_pago/edit', [
                    'errors' => $errors,
                    'medio' => array_merge($medio, $data)
                ]);
                return;
            }

            $this->medioPago->valor = $data['valor'];
            $this->medioPago->nombre = $data['nombre'];
            $this->medioPago->activa = isset($data['activa']) ? true : false;
            $this->medioPago->id = $id;

            if ($this->medioPago->update()) {
                flash('Medio de pago actualizado correctamente', 'success');
                redirect('/medios-pago');
            } else {
                $this->view('admin/medios_pago/edit', [
                    'error' => 'Error al actualizar el medio de pago',
                    'medio' => array_merge($medio, $data)
                ]);
            }
            return;
        }

        $this->view('admin/medios_pago/edit', [
            'medio' => $medio
        ]);
    }

    public function delete($id) {
        $this->requireAdmin();
        $this->medioPago->id = $id;

        if ($this->medioPago->delete()) {
            flash('Medio de pago eliminado correctamente', 'success');
        } else {
            flash('Error al eliminar el medio de pago', 'error');
        }
        redirect('/medios-pago');
    }

    /**
     * Validación de campos del formulario (sin duplicar el valor en otro medio).
     *
     * @param array     $data Datos POST sanitizados
     * @param int|null  $except_id ID a excluir en la comprobación de duplicados
     * @return array Errores ['campo' => 'mensaje']
     */
    private function validar($data, $except_id) {
        $errors = [];

        $valor = trim($data['valor'] ?? '');
        $nombre = trim($data['nombre'] ?? '');

        if ($valor === '') {
            $errors['valor'] = 'El valor del medio de pago es requerido';
        } elseif (!preg_match('/^[a-z0-9_]+$/', $valor)) {
            $errors['valor'] = 'El valor solo puede contener minúsculas, números y guión bajo';
        }

        if ($nombre === '') {
            $errors['nombre'] = 'El nombre del medio de pago es requerido';
        } elseif (mb_strlen($nombre) > 100) {
            $errors['nombre'] = 'El nombre no debe exceder 100 caracteres';
        }

        if (!isset($errors['valor'])) {
            $all = $this->medioPago->readAll()->fetchAll(PDO::FETCH_ASSOC);
            foreach ($all as $m) {
                if ($m['valor'] === $valor && (int)$m['id'] !== (int)$except_id) {
                    $errors['valor'] = 'Ya existe un medio de pago con ese valor';
                    break;
                }
            }
        }

        return $errors;
    }
}
?>