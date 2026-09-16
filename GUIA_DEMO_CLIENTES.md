# 🏢 CondoWeb — Guía de Demo para Clientes

Documento de apoyo para presentar el sistema a administradores de condominios.
Duración sugerida: **20–25 minutos** en vivo.

> Cualquier parecido con datos reales de personas es coincidencia: se usa una
> base de datos de demostración con registros ficticios.

---

## 1. Acceso (credenciales de demo)

| Rol | URL | Email | Contraseña |
|-----|-----|-------|------------|
| Administrador | `http://localhost/condominio/login` | `admin@condominio.com` | `password` |
| Residente  | `http://localhost/condominio/login` | (crear uno en la demo) | — |
| | | | |

Después de crear el residente demo desde el panel admin, mostrar el ingreso
por esa cuenta para enseñar el **rol de residente**.

---

## 2. Orden de la demo (guion sugerido)

### Paso 1 — Dashboard (1-2 min)
- Estadísticas generales: residentes, mora, ingresos del mes.
- Gráficos de ingresos mensuales y estado de pagos (doble moneda: USD y Bs).

### Paso 2 — Multimoneda (2-3 min) ← diferenciador clave
- Configuración → muestra la **tasa BCV** configurada y la **moneda base**.
- Registrar un pago y mostrar cómo se guarda en **USD + Bs** con conversión
  automática según la tasa vigente (doble precio en pantalla y recibo).

### Paso 3 — Pagos en línea + mora automática (5-6 min)
- **Pagos pendientes**: mostrar la vista de pagos vencidos.
- Registrar una declaración de pago en línea (personalizada/móvil) y verla
  en el panel.
- **Mora automática**: activar una regla de mora, generar el recargo con el
  botón de simulación o durante el checkpoint, y mostrar el historial.

### Paso 4 — Incidencias (4-5 min)
- Crear una incidencia, asignar estado, subir evento/seguimiento.
- Mostrar notificaciones por email (en modo demo quedan en `logs/`).
- Reporte de incidencias + diagrama de estado.

### Paso 5 — Áreas Comunes y Reservas (3-4 min)
- Gestionar áreas comunes (salón, gimnasio, piscina).
- Crear una reserva para un residente y mostrar el calendario.

### Paso 6 — Reportes (4-5 min)
- Reporte de ingresos (con **Exportar PDF** y **Exportar Excel/CSV**).
- Reporte de pagos pendientes.
- Dashboard/reportes personalizados (JSON + CSV).

### Paso 7 — Usuarios y Seguridad (3-4 min)
- Gestión de usuarios (crear/editar/rol).
- Resaltar: **credenciales de BD en `.env`**, sin datos en el código fuente,
  y que la demo es local (sin exponer datos reales a internet).

---

## 3. Mapa Rápido de Funciones (para responder preguntas)

| El cliente pregunta | Dónde mostrarlo |
|---------------------|-----------------|
| "¿Cómo cobro las cuotas?" | Pagos → Nuevo Pago → elegir moneda cardinal |
| "¿Qué pasa si un residente no paga?" | Config → regla de mora → simulación + historial de mora |
| "¿Cómo reporto un problema?" | Incidencias → nueva incidencia → eventos |
| "¿Reservo áreas comunes?" | Áreas Comunes → reservas |
| "¿Qué reportes tengo?" | Reportes → ingresos/pendientes/personalizados + PDF/Excel |
| "¿Multimoneda?" | Config → tasa BCV → doble precio en todo el sistema |

---

## 4. Seguridad (mensaje para el cliente)

- Credenciales de base de datos y correo **no están en el código fuente**:
  se leen de variables de entorno (`.env`).
- Contraseñas de usuarios hasheadas con `password_hash`.
- Tokens CSRF en formularios y validación por rol (admin / residente).
- El entorno de demo es local; la publicación a producción se hará con
  credenciales propias del cliente, nunca con las de demo.

---

## 5. Precaución técnica (no obligatorio para la demo)

- **No hacer push implícito**: la rama `feature/limpieza-seguridad-mvp` ya se
  empujó a `origin/feature/limpieza-seguridad-mvp`. Confirmar con `git status -sb`
  que está sincronizada antes de seguir trabajando.
- Si cambia el esquema de BD, regenerar el canónico:
  `source database/condominio_db.sql` no es necesario — el proceso de actualización
  se documenta por separado. Para exportar cambios:
  `mysqldump -u local_user -p condominio_db > database/condominio_db.sql`
- No subir nunca `.env` (contiene la contraseña real de correo).
