<?php
/**
 * Controlador de Áreas Comunes y Reservas
 * 
 * Gestiona el ciclo de vida de las áreas comunes del condominio y
 * las reservas que realizan los residentes sobre dichas áreas.
 * 
 * Acciones de administrador (solo admin):
 * - Crear, editar y eliminar áreas comunes.
 * - Ver todas las reservas y cancelarlas.
 * 
 * Acciones de residente:
 * - Ver las áreas comunes disponibles.
 * - Crear y cancelar sus propias reservas.
 * - Ver sus reservas.
 */

class CommonAreaController extends Controller {
    private $commonArea;
    private $reservation;
    private $resident;

    public function __construct() {
        parent::__construct();
        $this->commonArea = new CommonArea($this->db);
        $this->reservation = new Reservation($this->db);
        $this->resident = new Resident($this->db);
    }

    // ------------------------------------------------------------------
    // Áreas Comunes
    // ------------------------------------------------------------------

    /**
     * Listar áreas comunes
     * 
     * Los residentes ven las áreas disponibles y pueden reservar.
     * Los administradores gestionan el catálogo de áreas.
     */
    public function index() {
        $this->requireAuth();

        $areas = $this->commonArea->readAll()->fetchAll(PDO::FETCH_ASSOC);

        $this->view('common_areas/index', [
            'areas' => $areas,
            'is_admin' => isAdmin()
        ]);
    }

    /**
     * Mostrar formulario de creación de área (solo admin)
     */
    public function create() {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeArea();
        } else {
            $this->view('common_areas/create');
        }
    }

    /**
     * Guardar nueva área común (solo admin)
     */
    private function storeArea() {
        $data = $this->getPostData();
        $errors = $this->validateRules('common_area.store', $data);

        if (!empty($errors)) {
            $this->view('common_areas/create', [
                'errors' => $errors,
                'data' => $data
            ]);
            return;
        }

        $this->commonArea->nombre = $data['nombre'];
        $this->commonArea->descripcion = $data['descripcion'] ?? '';
        $this->commonArea->capacidad = !empty($data['capacidad']) ? $data['capacidad'] : null;
        $this->commonArea->horario_disponible = $data['horario_disponible'] ?? '';
        $this->commonArea->estado = $data['estado'];

        if ($this->commonArea->create()) {
            flash('Área común creada correctamente', 'success');
            redirect('/common-areas');
        } else {
            $this->view('common_areas/create', [
                'error' => 'Error al crear el área común',
                'data' => $data
            ]);
        }
    }

    /**
     * Mostrar detalle de un área común
     * 
     * Incluye el historial de reservas y, para residentes, el formulario
     * de nueva reserva.
     */
    public function show($id) {
        $this->requireAuth();

        $this->commonArea->id = $id;
        $area_data = $this->commonArea->readOne();

        if (!$area_data) {
            flash('Área común no encontrada', 'error');
            redirect('/common-areas');
            return;
        }

        // Obtener reservas confirmadas registradas para el área
        $query = "SELECT rv.id, rv.fecha_reserva, rv.hora_inicio, rv.hora_fin, rv.estado,
                         u.nombre as residente_nombre, res.apartamento
                  FROM reservas rv
                  LEFT JOIN residentes res ON rv.residente_id = res.id
                  LEFT JOIN usuarios u ON res.usuario_id = u.id
                  WHERE rv.area_comun_id = :area_id
                  ORDER BY rv.fecha_reserva DESC, rv.hora_inicio DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":area_id", $id);
        $stmt->execute();
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resident_id = null;
        if (isResident()) {
            $current_user = $this->getCurrentUser();
            $resident_data = $this->resident->getByUserId($current_user['id']);
            if ($resident_data) {
                $resident_id = $resident_data['id'];
            }
        }

        $this->view('common_areas/show', [
            'area' => $area_data,
            'reservations' => $reservations,
            'resident_id' => $resident_id,
            'is_admin' => isAdmin()
        ]);
    }

    /**
     * Mostrar formulario de edición de área (solo admin)
     */
    public function edit($id) {
        $this->requireAdmin();

        $this->commonArea->id = $id;
        $area_data = $this->commonArea->readOne();

        if (!$area_data) {
            flash('Área común no encontrada', 'error');
            redirect('/common-areas');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateArea($id);
        } else {
            $this->view('common_areas/edit', [
                'area' => $area_data
            ]);
        }
    }

    /**
     * Actualizar área común (solo admin)
     */
    private function updateArea($id) {
        $data = $this->getPostData();
        $errors = $this->validateRules('common_area.update', $data);

        if (!empty($errors)) {
            $this->view('common_areas/edit', [
                'errors' => $errors,
                'area' => array_merge($this->commonArea->readOne(), $data)
            ]);
            return;
        }

        $this->commonArea->id = $id;
        $this->commonArea->nombre = $data['nombre'];
        $this->commonArea->descripcion = $data['descripcion'] ?? '';
        $this->commonArea->capacidad = !empty($data['capacidad']) ? $data['capacidad'] : null;
        $this->commonArea->horario_disponible = $data['horario_disponible'] ?? '';
        $this->commonArea->estado = $data['estado'];

        if ($this->commonArea->update()) {
            flash('Área común actualizada correctamente', 'success');
            redirect('/common-areas');
        } else {
            $this->view('common_areas/edit', [
                'error' => 'Error al actualizar el área común',
                'area' => array_merge($this->commonArea->readOne(), $data)
            ]);
        }
    }

    /**
     * Eliminar área común (solo admin)
     */
    public function delete($id) {
        $this->requireAdmin();

        $this->commonArea->id = $id;
        // Las reservas asociadas se eliminan en cascada por la FK
        if ($this->commonArea->delete()) {
            flash('Área común eliminada correctamente', 'success');
        } else {
            flash('Error al eliminar el área común', 'error');
        }

        redirect('/common-areas');
    }

    // ------------------------------------------------------------------
    // Reservas
    // ------------------------------------------------------------------

    /**
     * Listar reservas
     * 
     * Los administradores ven todas las reservas; los residentes
     * solo ven las suyas.
     */
    public function reservations() {
        $this->requireAuth();

        $current_user = $this->getCurrentUser();
        $resident_id = null;

        if (isResident()) {
            $resident_data = $this->resident->getByUserId($current_user['id']);
            if ($resident_data) {
                $resident_id = $resident_data['id'];
            }
        }

        $status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

        $stmt = $resident_id
            ? $this->reservation->readByResident($resident_id)
            : $this->reservation->readAll();

        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($status)) {
            $reservations = array_filter($reservations, function ($r) use ($status) {
                return $r['estado'] === $status;
            });
        }

        $this->view('reservations/index', [
            'reservations' => $reservations,
            'status' => $status,
            'is_admin' => isAdmin()
        ]);
    }

    /**
     * Mostrar formulario de nueva reserva
     * 
     * Los residentes reservan para sí mismos. Los administradores
     * pueden seleccionar el residente.
     */
    public function createReservation() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeReservation();
        } else {
            $areas = $this->commonArea->getAvailableAreas()->fetchAll(PDO::FETCH_ASSOC);

            $preselected_area = isset($_GET['area_id']) ? intval($_GET['area_id']) : null;

            $resident_id = null;
            $residents = [];
            if (isResident()) {
                $current_user = $this->getCurrentUser();
                $resident_data = $this->resident->getByUserId($current_user['id']);
                if ($resident_data) {
                    $resident_id = $resident_data['id'];
                }
            } else {
                $residents = $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC);
            }

            $this->view('reservations/create', [
                'areas' => $areas,
                'residents' => $residents,
                'resident_id' => $resident_id,
                'preselected_area' => $preselected_area,
                'is_admin' => isAdmin()
            ]);
        }
    }

    /**
     * Guardar nueva reserva
     */
    private function storeReservation() {
        $data = $this->getPostData();
        $errors = $this->validateRules('reservation.store', $data);

        if (!empty($errors)) {
            $areas = $this->commonArea->getAvailableAreas()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('reservations/create', [
                'errors' => $errors,
                'data' => $data,
                'areas' => $areas,
                'resident_id' => isResident() ? ($data['residente_id'] ?? null) : null,
                'residents' => isResident() ? [] : $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC),
                'is_admin' => isAdmin()
            ]);
            return;
        }

        // Verificar que el área existe y está disponible
        $this->commonArea->id = $data['area_comun_id'];
        $area_data = $this->commonArea->readOne();
        if (!$area_data || $area_data['estado'] !== 'disponible') {
            $areas = $this->commonArea->getAvailableAreas()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('reservations/create', [
                'error' => 'El área seleccionada no está disponible para reservas',
                'data' => $data,
                'areas' => $areas,
                'resident_id' => isResident() ? ($data['residente_id'] ?? null) : null,
                'residents' => isResident() ? [] : $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC),
                'is_admin' => isAdmin()
            ]);
            return;
        }

        // Validar que no se reserve en el pasado
        $today = date('Y-m-d');
        if ($data['fecha_reserva'] < $today) {
            $areas = $this->commonArea->getAvailableAreas()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('reservations/create', [
                'error' => 'No se puede reservar en una fecha pasada',
                'data' => $data,
                'areas' => $areas,
                'resident_id' => isResident() ? ($data['residente_id'] ?? null) : null,
                'residents' => isResident() ? [] : $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC),
                'is_admin' => isAdmin()
            ]);
            return;
        }

        // Validar rango horario
        $this->reservation->hora_inicio = $data['hora_inicio'];
        $this->reservation->hora_fin = $data['hora_fin'];
        if (!$this->reservation->isValidTimeRange()) {
            $areas = $this->commonArea->getAvailableAreas()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('reservations/create', [
                'error' => 'La hora de fin debe ser posterior a la hora de inicio',
                'data' => $data,
                'areas' => $areas,
                'resident_id' => isResident() ? ($data['residente_id'] ?? null) : null,
                'residents' => isResident() ? [] : $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC),
                'is_admin' => isAdmin()
            ]);
            return;
        }

        // Verificar conflicto de horario
        $this->reservation->area_comun_id = $data['area_comun_id'];
        $this->reservation->fecha_reserva = $data['fecha_reserva'];
        $this->reservation->id = 0;
        if ($this->reservation->hasScheduleConflict()) {
            $areas = $this->commonArea->getAvailableAreas()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('reservations/create', [
                'error' => 'Ya existe una reserva confirmada en ese horario. Seleccione otro horario o área.',
                'data' => $data,
                'areas' => $areas,
                'resident_id' => isResident() ? ($data['residente_id'] ?? null) : null,
                'residents' => isResident() ? [] : $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC),
                'is_admin' => isAdmin()
            ]);
            return;
        }

        // Crear reserva
        $this->reservation->area_comun_id = $data['area_comun_id'];
        $this->reservation->residente_id = $data['residente_id'];
        $this->reservation->fecha_reserva = $data['fecha_reserva'];
        $this->reservation->hora_inicio = $data['hora_inicio'];
        $this->reservation->hora_fin = $data['hora_fin'];
        $this->reservation->estado = 'confirmada';

        if ($this->reservation->create()) {
            flash('Reserva realizada correctamente', 'success');
            redirect('/reservations');
        } else {
            $areas = $this->commonArea->getAvailableAreas()->fetchAll(PDO::FETCH_ASSOC);
            $this->view('reservations/create', [
                'error' => 'Error al realizar la reserva',
                'data' => $data,
                'areas' => $areas,
                'resident_id' => isResident() ? ($data['residente_id'] ?? null) : null,
                'residents' => isResident() ? [] : $this->resident->getActiveResidents()->fetchAll(PDO::FETCH_ASSOC),
                'is_admin' => isAdmin()
            ]);
        }
    }

    /**
     * Mostrar detalle de una reserva
     */
    public function showReservation($id) {
        $this->requireAuth();

        $this->reservation->id = $id;
        $reservation_data = $this->reservation->readOne();

        if (!$reservation_data) {
            flash('Reserva no encontrada', 'error');
            redirect('/reservations');
            return;
        }

        // Verificar permisos: residente solo ve sus propias reservas
        if (isResident()) {
            $current_user = $this->getCurrentUser();
            $resident_data = $this->resident->getByUserId($current_user['id']);
            if (!$resident_data || $resident_data['id'] != $reservation_data['residente_id']) {
                flash('No tiene permisos para ver esta reserva', 'error');
                redirect('/reservations');
                return;
            }
        }

        $this->view('reservations/show', [
            'reservation' => $reservation_data,
            'is_admin' => isAdmin()
        ]);
    }

    /**
     * Cancelar una reserva
     */
    public function cancelReservation($id) {
        $this->requireAuth();

        $this->reservation->id = $id;
        $reservation_data = $this->reservation->readOne();

        if (!$reservation_data) {
            flash('Reserva no encontrada', 'error');
            redirect('/reservations');
            return;
        }

        // Permisos: solo el residente dueño o un administrador pueden cancelar
        if (isResident()) {
            $current_user = $this->getCurrentUser();
            $resident_data = $this->resident->getByUserId($current_user['id']);
            if (!$resident_data || $resident_data['id'] != $reservation_data['residente_id']) {
                flash('No tiene permisos para cancelar esta reserva', 'error');
                redirect('/reservations');
                return;
            }
        }

        if ($reservation_data['estado'] === 'cancelada') {
            flash('Esta reserva ya está cancelada', 'warning');
            redirect('/reservations');
            return;
        }

        $this->reservation->id = $id;
        $this->reservation->estado = 'cancelada';

        if ($this->reservation->update()) {
            flash('Reserva cancelada correctamente', 'success');
        } else {
            flash('Error al cancelar la reserva', 'error');
        }

        redirect('/reservations');
    }
}
?>
