<?php
/**
 * Reportes de Calificaciones
 * Sistema de Control Escolar
 */

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn()) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$page_title = 'Reportes de Calificaciones';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-chart-line me-2"></i>Reportes de Calificaciones
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
                            <label for="materia" class="form-label">Materia</label>
                            <select class="form-select" id="materia" name="materia">
                                <option value="">Todas las materias</option>
                                <option value="matematicas" <?php echo (isset($_GET['materia']) && $_GET['materia'] == 'matematicas') ? 'selected' : ''; ?>>Matemáticas</option>
                                <option value="espanol" <?php echo (isset($_GET['materia']) && $_GET['materia'] == 'espanol') ? 'selected' : ''; ?>>Español</option>
                                <option value="ciencias" <?php echo (isset($_GET['materia']) && $_GET['materia'] == 'ciencias') ? 'selected' : ''; ?>>Ciencias</option>
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
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Generar
                            </button>
                            <button type="button" class="btn btn-success" onclick="exportarPDF()">
                                <i class="fas fa-file-pdf"></i> PDF
                            </button>
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
                $calificaciones = obtenerCalificacionesPorMateria();
                
                // Calcular estadísticas de calificaciones
                $total_calificaciones = 0;
                $aprobados = 0;
                $reprobados = 0;
                $en_riesgo = 0;
                
                foreach ($calificaciones as $cal) {
                    $total_calificaciones += $cal['aprobados'] + $cal['reprobados'];
                    $aprobados += $cal['aprobados'];
                    $reprobados += $cal['reprobados'];
                }
                
                $porcentaje_aprobados = $total_calificaciones > 0 ? round(($aprobados / $total_calificaciones) * 100, 0) : 0;
                $porcentaje_reprobados = $total_calificaciones > 0 ? round(($reprobados / $total_calificaciones) * 100, 0) : 0;
                $porcentaje_riesgo = max(0, 100 - $porcentaje_aprobados - $porcentaje_reprobados);
                ?>
                <div class="col-3 mb-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['promedio_general']; ?></h4>
                            <p class="mb-0">Promedio General</p>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $porcentaje_aprobados; ?>%</h4>
                            <p class="mb-0">Aprobados</p>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-3">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $porcentaje_riesgo; ?>%</h4>
                            <p class="mb-0">En Riesgo</p>
                        </div>
                    </div>
                </div>
                <div class="col-3 mb-3">
                    <div class="card bg-danger text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $porcentaje_reprobados; ?>%</h4>
                            <p class="mb-0">Reprobados</p>
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
                    <h5 class="card-title mb-0">Estadísticas por Materia</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-primary me-2" onclick="imprimir()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportarExcel()">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Materia</th>
                                    <th>Grupo</th>
                                    <th>Total Estudiantes</th>
                                    <th>Promedio</th>
                                    <th>Más Alta</th>
                                    <th>Más Baja</th>
                                    <th>Aprobados</th>
                                    <th>Reprobados</th>
                                    <th>% Aprobación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $calificaciones = obtenerCalificacionesPorMateria();
                                foreach ($calificaciones as $cal): 
                                    $porcentaje_aprobacion = $cal['total_estudiantes'] > 0 ? round(($cal['aprobados'] / $cal['total_estudiantes']) * 100, 0) : 0;
                                    
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
                                    <td><?php echo htmlspecialchars($cal['materia']); ?></td>
                                    <td><?php echo htmlspecialchars($cal['grupo']); ?></td>
                                    <td><?php echo $cal['total_estudiantes']; ?></td>
                                    <td><?php echo round($cal['promedio'], 1); ?></td>
                                    <td><?php echo round($cal['mas_alta'], 1); ?></td>
                                    <td><?php echo round($cal['mas_baja'], 1); ?></td>
                                    <td><?php echo $cal['aprobados']; ?></td>
                                    <td><?php echo $cal['reprobados']; ?></td>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo $porcentaje_aprobacion; ?>%</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function imprimir() {
    window.print();
}

function exportarPDF() {
    window.open('exportar.php?tipo=pdf&reporte=calificaciones', '_blank');
}

function exportarExcel() {
    window.open('exportar.php?tipo=excel&reporte=calificaciones', '_blank');
}

function exportarWord() {
    window.open('exportar.php?tipo=word&reporte=calificaciones', '_blank');
}

function exportarNotepad() {
    window.open('exportar.php?tipo=notepad&reporte=calificaciones', '_blank');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
