<?php
/**
 * Reportes de Grupos
 * Sistema de Control Escolar
 */

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn()) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$page_title = 'Reportes de Grupos';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-users me-2"></i>Reportes de Grupos
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
                            <label for="grupo" class="form-label">Grupo</label>
                            <select class="form-select" id="grupo" name="grupo">
                                <option value="">Todos los grupos</option>
                                <option value="1" <?php echo (isset($_GET['grupo']) && $_GET['grupo'] == '1') ? 'selected' : ''; ?>>1° A - Primaria</option>
                                <option value="2" <?php echo (isset($_GET['grupo']) && $_GET['grupo'] == '2') ? 'selected' : ''; ?>>1° B - Primaria</option>
                                <option value="3" <?php echo (isset($_GET['grupo']) && $_GET['grupo'] == '3') ? 'selected' : ''; ?>>2° A - Primaria</option>
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
                            <label for="periodo" class="form-label">Período</label>
                            <select class="form-select" id="periodo" name="periodo">
                                <option value="">Todos los períodos</option>
                                <option value="1" <?php echo (isset($_GET['periodo']) && $_GET['periodo'] == '1') ? 'selected' : ''; ?>>1° Trimestre</option>
                                <option value="2" <?php echo (isset($_GET['periodo']) && $_GET['periodo'] == '2') ? 'selected' : ''; ?>>2° Trimestre</option>
                                <option value="3" <?php echo (isset($_GET['periodo']) && $_GET['periodo'] == '3') ? 'selected' : ''; ?>>3° Trimestre</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end justify-content-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Generar
                            </button>
                            <div class="btn-group dropend">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="true">
                                    <i class="fas fa-download"></i> Exportar
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" style="z-index: 1050;">
                                    <li><a class="dropdown-item" href="#" onclick="exportarPDF()"><i class="fas fa-file-pdf me-2"></i>PDF</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportarExcel()"><i class="fas fa-file-excel me-2"></i>Excel</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportarWord()"><i class="fas fa-file-word me-2"></i>Word</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportarNotepad()"><i class="fas fa-file-alt me-2"></i>Notepad</a></li>
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
                $grupos = obtenerGrupos();
                $total_estudiantes_grupos = array_sum(array_column($grupos, 'estudiantes'));
                $promedio_por_grupo = count($grupos) > 0 ? round($total_estudiantes_grupos / count($grupos), 1) : 0;
                ?>
                <div class="col-3 mb-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['total_grupos']; ?></h4>
                            <p class="mb-0">Total Grupos</p>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $total_estudiantes_grupos; ?></h4>
                            <p class="mb-0">Total Estudiantes</p>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $promedio_por_grupo; ?></h4>
                            <p class="mb-0">Promedio por Grupo</p>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-3">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['promedio_general']; ?></h4>
                            <p class="mb-0">Promedio General</p>
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
                    <h5 class="card-title mb-0">Información Detallada por Grupo</h5>
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
                                    <th>Grupo</th>
                                    <th>Nivel</th>
                                    <th>Profesor</th>
                                    <th>Estudiantes</th>
                                    <th>Promedio</th>
                                    <th>Aprobados</th>
                                    <th>Reprobados</th>
                                    <th>% Aprobación</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $grupos = obtenerGrupos();
                                foreach ($grupos as $grupo): 
                                    $porcentaje_aprobacion = $grupo['estudiantes'] > 0 ? round(($grupo['aprobados'] / $grupo['estudiantes']) * 100, 0) : 0;
                                    $reprobados = $grupo['estudiantes'] - $grupo['aprobados'];
                                    
                                    // Obtener el profesor del grupo (primer profesor encontrado en horarios)
                                    global $pdo;
                                    $stmt = $pdo->prepare("
                                        SELECT CONCAT(p.nombre, ' ', p.apellido_paterno) as profesor
                                        FROM horarios h
                                        JOIN profesores p ON h.profesor_id = p.id
                                        WHERE h.grupo_id = ? AND h.estado = 'activo'
                                        LIMIT 1
                                    ");
                                    $stmt->execute([$grupo['id']]);
                                    $profesor = $stmt->fetch(PDO::FETCH_ASSOC);
                                    $nombre_profesor = $profesor ? $profesor['profesor'] : 'Sin asignar';
                                    
                                    // Determinar estado basado en el porcentaje de aprobación
                                    if ($porcentaje_aprobacion >= 90) {
                                        $estado_class = 'bg-success';
                                        $estado_texto = 'Excelente';
                                    } elseif ($porcentaje_aprobacion >= 80) {
                                        $estado_class = 'bg-success';
                                        $estado_texto = 'Bueno';
                                    } elseif ($porcentaje_aprobacion >= 70) {
                                        $estado_class = 'bg-warning';
                                        $estado_texto = 'Regular';
                                    } else {
                                        $estado_class = 'bg-danger';
                                        $estado_texto = 'Necesita Atención';
                                    }
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($grupo['grupo']); ?></strong></td>
                                    <td><?php echo explode(' - ', $grupo['grupo'])[1] ?? 'N/A'; ?></td>
                                    <td><?php echo htmlspecialchars($nombre_profesor); ?></td>
                                    <td><?php echo $grupo['estudiantes']; ?></td>
                                    <td><?php echo round($grupo['promedio'], 1); ?></td>
                                    <td><?php echo $grupo['aprobados']; ?></td>
                                    <td><?php echo $reprobados; ?></td>
                                    <td><span class="badge <?php echo $estado_class; ?>"><?php echo $porcentaje_aprobacion; ?>%</span></td>
                                    <td><span class="badge <?php echo $estado_class; ?>"><?php echo $estado_texto; ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Gráfico de rendimiento por grupo -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6>Rendimiento por Grupo</h6>
                            <canvas id="gruposChart" width="400" height="100"></canvas>
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
// Gráfico de rendimiento
const ctx = document.getElementById('gruposChart').getContext('2d');
const gruposChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['1° A', '1° B', '2° A', '2° B', '3° A'],
        datasets: [{
            label: 'Promedio por Grupo',
            data: [8.7, 8.4, 8.9, 8.2, 8.6],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(255, 205, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)'
            ],
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

function imprimir() {
    window.print();
}

function exportarPDF() {
    window.open('exportar.php?tipo=pdf&reporte=grupos', '_blank');
}

function exportarExcel() {
    window.open('exportar.php?tipo=excel&reporte=grupos', '_blank');
}

function exportarWord() {
    window.open('exportar.php?tipo=word&reporte=grupos', '_blank');
}

function exportarNotepad() {
    window.open('exportar.php?tipo=notepad&reporte=grupos', '_blank');
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
