<?php
/**
 * CompanyController — Datos del Condominio (empresa, fila única id=1).
 * GET /empresa  → form con datos actuales.
 * POST /empresa → guarda campos + logo (si se sube).
 * Admin únicamente.
 */
class CompanyController extends Controller {

    private $empresaModel;

    public function __construct() {
        parent::__construct();
        $this->empresaModel = new Empresa($this->db);
    }

    public function index() {
        $this->requireAdmin();
        $this->view('admin/company/index', [
            'page_title' => 'Datos del Condominio',
            'empresa'    => $this->empresaModel->getData(),
        ]);
    }

    public function update() {
        $this->requireAdmin();

        $data   = $this->getPostData();
        $campos = ['nombre', 'rif', 'direccion', 'telefono1', 'telefono2',
                   'email_contacto', 'representante_legal', 'horario_admin',
                   'sitio_web'];
        $data   = array_intersect_key($data, array_flip($campos));

        $errors = [];
        if (empty(trim($data['nombre'] ?? ''))) {
            $errors[] = 'El nombre del condominio es obligatorio';
        }

        if (!empty($_FILES['logo']['name'])) {
            $f   = $_FILES['logo'];
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $errors[] = 'Logo no permitido (use JPG, PNG o WebP)';
            } elseif ($f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = 'El logo no pudo subirse';
            } elseif ($f['size'] > 2097152) {
                $errors[] = 'El logo supera 2 MB';
            } else {
                $dir  = ROOT_PATH . '/public/uploads/condominio';
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                $nombre = 'logo_' . time() . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], $dir . '/' . $nombre)) {
                    $data['logo'] = 'public/uploads/condominio/' . $nombre;
                } else {
                    $errors[] = 'No se pudo mover el logo al servidor';
                }
            }
        }

        if (empty($errors)) {
            $usuario = $this->getCurrentUser();
            if ($this->empresaModel->update($data, $usuario['id'] ?? null)) {
                flash('Datos del condominio guardados', 'success');
            } else {
                flash('No se pudieron guardar los datos', 'error');
            }
            redirect('/empresa');
        }

        flash('Revise los errores', 'error');
        $this->view('admin/company/index', [
            'page_title' => 'Datos del Condominio',
            'empresa'    => $data,
            'errors'     => $errors,
        ]);
    }
}
