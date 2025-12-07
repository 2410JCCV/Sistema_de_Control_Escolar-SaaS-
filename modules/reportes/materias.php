<?php
/**
 * Reportes de Materias
 * Sistema de Control Escolar
 */

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn()) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$page_title = 'Reportes de Materias';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-book me-2"></i>Reportes de Materias
                </h2>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Volver a Reportes
                </a>
            </div>
        </div>
    </div>
    
    <!-- Filtros de reporte -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Filtros de Reporte</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="materia" class="form-label">Materia</label>
                            <select class="form-select" id="materia" name="materia">
                                <option value="">Todas las materias</option>
                                <option value="matematicas" <?php echo (isset($_GET['materia']) && $_GET['materia'] == 'matematicas') ? 'selected' : ''; ?>>Matemáticas</option>
                                <option value="espanol" <?php echo (isset($_GET['materia']) && $_GET['materia'] == 'espanol') ? 'selected' : ''; ?>>Español</option>
                                <option value="ciencias" <?php echo (isset($_GET['materia']) && $_GET['materia'] == 'ciencias') ? 'selected' : ''; ?>>Ciencias</option>
                                <option value="historia" <?php echo (isset($_GET['materia']) && $_GET['materia'] == 'historia') ? 'selected' : ''; ?>>Historia</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="nivel" class="form-label">Nivel</label>
                            <select class="form-select" id="nivel" name="nivel">
                                <option value="">Todos los niveles</option>
                                <option value="primaria" <?php echo (isset($_GET['nivel']) && $_GET['nivel'] == 'primaria') ? 'selected' : ''; ?>>Primaria</option>
                                <option value="secundaria" <?php echo (isset($_GET['nivel']) && $_GET['nivel'] == 'secundaria') ? 'selected' : ''; ?>>Secundaria</option>
                                <option value="preparatoria" <?php echo (isset($_GET['nivel']) && $_GET['nivel'] == 'preparatoria') ? 'selected' : ''; ?>>Preparatoria</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="profesor" class="form-label">Profesor</label>
                            <select class="form-select" id="profesor" name="profesor">
                                <option value="">Todos los profesores</option>
                                <option value="1" <?php echo (isset($_GET['profesor']) && $_GET['profesor'] == '1') ? 'selected' : ''; ?>>María González</option>
                                <option value="2" <?php echo (isset($_GET['profesor']) && $_GET['profesor'] == '2') ? 'selected' : ''; ?>>Juan Pérez</option>
                                <option value="3" <?php echo (isset($_GET['profesor']) && $_GET['profesor'] == '3') ? 'selected' : ''; ?>>Ana Martínez</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Generar
                            </button>
                            <div class="btn-group">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportarPDF()"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportarExcel()"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportarWord()"><i class="fas fa-file-word me-2"></i>Word</a></li>
                                </ul>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estadísticas generales -->
    <div class="row mb-4">
        <div class="col-lg-9 col-md-12">
            <div class="row">
                <?php 
                $estadisticas = obtenerEstadisticasGenerales();
                ?>
                <div class="col-3 mb-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['total_materias']; ?></h4>
                            <p class="mb-0">Total Materias</p>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['promedio_general']; ?></h4>
                            <p class="mb-0">Promedio General</p>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['total_profesores']; ?></h4>
                            <p class="mb-0">Profesores Activos</p>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-3">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['porcentaje_aprobacion']; ?>%</h4>
                            <p class="mb-0">% Aprobación</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-12">
            <!-- Espacio reservado para los botones Generar y Exportar -->
        </div>
    </div>
    
    <!-- Resultados del reporte -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Rendimiento por Materia</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="imprimir()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="subirReporte()">
                            <i class="fas fa-upload"></i> Subir Reporte
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Materia</th>
                                    <th>Código</th>
                                    <th>Nivel</th>
                                    <th>Profesor</th>
                                    <th>Grupos</th>
                                    <th>Estudiantes</th>
                                    <th>Promedio</th>
                                    <th>Aprobados</th>
                                    <th>Reprobados</th>
                                    <th>% Aprobación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $materias = obtenerMaterias();
                                foreach ($materias as $materia): 
                                    $porcentaje_aprobacion = $materia['aprobados'] > 0 ? round(($materia['aprobados'] / ($materia['aprobados'] + ($materia['aprobados'] - $materia['aprobados']))) * 100, 0) : 0;
                                    
                                    // Obtener información adicional de la materia
                                    global $pdo;
                                    $stmt = $pdo->prepare("
                                        SELECT 
                                            COUNT(DISTINCT h.grupo_id) as grupos,
                                            COUNT(DISTINCT e.id) as estudiantes,
                                            CONCAT(p.nombre, ' ', p.apellido_paterno) as profesor
                                        FROM materias m
                                        LEFT JOIN horarios h ON m.id = h.materia_id AND h.estado = 'activo'
                                        LEFT JOIN estudiantes e ON h.grupo_id = e.grupo_id AND e.estado = 'activo'
                                        LEFT JOIN profesores p ON h.profesor_id = p.id
                                        WHERE m.id = ?
                                        GROUP BY m.id
                                    ");
                                    $stmt->execute([$materia['id']]);
                                    $info_adicional = $stmt->fetch(PDO::FETCH_ASSOC);
                                    
                                    $grupos = $info_adicional['grupos'] ?? 0;
                                    $estudiantes = $info_adicional['estudiantes'] ?? 0;
                                    $profesor = $info_adicional['profesor'] ?? 'Sin asignar';
                                    $reprobados = $estudiantes - $materia['aprobados'];
                                    
                                    // Determinar color del badge basado en el porcentaje de aprobación
                                    if ($porcentaje_aprobacion >= 90) {
                                        $badge_class = 'bg-success';
                                    } elseif ($porcentaje_aprobacion >= 80) {
                                        $badge_class = 'bg-success';
                                    } elseif ($porcentaje_aprobacion >= 70) {
                                        $badge_class = 'bg-warning';
                                    } else {
                                        $badge_class = 'bg-danger';
                                    }
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($materia['materia']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($materia['codigo']); ?></td>
                                    <td><?php echo htmlspecialchars($materia['nivel']); ?></td>
                                    <td><?php echo htmlspecialchars($profesor); ?></td>
                                    <td><?php echo $grupos; ?></td>
                                    <td><?php echo $estudiantes; ?></td>
                                    <td><?php echo round($materia['promedio'], 1); ?></td>
                                    <td><?php echo $materia['aprobados']; ?></td>
                                    <td><?php echo $reprobados; ?></td>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo $porcentaje_aprobacion; ?>%</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Gráfico de rendimiento por materia -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>Promedio por Materia</h6>
                            <canvas id="materiasChart" width="400" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <h6>Distribución de Aprobados</h6>
                            <canvas id="aprobadosChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para subir reporte -->
<div class="modal fade" id="subirReporteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subir Reporte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="archivo" class="form-label">Seleccionar Archivo</label>
                        <input type="file" class="form-control" id="archivo" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx">
                        <div class="form-text">Formatos permitidos: PDF, Word, Excel</div>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="procesarSubida()">Subir Reporte</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de promedio por materia
const ctx1 = document.getElementById('materiasChart').getContext('2d');
const materiasChart = new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: ['Matemáticas', 'Español', 'Ciencias', 'Historia', 'Educación Física'],
        datasets: [{
            label: 'Promedio',
            data: [8.7, 8.5, 8.8, 8.3, 9.2],
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                max: 10
            }
        }
    }
});

// Gráfico de distribución de aprobados
const ctx2 = document.getElementById('aprobadosChart').getContext('2d');
const aprobadosChart = new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Aprobados', 'Reprobados'],
        datasets: [{
            data: [89, 11],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true
    }
});

function imprimir() {
    window.print();
}

function exportarPDF() {
    window.open('exportar.php?tipo=pdf&reporte=materias', '_blank');
}

function exportarExcel() {
    window.open('exportar.php?tipo=excel&reporte=materias', '_blank');
}

function exportarWord() {
    window.open('exportar.php?tipo=word&reporte=materias', '_blank');
}

function exportarNotepad() {
    window.open('exportar.php?tipo=notepad&reporte=materias', '_blank');
}

function subirReporte() {
    const modal = new bootstrap.Modal(document.getElementById('subirReporteModal'));
    modal.show();
}

function procesarSubida() {
    const archivo = document.getElementById('archivo').files[0];
    const descripcion = document.getElementById('descripcion').value;
    
    if (!archivo) {
        alert('Por favor selecciona un archivo');
        return;
    }
    
    // Aquí iría la lógica para subir el archivo
    alert('Reporte subido correctamente');
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('subirReporteModal'));
    modal.hide();
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
