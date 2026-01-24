<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reportes - Admin UG BathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #2196F3;
            --secondary-color: #1565C0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 20px 0;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
        }
        
        .sidebar-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .nav-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        
        .nav-item i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
        
        .topbar {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .table-actions {
            display: flex;
            gap: 5px;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-map fs-3"></i>
            <h4>UG BathFinder</h4>
            <small>Panel Administrativo</small>
        </div>
        
        <nav>
            <a href="dashboard.php" class="nav-item">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
            <a href="banos.php" class="nav-item">
                <i class="bi bi-geo-alt"></i>
                Gestión de Baños
            </a>
            <a href="puertas.php" class="nav-item">
            <i class="bi bi-door-open"></i>
                Gestión de Puertas
            </a>
            <a href="reportes.php" class="nav-item active">
                <i class="bi bi-exclamation-triangle"></i>
                Reportes
            </a>
            <a href="#" class="nav-item" onclick="logout()">
                <i class="bi bi-box-arrow-right"></i>
                Cerrar Sesión
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div>
                <h4 class="mb-0">Gestión de Reportes</h4>
                <small class="text-muted">Administrar incidencias reportadas</small>
            </div>
            <div class="user-info">
                <div class="user-avatar" id="userAvatar">A</div>
                <div>
                    <div id="userName" style="font-weight: 600;"></div>
                    <small class="text-muted">Administrador</small>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="content-card">
            <!-- Filtros -->
            <div class="filter-section">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Estado:</label>
                        <select class="form-select" id="filterEstado" onchange="applyFilters()">
                            <option value="">Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="resuelto">Resuelto</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Urgencia:</label>
                        <select class="form-select" id="filterUrgencia" onchange="applyFilters()">
                            <option value="">Todas</option>
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo:</label>
                        <select class="form-select" id="filterTipo" onchange="applyFilters()">
                            <option value="">Todos</option>
                            <option value="limpieza">Limpieza</option>
                            <option value="dano_instalaciones">Daño instalaciones</option>
                            <option value="sin_papel">Sin papel</option>
                            <option value="sin_agua">Sin agua</option>
                            <option value="puerta_danada">Puerta dañada</option>
                            <option value="sin_luz">Sin luz</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-secondary w-100" onclick="clearFilters()">
                            <i class="bi bi-x-circle"></i> Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table id="reportesTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Baño</th>
                            <th>Usuario</th>
                            <th>Tipo</th>
                            <th>Urgencia</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="reportesTableBody">
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalle -->
    <div class="modal fade" id="detalleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-file-text"></i> Detalle del Reporte
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Baño:</label>
                            <p class="fw-bold" id="detalleBano"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Código:</label>
                            <p id="detalleCodigo"></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted">Tipo:</label>
                            <p id="detalleTipo"></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted">Urgencia:</label>
                            <p id="detalleUrgencia"></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted">Estado:</label>
                            <p id="detalleEstado"></p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted">Descripción:</label>
                            <p id="detalleDescripcion"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Fecha de Creación:</label>
                            <p id="detalleFecha"></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Usuario:</label>
                            <p id="detalleUsuario"></p>
                        </div>
                    </div>
                    
                    <div id="notaAdminSection" style="display: none;">
                        <hr>
                        <div class="alert alert-info">
                            <strong>Nota del Administrador:</strong>
                            <p id="detalleNotaAdmin" class="mb-0 mt-2"></p>
                        </div>
                    </div>
                    
                    <div id="accionesSection">
                        <hr>
                        <h6>Gestionar Reporte</h6>
                        <div class="mb-3">
                            <label class="form-label">Cambiar Estado:</label>
                            <select class="form-select" id="nuevoEstadoReporte">
                                <option value="pendiente">Pendiente</option>
                                <option value="en_proceso">En Proceso</option>
                                <option value="resuelto">Resuelto</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nota (opcional):</label>
                            <textarea class="form-control" id="notaAdmin" rows="3" 
                                      placeholder="Agregar comentarios sobre la resolución..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="actualizarReporte()">
                        <i class="bi bi-check-circle"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        const API_URL = 'http://localhost/ug-bathfinder/api/v1';
        let adminToken = '';
        let adminUser = null;
        let reportesData = [];
        let selectedReporte = null;
        let detalleModal;

        // Verificar autenticación
        $(document).ready(function() {
            adminToken = sessionStorage.getItem('admin_token');
            const userStr = sessionStorage.getItem('admin_user');
            
            if (!adminToken || !userStr) {
                window.location.href = 'index.php';
                return;
            }
            
            adminUser = JSON.parse(userStr);
            
            // Mostrar info del usuario
            $('#userName').text(adminUser.nombre_completo || adminUser.email);
            $('#userAvatar').text((adminUser.nombre_completo || adminUser.email).charAt(0).toUpperCase());
            
            detalleModal = new bootstrap.Modal(document.getElementById('detalleModal'));
            
            // Cargar datos
            loadReportes();
        });

        async function loadReportes() {
            try {
                const response = await fetch(`${API_URL}/reportes/get_all_admin.php`, {
                    headers: {
                        'Authorization': `Bearer ${adminToken}`
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    reportesData = data.data;
                    displayReportes(reportesData);
                }
            } catch (error) {
                console.error('Error al cargar reportes:', error);
                $('#reportesTableBody').html(`
                    <tr>
                        <td colspan="8" class="text-center text-danger">
                            Error al cargar datos
                        </td>
                    </tr>
                `);
            }
        }

        function displayReportes(reportes) {
            // Destruir DataTable existente
            if ($.fn.DataTable.isDataTable('#reportesTable')) {
                $('#reportesTable').DataTable().destroy();
            }
            
            const tbody = $('#reportesTableBody');
            tbody.empty();
            
            if (reportes.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No hay reportes que mostrar
                        </td>
                    </tr>
                `);
                return;
            }
            
            reportes.forEach(reporte => {
                tbody.append(`
                    <tr>
                        <td>#${reporte.id}</td>
                        <td>
                            <strong>${reporte.bano_nombre}</strong><br>
                            <small class="text-muted">${reporte.bano_codigo}</small>
                        </td>
                        <td>${reporte.usuario_email}</td>
                        <td>${formatTipo(reporte.tipo)}</td>
                        <td>
                            <span class="badge bg-${getUrgenciaColor(reporte.urgencia)}">
                                ${formatUrgencia(reporte.urgencia)}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-${getEstadoColor(reporte.estado)}">
                                ${formatEstado(reporte.estado)}
                            </span>
                        </td>
                        <td>${new Date(reporte.fecha_creacion).toLocaleDateString('es-EC')}</td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="verDetalle(${reporte.id})">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            
            // Inicializar DataTable
            $('#reportesTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 25,
                order: [[0, 'desc']]
            });
        }

        function applyFilters() {
            const estado = $('#filterEstado').val();
            const urgencia = $('#filterUrgencia').val();
            const tipo = $('#filterTipo').val();
            
            let filtered = reportesData;
            
            if (estado) {
                filtered = filtered.filter(r => r.estado === estado);
            }
            if (urgencia) {
                filtered = filtered.filter(r => r.urgencia === urgencia);
            }
            if (tipo) {
                filtered = filtered.filter(r => r.tipo === tipo);
            }
            
            displayReportes(filtered);
        }

        function clearFilters() {
            $('#filterEstado').val('');
            $('#filterUrgencia').val('');
            $('#filterTipo').val('');
            displayReportes(reportesData);
        }

        function verDetalle(reporteId) {
            selectedReporte = reportesData.find(r => r.id == reporteId);
            
            if (!selectedReporte) return;
            
            $('#detalleBano').text(selectedReporte.bano_nombre);
            $('#detalleCodigo').text(selectedReporte.bano_codigo);
            $('#detalleTipo').text(formatTipo(selectedReporte.tipo));
            $('#detalleUrgencia').html(`
                <span class="badge bg-${getUrgenciaColor(selectedReporte.urgencia)}">
                    ${formatUrgencia(selectedReporte.urgencia)}
                </span>
            `);
            $('#detalleEstado').html(`
                <span class="badge bg-${getEstadoColor(selectedReporte.estado)}">
                    ${formatEstado(selectedReporte.estado)}
                </span>
            `);
            $('#detalleDescripcion').text(selectedReporte.descripcion || 'Sin descripción');
            $('#detalleFecha').text(new Date(selectedReporte.fecha_creacion).toLocaleString('es-EC'));
            $('#detalleUsuario').text(selectedReporte.usuario_email);
            
            $('#nuevoEstadoReporte').val(selectedReporte.estado);
            $('#notaAdmin').val('');
            
            if (selectedReporte.nota_admin) {
                $('#notaAdminSection').show();
                $('#detalleNotaAdmin').text(selectedReporte.nota_admin);
            } else {
                $('#notaAdminSection').hide();
            }
            
            if (selectedReporte.estado === 'resuelto' || selectedReporte.estado === 'rechazado') {
                $('#accionesSection').hide();
            } else {
                $('#accionesSection').show();
            }
            
            detalleModal.show();
        }

        async function actualizarReporte() {
            if (!selectedReporte) return;
            
            const nuevoEstado = $('#nuevoEstadoReporte').val();
            const nota = $('#notaAdmin').val().trim();
            
            try {
                const response = await fetch(`${API_URL}/reportes/update_estado.php`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${adminToken}`
                    },
                    body: JSON.stringify({
                        reporte_id: selectedReporte.id,
                        estado: nuevoEstado,
                        nota_admin: nota || null
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    detalleModal.hide();
                    alert('Reporte actualizado correctamente');
                    loadReportes();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo actualizar el reporte'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al actualizar reporte');
            }
        }

        function formatTipo(tipo) {
            const tipos = {
                'limpieza': 'Limpieza',
                'dano_instalaciones': 'Daño en instalaciones',
                'sin_papel': 'Sin papel',
                'sin_agua': 'Sin agua',
                'puerta_danada': 'Puerta dañada',
                'sin_luz': 'Sin luz',
                'otro': 'Otro'
            };
            return tipos[tipo] || tipo;
        }

        function formatUrgencia(urgencia) {
            const urgencias = {
                'baja': 'Baja',
                'media': 'Media',
                'alta': 'Alta'
            };
            return urgencias[urgencia] || urgencia;
        }

        function formatEstado(estado) {
            const estados = {
                'pendiente': 'Pendiente',
                'en_proceso': 'En Proceso',
                'resuelto': 'Resuelto',
                'rechazado': 'Rechazado'
            };
            return estados[estado] || estado;
        }

        function getUrgenciaColor(urgencia) {
            switch(urgencia) {
                case 'baja': return 'info';
                case 'media': return 'warning';
                case 'alta': return 'danger';
                default: return 'secondary';
            }
        }

        function getEstadoColor(estado) {
            switch(estado) {
                case 'pendiente': return 'warning';
                case 'en_proceso': return 'info';
                case 'resuelto': return 'success';
                case 'rechazado': return 'danger';
                default: return 'secondary';
            }
        }

        function logout() {
            if (confirm('¿Está seguro de cerrar sesión?')) {
                sessionStorage.removeItem('admin_token');
                sessionStorage.removeItem('admin_user');
                window.location.href = 'index.php';
            }
        }
    </script>
</body>
</html>