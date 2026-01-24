<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin UG BathFinder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .stat-card .icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .stat-card.primary .icon {
            background: rgba(33, 150, 243, 0.1);
            color: var(--primary-color);
        }
        
        .stat-card.success .icon {
            background: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        
        .stat-card.warning .icon {
            background: rgba(255, 193, 7, 0.1);
            color: #FFC107;
        }
        
        .stat-card.danger .icon {
            background: rgba(244, 67, 54, 0.1);
            color: #F44336;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .stat-label {
            color: #757575;
            font-size: 14px;
        }
        
        .chart-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 25px;
        }
        
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
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
        
        .table-responsive {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
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
            <a href="dashboard.php" class="nav-item active">
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
                <h4 class="mb-0">Dashboard</h4>
                <small class="text-muted">Resumen general del sistema</small>
            </div>
            <div class="user-info">
                <div class="user-avatar" id="userAvatar">A</div>
                <div>
                    <div id="userName" style="font-weight: 600;"></div>
                    <small class="text-muted">Administrador</small>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card primary">
                    <div class="icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div class="stat-value" id="totalBanos">0</div>
                    <div class="stat-label">Total de Baños</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card success">
                    <div class="icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="stat-value" id="banosDisponibles">0</div>
                    <div class="stat-label">Disponibles</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card warning">
                    <div class="icon">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div class="stat-value" id="banosMantenimiento">0</div>
                    <div class="stat-label">En Mantenimiento</div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="stat-card danger">
                    <div class="icon">
                        <i class="bi bi-exclamation-circle-fill"></i>
                    </div>
                    <div class="stat-value" id="reportesPendientes">0</div>
                    <div class="stat-label">Reportes Pendientes</div>
                </div>
            </div>
        </div>

        <!-- Recent Reports -->
        <div class="chart-card">
            <h5 class="mb-3">
                <i class="bi bi-clock-history"></i> Reportes Recientes
            </h5>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Baño</th>
                            <th>Tipo</th>
                            <th>Urgencia</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="recentReportsTable">
                        <tr>
                            <td colspan="7" class="text-center">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_URL = 'http://localhost/ug-bathfinder/api/v1';
        let adminToken = '';
        let adminUser = null;

        // Verificar autenticación
        window.addEventListener('DOMContentLoaded', () => {
            adminToken = sessionStorage.getItem('admin_token');
            const userStr = sessionStorage.getItem('admin_user');
            
            if (!adminToken || !userStr) {
                window.location.href = 'index.php';
                return;
            }
            
            adminUser = JSON.parse(userStr);
            
            // Mostrar info del usuario
            document.getElementById('userName').textContent = adminUser.nombre_completo || adminUser.email;
            document.getElementById('userAvatar').textContent = (adminUser.nombre_completo || adminUser.email).charAt(0).toUpperCase();
            
            // Cargar datos
            loadStatistics();
            loadRecentReports();
        });

        async function loadStatistics() {
            try {
                const response = await fetch(`${API_URL}/banos/get_estadisticas.php`, {
                    headers: {
                        'Authorization': `Bearer ${adminToken}`
                    }
                });
                
                const data = await response.json();
                
                
                if (data.success) {
                    
                    document.getElementById('totalBanos').textContent = data.data.banos.total || 0;
                    document.getElementById('banosDisponibles').textContent = data.data.banos.disponibles || 0;
                    document.getElementById('banosMantenimiento').textContent = data.data.banos.mantenimiento || 0;
                    document.getElementById('reportesPendientes').textContent = data.data.reportes.pendientes || 0;
                    
                    
                } else {
                    console.error('❌ Error en respuesta:', data.error);
                }
            } catch (error) {
                console.error('❌ Error al cargar estadísticas:', error);
            }
        }

        async function loadRecentReports() {
            try {
                const response = await fetch(`${API_URL}/reportes/get_all_admin.php`, {
                    headers: {
                        'Authorization': `Bearer ${adminToken}`
                    }
                });
                
                const data = await response.json();
                
                
                if (data.success) {
                    const reports = data.data.slice(0, 10); 
                    displayReports(reports);
                }
            } catch (error) {
                console.error('❌ Error al cargar reportes:', error);
                document.getElementById('recentReportsTable').innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            Error al cargar reportes
                        </td>
                    </tr>
                `;
            }
        }

        function displayReports(reports) {
            const tbody = document.getElementById('recentReportsTable');
            
            if (reports.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            No hay reportes recientes
                        </td>
                    </tr>
                `;
                return;
            }
            
            tbody.innerHTML = reports.map(report => `
                <tr>
                    <td>#${report.id}</td>
                    <td>
                        <strong>${report.bano_nombre}</strong><br>
                        <small class="text-muted">${report.bano_codigo}</small>
                    </td>
                    <td>${formatTipo(report.tipo)}</td>
                    <td>
                        <span class="badge bg-${getUrgenciaColor(report.urgencia)}">
                            ${formatUrgencia(report.urgencia)}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-${getEstadoColor(report.estado)}">
                            ${formatEstado(report.estado)}
                        </span>
                    </td>
                    <td>${new Date(report.fecha_creacion).toLocaleDateString('es-EC')}</td>
                    <td>
                        <a href="reportes.php?id=${report.id}" class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
            `).join('');
            
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
                'en_proceso': 'En proceso',
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