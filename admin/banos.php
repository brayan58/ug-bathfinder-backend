<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Baños - Admin UG BathFinder</title>
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
        
        .status-disponible {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        
        .status-mantenimiento {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
        }
        
        .status-cerrado {
            background: rgba(158, 158, 158, 0.1);
            color: #9E9E9E;
        }
    </style>
</head>
<body>
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
            <a href="banos.php" class="nav-item active">
                <i class="bi bi-geo-alt"></i>
                Gestión de Baños
            </a>
            <a href="puertas.php" class="nav-item">
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

    <div class="main-content">
        <div class="topbar">
            <div>
                <h4 class="mb-0">Gestión de Baños</h4>
                <small class="text-muted">Administrar estados de servicios sanitarios</small>
            </div>
            <div class="user-info">
                <div class="user-avatar" id="userAvatar">A</div>
                <div>
                    <div id="userName" style="font-weight: 600;"></div>
                    <small class="text-muted">Administrador</small>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <i class="bi bi-list-check"></i> Lista de Baños
                </h5>
                <div>
                    <span class="badge bg-success me-2">
                        <i class="bi bi-circle-fill"></i> Disponible
                    </span>
                    <span class="badge bg-danger me-2">
                        <i class="bi bi-circle-fill"></i> Mantenimiento
                    </span>
                    <span class="badge bg-secondary">
                        <i class="bi bi-circle-fill"></i> Cerrado
                    </span>
                </div>
            </div>
            
            <div class="table-responsive">
                <table id="banosTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Facultad</th>
                            <th>Piso</th>
                            <th>Género</th>
                            <th>Accesibilidad</th>
                            <th>Estado</th>
                            <th>Último Reporte</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="banosTableBody">
                        <tr>
                            <td colspan="10" class="text-center">
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

    <div class="modal fade" id="cambiarEstadoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square"></i> Cambiar Estado de Baño
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Baño:</label>
                        <p id="banoInfo" class="form-control-plaintext fw-bold"></p>
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
                            <option value="disponible">Disponible</option>
                            <option value="mantenimiento">Mantenimiento</option>
                            <option value="cerrado">Cerrado</option>
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
        let banosData = [];
        let selectedBanoId = null;
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
            loadBanos();
        });

        async function loadBanos() {
            try {
                const response = await fetch(`${API_URL}/banos/get_all.php`, {
                    headers: {
                        'Authorization': `Bearer ${adminToken}`
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    banosData = data.data;
                    displayBanos(banosData);
                }
            } catch (error) {
                console.error('Error al cargar baños:', error);
                $('#banosTableBody').html(`
                    <tr>
                        <td colspan="10" class="text-center text-danger">
                            Error al cargar datos
                        </td>
                    </tr>
                `);
            }
        }

        function displayBanos(banos) {
            // Destruir DataTable existente si hay uno
            if ($.fn.DataTable.isDataTable('#banosTable')) {
                $('#banosTable').DataTable().destroy();
            }
            
            const tbody = $('#banosTableBody');
            tbody.empty();
            
            banos.forEach(bano => {
                tbody.append(`
                    <tr>
                        <td>${bano.id}</td>
                        <td><strong>${bano.codigo}</strong></td>
                        <td>${bano.nombre}</td>
                        <td>${bano.facultad_nombre || 'N/A'}</td>
                        <td>${bano.piso}</td>
                        <td>${formatGenero(bano.genero)}</td>
                        <td>
                            ${bano.accesibilidad == 1 ? 
                                '<i class="bi bi-check-circle-fill text-success"></i>' : 
                                '<i class="bi bi-x-circle-fill text-secondary"></i>'}
                        </td>
                        <td>
                            <span class="status-badge status-${bano.estado}">
                                ${formatEstado(bano.estado)}
                            </span>
                        </td>
                        <td>${bano.ultimo_reporte_fecha ? new Date(bano.ultimo_reporte_fecha).toLocaleDateString('es-EC') : 'Sin reportes'}</td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-sm btn-primary" onclick="abrirModalEstado(${bano.id})" 
                                        title="Cambiar estado">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `);
            });
            
            // Inicializar DataTable 
            $('#banosTable').DataTable({
                language: {
                    "decimal": "",
                    "emptyTable": "No hay datos disponibles",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron registros coincidentes",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                pageLength: 25,
                order: [[0, 'asc']]
            });
        }

        function abrirModalEstado(banoId) {
            selectedBanoId = banoId;
            const bano = banosData.find(b => b.id == banoId);
            
            if (!bano) return;
            
            $('#banoInfo').text(`${bano.nombre} (${bano.codigo})`);
            $('#estadoActual').html(`
                <span class="status-badge status-${bano.estado}">
                    ${formatEstado(bano.estado)}
                </span>
            `);
            $('#nuevoEstado').val(bano.estado);
            
            cambiarEstadoModal.show();
        }

        async function confirmarCambioEstado() {
            const nuevoEstado = $('#nuevoEstado').val();
            
            if (!selectedBanoId || !nuevoEstado) return;
            
            try {
                const response = await fetch(`${API_URL}/banos/update_estado.php`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${adminToken}`
                    },
                    body: JSON.stringify({
                        bano_id: selectedBanoId,
                        estado: nuevoEstado
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    cambiarEstadoModal.hide();
                    
                    // Mostrar alerta de éxito
                    alert('Estado actualizado correctamente');
                    
                    // Recargar datos
                    loadBanos();
                } else {
                    alert('Error: ' + (data.error || 'No se pudo actualizar el estado'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al actualizar estado');
            }
        }

        function formatGenero(genero) {
            const generos = {
                'hombres': 'Hombres',
                'mujeres': 'Mujeres',
                'universal': 'Universal'
            };
            return generos[genero] || genero;
        }

        function formatEstado(estado) {
            const estados = {
                'disponible': 'Disponible',
                'mantenimiento': 'Mantenimiento',
                'cerrado': 'Cerrado'
            };
            return estados[estado] || estado;
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