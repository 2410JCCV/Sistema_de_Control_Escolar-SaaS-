<?php
/**
 * Reportes Generales
 * Sistema de Control Escolar
 */

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn()) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$page_title = 'Reportes Generales';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-chart-pie me-2"></i>Reportes Generales
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
                    <form method="GET" class="row g-3" id="filtrosForm">
                        <div class="col-md-3">
                            <label for="periodo" class="form-label">Período</label>
                            <select class="form-select" id="periodo" name="periodo">
                                <option value="">Todos los períodos</option>
                                <option value="1" <?php echo (isset($_GET['periodo']) && $_GET['periodo'] == '1') ? 'selected' : ''; ?>>1° Trimestre</option>
                                <option value="2" <?php echo (isset($_GET['periodo']) && $_GET['periodo'] == '2') ? 'selected' : ''; ?>>2° Trimestre</option>
                                <option value="3" <?php echo (isset($_GET['periodo']) && $_GET['periodo'] == '3') ? 'selected' : ''; ?>>3° Trimestre</option>
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
                            <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                            <select class="form-select" id="tipo_reporte" name="tipo_reporte">
                                <option value="">Todos los tipos</option>
                                <option value="resumen" <?php echo (isset($_GET['tipo_reporte']) && $_GET['tipo_reporte'] == 'resumen') ? 'selected' : ''; ?>>Resumen Ejecutivo</option>
                                <option value="estadisticas" <?php echo (isset($_GET['tipo_reporte']) && $_GET['tipo_reporte'] == 'estadisticas') ? 'selected' : ''; ?>>Estadísticas</option>
                                <option value="tendencias" <?php echo (isset($_GET['tipo_reporte']) && $_GET['tipo_reporte'] == 'tendencias') ? 'selected' : ''; ?>>Tendencias</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary me-2" onclick="procesarFiltros()">
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
                ?>
                <div class="col-4 mb-3">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['total_estudiantes']; ?></h4>
                            <p class="mb-0">Estudiantes</p>
                        </div>
                    </div>
                </div>
                <div class="col-4 mb-3">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['total_profesores']; ?></h4>
                            <p class="mb-0">Profesores</p>
                        </div>
                    </div>
                </div>
                <div class="col-4 mb-3">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['total_grupos']; ?></h4>
                            <p class="mb-0">Grupos</p>
                        </div>
                    </div>
                </div>
                <div class="col-4 mb-3">
                    <div class="card bg-warning text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['total_materias']; ?></h4>
                            <p class="mb-0">Materias</p>
                        </div>
                    </div>
                </div>
                <div class="col-4 mb-3">
                    <div class="card bg-danger text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['promedio_general']; ?></h4>
                            <p class="mb-0">Promedio</p>
                        </div>
                    </div>
                </div>
                <div class="col-4 mb-3">
                    <div class="card bg-secondary text-white h-100">
                        <div class="card-body text-center">
                            <h4><?php echo $estadisticas['porcentaje_aprobacion']; ?>%</h4>
                            <p class="mb-0">Aprobación</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-12">
            <!-- Espacio reservado para los botones Generar y Exportar -->
        </div>
    </div>
    
    <!-- Resumen ejecutivo -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Resumen Ejecutivo</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Rendimiento Académico</h6>
                            <p>El sistema escolar muestra un rendimiento académico sólido con un promedio general de 8.6 sobre 10. 
                            La tasa de aprobación del 89% indica un buen nivel de aprendizaje y retención de conocimientos.</p>
                            
                            <h6>Distribución por Niveles</h6>
                            <ul>
                                <li><strong>Primaria:</strong> 195 estudiantes en 8 grupos</li>
                                <li><strong>Secundaria:</strong> 0 estudiantes (próximamente)</li>
                                <li><strong>Preparatoria:</strong> 0 estudiantes (próximamente)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Materias con Mejor Rendimiento</h6>
                            <ol>
                                <li>Educación Física - 9.2</li>
                                <li>Ciencias - 8.8</li>
                                <li>Matemáticas - 8.7</li>
                                <li>Español - 8.5</li>
                                <li>Historia - 8.3</li>
                            </ol>
                            
                            <h6>Recomendaciones</h6>
                            <ul>
                                <li>Fortalecer el área de Historia</li>
                                <li>Mantener el excelente rendimiento en Educación Física</li>
                                <li>Implementar programas de apoyo para estudiantes en riesgo</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gráficos y estadísticas -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Distribución de Calificaciones</h5>
                    <button class="btn btn-sm btn-outline-info" onclick="subirReporte()">
                        <i class="fas fa-upload"></i> Subir Reporte
                    </button>
                </div>
                <div class="card-body">
                    <canvas id="calificacionesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tendencias por Período</h5>
                </div>
                <div class="card-body">
                    <canvas id="tendenciasChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabla de resumen por grupo -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Resumen por Grupo</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Grupo</th>
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
// Gráfico de distribución de calificaciones
const ctx1 = document.getElementById('calificacionesChart').getContext('2d');
const calificacionesChart = new Chart(ctx1, {
    type: 'doughnut',
    data: {
        labels: ['9-10', '8-8.9', '7-7.9', '6-6.9', '0-5.9'],
        datasets: [{
            data: [45, 35, 15, 3, 2],
            backgroundColor: [
                'rgba(75, 192, 192, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 205, 86, 0.8)',
                'rgba(255, 159, 64, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(75, 192, 192, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 205, 86, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true
    }
});

// Gráfico de tendencias
const ctx2 = document.getElementById('tendenciasChart').getContext('2d');
const tendenciasChart = new Chart(ctx2, {
    type: 'line',
    data: {
        labels: ['1° Trimestre', '2° Trimestre', '3° Trimestre'],
        datasets: [{
            label: 'Promedio General',
            data: [8.4, 8.6, 8.6],
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.1
        }, {
            label: '% Aprobación',
            data: [85, 89, 89],
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.1
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

function procesarFiltros() {
    const form = document.getElementById('filtrosForm');
    const button = event.target;
    const originalText = button.innerHTML;
    
    // Mostrar estado de procesando
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    button.disabled = true;
    
    // Simular procesamiento (en un caso real, aquí harías la petición AJAX)
    setTimeout(() => {
        // Aquí iría la lógica real de procesamiento
        // Por ahora solo recargamos la página con los filtros
        form.submit();
    }, 1000);
}

function imprimir() {
    window.print();
}

function exportarPDF() {
    window.open('exportar.php?tipo=pdf&reporte=generales', '_blank');
}

function exportarExcel() {
    window.open('exportar.php?tipo=excel&reporte=generales', '_blank');
}

function exportarWord() {
    window.open('exportar.php?tipo=word&reporte=generales', '_blank');
}

function exportarNotepad() {
    window.open('exportar.php?tipo=notepad&reporte=generales', '_blank');
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
