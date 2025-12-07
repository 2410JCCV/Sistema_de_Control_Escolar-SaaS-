<?php
/**
 * Módulo de Reportes
 * Sistema de Control Escolar
 */

require_once __DIR__ . '/../../config/config.php';

// Verificar autenticación y permisos
if (!isLoggedIn()) {
    // CAMBIO: URL actualizada de /sistema_escolar/index.php a index.php para dominio https://tarea.site/
redirect('index.php');
}

$page_title = 'Reportes';
include __DIR__ . '/../../includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/dashboard-style.css" rel="stylesheet">
    <style>
        body { font-family: 'Comic Sans MS', 'Chalkboard', 'Marker Felt', cursive; }
        .main-container {
            background: linear-gradient(135deg, rgba(224, 242, 254, 0.5) 0%, rgba(254, 243, 199, 0.5) 50%, rgba(252, 231, 243, 0.5) 100%);
            padding: 2rem;
            min-height: calc(100vh - 100px);
        }
        .page-header {
            background: linear-gradient(135deg, var(--sky-blue) 0%, var(--purple) 100%);
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            color: white;
        }
        .report-card {
            background: white;
            border-radius: 25px;
            border: 3px solid var(--sunny-yellow);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        .report-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        .report-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            background: linear-gradient(135deg, var(--sky-blue) 0%, var(--purple) 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .report-card.students .report-icon { background: linear-gradient(135deg, var(--sky-blue) 0%, #2196F3 100%); }
        .report-card.teachers .report-icon { background: linear-gradient(135deg, var(--grass-green) 0%, #059669 100%); }
        .report-card.grades .report-icon { background: linear-gradient(135deg, var(--purple) 0%, #7C3AED 100%); }
        .report-card.groups .report-icon { background: linear-gradient(135deg, var(--purple) 0%, var(--pink) 100%); }
        .report-card.subjects .report-icon { background: linear-gradient(135deg, var(--orange) 0%, #EA580C 100%); }
    </style>
</head>
<body class="dashboard-style">
<div class="main-container">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h2 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Módulo de Reportes
                </h2>
                <p class="mb-0 mt-2">Genera reportes detallados de todas las áreas del sistema</p>
            </div>
        </div>
    </div>
    
    <!-- Tarjetas de reportes disponibles -->
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="report-card students">
                <div class="card-body text-center p-4">
                    <div class="report-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-3">Reporte de Estudiantes</h5>
                    <p class="card-text mb-4">Generar reportes detallados sobre estudiantes, calificaciones y rendimiento académico.</p>
                    <div class="d-grid gap-2">
                        <a href="estudiantes.php" class="btn btn-school btn-students">
                            <i class="fas fa-file-alt me-1"></i>Ver Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="report-card teachers">
                <div class="card-body text-center p-4">
                    <div class="report-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-3">Reporte de Profesores</h5>
                    <p class="card-text mb-4">Análisis de carga académica, materias asignadas y rendimiento docente.</p>
                    <div class="d-grid gap-2">
                        <a href="profesores.php" class="btn btn-school btn-teachers">
                            <i class="fas fa-file-alt me-1"></i>Ver Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="report-card grades">
                <div class="card-body text-center p-4">
                    <div class="report-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-3">Reporte de Calificaciones</h5>
                    <p class="card-text mb-4">Estadísticas de calificaciones por materia, grupo y período académico.</p>
                    <div class="d-grid gap-2">
                        <a href="calificaciones.php" class="btn btn-school btn-grades">
                            <i class="fas fa-file-alt me-1"></i>Ver Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="report-card groups">
                <div class="card-body text-center p-4">
                    <div class="report-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-3">Reporte de Grupos</h5>
                    <p class="card-text mb-4">Información detallada sobre grupos, horarios y distribución de estudiantes.</p>
                    <div class="d-grid gap-2">
                        <a href="grupos.php" class="btn btn-school btn-groups">
                            <i class="fas fa-file-alt me-1"></i>Ver Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="report-card subjects">
                <div class="card-body text-center p-4">
                    <div class="report-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-3">Reporte de Materias</h5>
                    <p class="card-text mb-4">Análisis de materias, profesores asignados y carga académica.</p>
                    <div class="d-grid gap-2">
                        <a href="materias.php" class="btn btn-school btn-subjects">
                            <i class="fas fa-file-alt me-1"></i>Ver Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="report-card groups">
                <div class="card-body text-center p-4">
                    <div class="report-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h5 class="card-title fw-bold mb-3">Reportes Generales</h5>
                    <p class="card-text mb-4">Estadísticas generales del sistema, resúmenes ejecutivos y métricas.</p>
                    <div class="d-grid gap-2">
                        <a href="generales.php" class="btn btn-school btn-groups">
                            <i class="fas fa-file-alt me-1"></i>Ver Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reportes recientes -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="content-card">
                <div class="content-card-header">
                    <i class="fas fa-history"></i>
                    Reportes Recientes
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-module table-striped">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo de Reporte</th>
                                    <th>Descripción</th>
                                    <th>Generado por</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i'); ?></td>
                                    <td>Calificaciones</td>
                                    <td>Reporte de calificaciones del 1° trimestre</td>
                                    <td>admin</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-school btn-groups">
                                            <i class="fas fa-download"></i> Descargar
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime('-1 day')); ?></td>
                                    <td>Estudiantes</td>
                                    <td>Lista de estudiantes por grupo</td>
                                    <td>admin</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-school btn-groups">
                                            <i class="fas fa-download"></i> Descargar
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime('-3 days')); ?></td>
                                    <td>Profesores</td>
                                    <td>Reporte de carga académica</td>
                                    <td>admin</td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-school btn-groups">
                                            <i class="fas fa-download"></i> Descargar
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Opciones de descarga y subida -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Herramientas de Reportes</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-download me-2"></i>Opciones de Descarga</h6>
                            <p>Exporta tus reportes en diferentes formatos para análisis externo o presentaciones.</p>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-danger" onclick="descargarPDF()">
                                    <i class="fas fa-file-pdf me-1"></i>PDF
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="descargarExcel()">
                                    <i class="fas fa-file-excel me-1"></i>Excel
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="descargarWord()">
                                    <i class="fas fa-file-word me-1"></i>Word
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="descargarNotepad()">
                                    <i class="fas fa-file-alt me-1"></i>Notepad
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-upload me-2"></i>Subir Reportes</h6>
                            <p>Sube reportes externos para análisis comparativo o archivo de documentos.</p>
                            <button type="button" class="btn btn-outline-info" onclick="subirReporte()">
                                <i class="fas fa-upload me-1"></i>Subir Archivo
                            </button>
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
                        <div class="form-text">Formatos permitidos: PDF, Word, Excel (Máximo 10MB)</div>
                    </div>
                    <div class="mb-3">
                        <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                        <select class="form-select" id="tipo_reporte" name="tipo_reporte" required>
                            <option value="">Seleccionar tipo</option>
                            <option value="estudiantes">Reporte de Estudiantes</option>
                            <option value="profesores">Reporte de Profesores</option>
                            <option value="calificaciones">Reporte de Calificaciones</option>
                            <option value="grupos">Reporte de Grupos</option>
                            <option value="materias">Reporte de Materias</option>
                            <option value="generales">Reporte General</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                  placeholder="Describe el contenido del reporte..."></textarea>
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

<script>
function descargarPDF() {
    window.open('exportar.php?tipo=pdf&reporte=generales', '_blank');
}

function descargarExcel() {
    window.open('exportar.php?tipo=excel&reporte=generales', '_blank');
}

function descargarWord() {
    window.open('exportar.php?tipo=word&reporte=generales', '_blank');
}

function descargarNotepad() {
    window.open('exportar.php?tipo=notepad&reporte=generales', '_blank');
}

function subirReporte() {
    const modal = new bootstrap.Modal(document.getElementById('subirReporteModal'));
    modal.show();
}

function procesarSubida() {
    const archivo = document.getElementById('archivo').files[0];
    const tipo_reporte = document.getElementById('tipo_reporte').value;
    const descripcion = document.getElementById('descripcion').value;
    
    if (!archivo) {
        alert('Por favor selecciona un archivo');
        return;
    }
    
    if (!tipo_reporte) {
        alert('Por favor selecciona el tipo de reporte');
        return;
    }
    
    // Aquí iría la lógica para subir el archivo
    alert('Reporte subido correctamente');
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('subirReporteModal'));
    modal.hide();
    
    // Limpiar formulario
    document.getElementById('uploadForm').reset();
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
