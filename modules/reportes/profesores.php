<?php
/**
 * Reportes de Profesores
 * Sistema de Control Escolar
 */

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn()) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$page_title = 'Reportes de Profesores';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-chalkboard-teacher me-2"></i>Reportes de Profesores
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
                            <label for="profesor" class="form-label">Profesor</label>
                            <select class="form-select" id="profesor" name="profesor">
                                <option value="">Todos los profesores</option>
                                <option value="1" <?php echo (isset($_GET['profesor']) && $_GET['profesor'] == '1') ? 'selected' : ''; ?>>María González</option>
                                <option value="2" <?php echo (isset($_GET['profesor']) && $_GET['profesor'] == '2') ? 'selected' : ''; ?>>Juan Pérez</option>
                                <option value="3" <?php echo (isset($_GET['profesor']) && $_GET['profesor'] == '3') ? 'selected' : ''; ?>>Ana Martínez</option>
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
    
    <!-- Resultados del reporte -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Carga Académica de Profesores</h5>
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
                                    <th>Profesor</th>
                                    <th>Materia</th>
                                    <th>Grupo</th>
                                    <th>Horas Semanales</th>
                                    <th>Total Estudiantes</th>
                                    <th>Promedio Grupal</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>María González</td>
                                    <td>Matemáticas</td>
                                    <td>1° A Primaria</td>
                                    <td>10</td>
                                    <td>25</td>
                                    <td>8.7</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                </tr>
                                <tr>
                                    <td>María González</td>
                                    <td>Matemáticas</td>
                                    <td>1° B Primaria</td>
                                    <td>10</td>
                                    <td>23</td>
                                    <td>8.4</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                </tr>
                                <tr>
                                    <td>Juan Pérez</td>
                                    <td>Ciencias</td>
                                    <td>1° A Primaria</td>
                                    <td>8</td>
                                    <td>25</td>
                                    <td>8.7</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                </tr>
                                <tr>
                                    <td>Juan Pérez</td>
                                    <td>Ciencias</td>
                                    <td>2° A Primaria</td>
                                    <td>8</td>
                                    <td>28</td>
                                    <td>8.9</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                </tr>
                                <tr>
                                    <td>Ana Martínez</td>
                                    <td>Historia</td>
                                    <td>1° A Primaria</td>
                                    <td>6</td>
                                    <td>25</td>
                                    <td>8.5</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                </tr>
                                <tr>
                                    <td>Ana Martínez</td>
                                    <td>Historia</td>
                                    <td>2° A Primaria</td>
                                    <td>6</td>
                                    <td>28</td>
                                    <td>8.8</td>
                                    <td><span class="badge bg-success">Activo</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Estadísticas adicionales -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>25</h4>
                                    <p class="mb-0">Total Profesores</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>23</h4>
                                    <p class="mb-0">Activos</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>8.6</h4>
                                    <p class="mb-0">Promedio General</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>42</h4>
                                    <p class="mb-0">Horas Totales</p>
                                </div>
                            </div>
                        </div>
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
    window.open('exportar.php?tipo=pdf&reporte=profesores', '_blank');
}

function exportarExcel() {
    window.open('exportar.php?tipo=excel&reporte=profesores', '_blank');
}

function exportarWord() {
    window.open('exportar.php?tipo=word&reporte=profesores', '_blank');
}

function exportarNotepad() {
    window.open('exportar.php?tipo=notepad&reporte=profesores', '_blank');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
