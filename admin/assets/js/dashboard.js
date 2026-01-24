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
            // Actualizar cards de baños
            document.getElementById('totalBanos').textContent = data.data.banos.total || 0;
            document.getElementById('banosDisponibles').textContent = data.data.banos.disponibles || 0;
            document.getElementById('banosMantenimiento').textContent = data.data.banos.mantenimiento || 0;
            
            // Actualizar card de reportes pendientes
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