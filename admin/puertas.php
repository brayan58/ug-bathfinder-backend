<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Puertas - Admin UG BathFinder</title>
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
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-abierta {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        
        .status-cerrada {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
        }
        
        .principal-badge {
            background: rgba(33, 150, 243, 0.1);
            color: #2196F3;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
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
            <a href="puertas.php" class="nav-item active">
                <i class="bi bi-door-open"></i>
                Gestión de Puertas
            </a>
            <a href="reportes.php" class="nav-item">
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
                <h4 class="mb-0">Gestión de Puertas</h4>
                <small class="text-muted">Administrar entradas de la Ciudadela Universitaria</small>
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="bi bi-door-open"></i> Lista de Puertas
                </h5>
                <div>
                    <span class="badge bg-success me-2">
                        <i class="bi bi-circle-fill"></i> Abierta
                    </span>
                    <span class="badge bg-danger">
                        <i class="bi bi-circle-fill"></i> Cerrada
                    </span>
                </div>
            </div>
            
            <div class="table-responsive">
                <table id="puertasTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Principal</th>
                            <th>Horario</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="puertasTableBody">
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

    <!-- Modal Cambiar Estado -->
    <div class="modal fade" id="cambiarEstadoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square"></i> Cambiar Estado de Puerta
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Puerta:</label>
                        <p id="puertaInfo" class="form-control-plaintext fw-bold"></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Estado Actual:</label>
                        <p id="estadoActual" class="form-control-plaintext">
                            <span id="estadoActualBadge"></span>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nuevo Estado:</label>
                        <select class="form-select" id="nuevoEstado">
                            <option value="abierta">🟢 Abierta</option>
                            <option value="cerrada">🔴 Cerrada</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="confirmarCambioEstado()">
                        Guardar Cambios
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
        let puertasData = [];
        let selectedPuertaId = null;
        let cambiarEstadoModal;

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
            
            cambiarEstadoModal = new bootstrap.Modal(document.getElementById('cambiarEstadoModal'));
            
            // Cargar datos
            loadPuertas();
        });

        async function loadPuertas() {
            try {
                const response = await fetch(`${API_URL}/puertas/get_all.php`, {
                    headers: {
                        'Authorization': `Bearer ${adminToken}`
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    puertasData = data.data;
                    displayPuertas(puertasData);
                }
            } catch (error) {
                console.error('Error al cargar puertas:', error);
                $('#puertasTableBody').html(`
                    <tr>
                        <td colspan="8" class="text-center text-danger">
                            Error al cargar datos
                        </td>
                    </tr>
                `);
            }
        }

        function displayPuertas(puertas) {
            // Destruir DataTable existente si hay uno
            if ($.fn.DataTable.isDataTable('#puertasTable')) {
                $('#puertasTable').DataTable().destroy();
            }
            
            const tbody = $('#puertasTableBody');
            tbody.empty();
            
            if (puertas.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            No hay puertas registradas
                        </td>
                    </tr>
                `);
                return;
            }
            
            puertas.forEach(puerta => {
                const horario = puerta.horario_apertura && puerta.horario_cierre 
                    ? `${puerta.horario_apertura.substring(0,5)} - ${puerta.horario_cierre.substring(0,5)}`
                    : 'No definido';
                    
                tbody.append(`
                    <tr>
                        <td>${puerta.id}</td>
                        <td><strong>${puerta.codigo}</strong></td>
                        <td>${puerta.nombre}</td>
                        <td>${puerta.descripcion || 'Sin descripción'}</td>
                        <td>
                            ${puerta.es_principal == 1 ? 
                                '<span class="principal-badge">⭐ Principal</span>' : 
                                '<span class="text-muted">-</span>'}
                        </td>
                        <td>${horario}</td>
                        <td>
                            <span class="status-badge status-${puerta.estado}">
                                ${puerta.estado === 'abierta' ? '🟢 Abierta' : '🔴 Cerrada'}
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-sm btn-primary" onclick="abrirModalEstado(${puerta.id})" 
                                        title="Cambiar estado">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `);
            });
            
            // Inicializar DataTable
            $('#puertasTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 10,
                order: [[0, 'asc']]
            });
        }

        function abrirModalEstado(puertaId) {
            selectedPuertaId = puertaId;
            const puerta = puertasData.find(p => p.id == puertaId);
            
            if (!puerta) return;
            
            $('#puertaInfo').text(`${puerta.nombre} (${puerta.codigo})`);
            $('#estadoActual').html(`
                <span class="status-badge status-${puerta.estado}">
                    ${puerta.estado === 'abierta' ? '🟢 Abierta' : '🔴 Cerrada'}
                </span>
            `);
            $('#nuevoEstado').val(puerta.estado);
            
            cambiarEstadoModal.show();
        }

        async function confirmarCambioEstado() {
            const nuevoEstado = $('#nuevoEstado').val();
            
            if (!selectedPuertaId || !nuevoEstado) return;
            
            try {
                const response = await fetch(`${API_URL}/puertas/update_estado.php`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${adminToken}`
                    },
                    body: JSON.stringify({
                        puerta_id: selectedPuertaId,
                        estado: nuevoEstado
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    cambiarEstadoModal.hide();
                    
                    // Mostrar alerta de éxito
                    alert('Estado actualizado correctamente');
                    
                    // Recargar datos
                    loadPuertas();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo actualizar el estado'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al actualizar estado');
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