<?php
/**
 * Reportes de Estudiantes
 * Sistema de Control Escolar
 */

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn()) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$page_title = 'Reportes de Estudiantes';
include __DIR__ . '/../../includes/navbar.php';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>
                    <i class="fas fa-user-graduate me-2"></i>Reportes de Estudiantes
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
                            <label for="periodo" class="form-label">Período</label>
                            <select class="form-select" id="periodo" name="periodo">
                                <option value="">Todos los períodos</option>
                                <option value="1" <?php echo (isset($_GET['periodo']) && $_GET['periodo'] == '1') ? 'selected' : ''; ?>>1° Trimestre</option>
                                <option value="2" <?php echo (isset($_GET['periodo']) && $_GET['periodo'] == '2') ? 'selected' : ''; ?>>2° Trimestre</option>
                                <option value="3" <?php echo (isset($_GET['periodo']) && $_GET['periodo'] == '3') ? 'selected' : ''; ?>>3° Trimestre</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                            <select class="form-select" id="tipo_reporte" name="tipo_reporte">
                                <option value="">Seleccionar tipo</option>
                                <option value="calificaciones" <?php echo (isset($_GET['tipo_reporte']) && $_GET['tipo_reporte'] == 'calificaciones') ? 'selected' : ''; ?>>Calificaciones</option>
                                <option value="asistencia" <?php echo (isset($_GET['tipo_reporte']) && $_GET['tipo_reporte'] == 'asistencia') ? 'selected' : ''; ?>>Asistencia</option>
                                <option value="comportamiento" <?php echo (isset($_GET['tipo_reporte']) && $_GET['tipo_reporte'] == 'comportamiento') ? 'selected' : ''; ?>>Comportamiento</option>
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
                    <h5 class="card-title mb-0">Reporte de Calificaciones - 1° A Primaria</h5>
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
                                    <th rowspan="2">Estudiante</th>
                                    <th colspan="3" class="text-center">Matemáticas</th>
                                    <th colspan="3" class="text-center">Español</th>
                                    <th colspan="3" class="text-center">Ciencias</th>
                                    <th rowspan="2">Promedio</th>
                                </tr>
                                <tr>
                                    <th>P1</th>
                                    <th>P2</th>
                                    <th>P3</th>
                                    <th>P1</th>
                                    <th>P2</th>
                                    <th>P3</th>
                                    <th>P1</th>
                                    <th>P2</th>
                                    <th>P3</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Ana García López</td>
                                    <td>9.5</td>
                                    <td>8.8</td>
                                    <td>9.2</td>
                                    <td>9.0</td>
                                    <td>8.5</td>
                                    <td>9.1</td>
                                    <td>8.7</td>
                                    <td>9.3</td>
                                    <td>8.9</td>
                                    <td><strong>9.0</strong></td>
                                </tr>
                                <tr>
                                    <td>Carlos Méndez Ruiz</td>
                                    <td>8.2</td>
                                    <td>8.5</td>
                                    <td>8.8</td>
                                    <td>8.0</td>
                                    <td>8.3</td>
                                    <td>8.6</td>
                                    <td>8.1</td>
                                    <td>8.4</td>
                                    <td>8.7</td>
                                    <td><strong>8.4</strong></td>
                                </tr>
                                <tr>
                                    <td>María Fernández Torres</td>
                                    <td>9.8</td>
                                    <td>9.5</td>
                                    <td>9.7</td>
                                    <td>9.6</td>
                                    <td>9.3</td>
                                    <td>9.5</td>
                                    <td>9.4</td>
                                    <td>9.6</td>
                                    <td>9.8</td>
                                    <td><strong>9.6</strong></td>
                                </tr>
                                <tr>
                                    <td>Luis Rodríguez Pérez</td>
                                    <td>7.5</td>
                                    <td>7.8</td>
                                    <td>8.0</td>
                                    <td>7.2</td>
                                    <td>7.5</td>
                                    <td>7.8</td>
                                    <td>7.0</td>
                                    <td>7.3</td>
                                    <td>7.6</td>
                                    <td><strong>7.5</strong></td>
                                </tr>
                                <tr>
                                    <td>Sofía Hernández Jiménez</td>
                                    <td>9.0</td>
                                    <td>9.2</td>
                                    <td>9.4</td>
                                    <td>8.8</td>
                                    <td>9.0</td>
                                    <td>9.2</td>
                                    <td>8.9</td>
                                    <td>9.1</td>
                                    <td>9.3</td>
                                    <td><strong>9.1</strong></td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td><strong>PROMEDIO GRUPAL</strong></td>
                                    <td><strong>8.8</strong></td>
                                    <td><strong>8.8</strong></td>
                                    <td><strong>9.0</strong></td>
                                    <td><strong>8.5</strong></td>
                                    <td><strong>8.5</strong></td>
                                    <td><strong>8.8</strong></td>
                                    <td><strong>8.4</strong></td>
                                    <td><strong>8.7</strong></td>
                                    <td><strong>8.8</strong></td>
                                    <td><strong>8.7</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Estadísticas adicionales -->
                    <div class="row mt-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>25</h4>
                                    <p class="mb-0">Total Estudiantes</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>20</h4>
                                    <p class="mb-0">Aprobados</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>3</h4>
                                    <p class="mb-0">En Riesgo</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h4>2</h4>
                                    <p class="mb-0">Reprobados</p>
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
    window.open('exportar.php?tipo=pdf&reporte=estudiantes', '_blank');
}

function exportarExcel() {
    window.open('exportar.php?tipo=excel&reporte=estudiantes', '_blank');
}

function exportarWord() {
    window.open('exportar.php?tipo=word&reporte=estudiantes', '_blank');
}

function exportarNotepad() {
    window.open('exportar.php?tipo=notepad&reporte=estudiantes', '_blank');
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
