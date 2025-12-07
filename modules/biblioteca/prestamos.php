<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Préstamos de Libros';

try {
    $pdo = conectarDB();
    
    $sql = "SELECT pl.*, 
            l.titulo, l.codigo as codigo_libro,
            CONCAT(e.nombre, ' ', e.apellido_paterno) as estudiante_nombre,
            CONCAT(p.nombre, ' ', p.apellido_paterno) as profesor_nombre
            FROM prestamos_libros pl
            LEFT JOIN libros l ON pl.libro_id = l.id
            LEFT JOIN estudiantes e ON pl.estudiante_id = e.id
            LEFT JOIN profesores p ON pl.profesor_id = p.id
            WHERE pl.estado = 'prestado' OR pl.estado = 'vencido'
            ORDER BY pl.fecha_prestamo DESC";
    $prestamos = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    $libros = $pdo->query("SELECT id, CONCAT(codigo, ' - ', titulo) as nombre FROM libros WHERE estado = 'disponible' OR cantidad_disponible > 0 ORDER BY titulo")->fetchAll(PDO::FETCH_ASSOC);
    $estudiantes = $pdo->query("SELECT id, CONCAT(nombre, ' ', apellido_paterno, ' ', apellido_materno) as nombre_completo FROM estudiantes WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    $profesores = $pdo->query("SELECT id, CONCAT(nombre, ' ', apellido_paterno) as nombre_completo FROM profesores WHERE estado = 'activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $prestamos = [];
    $libros = [];
    $estudiantes = [];
    $profesores = [];
}

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
            background: linear-gradient(135deg, var(--lime) 0%, #65A30D 100%);
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            color: white;
        }
    </style>
</head>
<body class="dashboard-style">
    <div class="main-container">
        <div class="row">
            <div class="col-12">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><i class="fas fa-exchange-alt me-2"></i><?php echo $page_title; ?></h2>
                            <p class="mb-0 mt-2">Gestiona los préstamos de libros</p>
                        </div>
                        <div>
                            <button class="btn btn-light btn-lg" data-bs-toggle="modal" data-bs-target="#modalPrestamo">
                                <i class="fas fa-plus me-2"></i>Nuevo Préstamo
                            </button>
                            <a href="listar.php" class="btn btn-light btn-lg ms-2">
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </a>
                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-list"></i>Préstamos Activos
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($prestamos)): ?>
                        <div class="table-responsive">
                            <table class="table table-module table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Libro</th>
                                        <th>Estudiante/Profesor</th>
                                        <th>Fecha Préstamo</th>
                                        <th>Fecha Devolución</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prestamos as $prestamo): ?>
                                    <tr>
                                        <td><?php echo $prestamo['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($prestamo['titulo']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($prestamo['codigo_libro']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($prestamo['estudiante_nombre'] ?? $prestamo['profesor_nombre'] ?? 'N/A'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($prestamo['fecha_prestamo'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($prestamo['fecha_devolucion_esperada'])); ?></td>
                                        <td>
                                            <span class="badge badge-module bg-<?php 
                                                echo $prestamo['estado'] == 'prestado' ? 'primary' : 
                                                    ($prestamo['estado'] == 'vencido' ? 'danger' : 'success'); 
                                            ?>">
                                                <?php echo ucfirst($prestamo['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-school btn-library" onclick="devolverLibro(<?php echo $prestamo['id']; ?>)">
                                                <i class="fas fa-undo me-1"></i>Devolver
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay préstamos activos</h5>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Préstamo -->
    <div class="modal fade" id="modalPrestamo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Préstamo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="realizar_prestamo.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="libro_id" class="form-label">Libro <span class="text-danger">*</span></label>
                            <select class="form-select" id="libro_id" name="libro_id" required>
                                <option value="">Seleccionar libro</option>
                                <?php foreach ($libros as $lib): ?>
                                <option value="<?php echo $lib['id']; ?>"><?php echo htmlspecialchars($lib['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="estudiante_id" class="form-label">Estudiante</label>
                            <select class="form-select" id="estudiante_id" name="estudiante_id">
                                <option value="">Seleccionar estudiante</option>
                                <?php foreach ($estudiantes as $est): ?>
                                <option value="<?php echo $est['id']; ?>"><?php echo htmlspecialchars($est['nombre_completo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="profesor_id" class="form-label">Profesor</label>
                            <select class="form-select" id="profesor_id" name="profesor_id">
                                <option value="">Seleccionar profesor</option>
                                <?php foreach ($profesores as $prof): ?>
                                <option value="<?php echo $prof['id']; ?>"><?php echo htmlspecialchars($prof['nombre_completo']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_devolucion_esperada" class="form-label">Fecha Devolución <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_devolucion_esperada" name="fecha_devolucion_esperada" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-school btn-library">Realizar Préstamo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function devolverLibro(id) {
            if (confirm('¿Marcar este libro como devuelto?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'devolver_libro.php';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'id';
                input.value = id;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        document.getElementById('estudiante_id').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('profesor_id').disabled = true;
                document.getElementById('profesor_id').value = '';
            } else {
                document.getElementById('profesor_id').disabled = false;
            }
        });
        
        document.getElementById('profesor_id').addEventListener('change', function() {
            if (this.value) {
                document.getElementById('estudiante_id').disabled = true;
                document.getElementById('estudiante_id').value = '';
            } else {
                document.getElementById('estudiante_id').disabled = false;
            }
        });
    </script>
</body>
</html>



