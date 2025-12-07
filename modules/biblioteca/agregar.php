<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasPermission('admin')) {
    redirect('index.php');
}

$page_title = 'Agregar Libro';
$errors = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_libro'])) {
    $form_data = [
        'codigo' => trim($_POST['codigo'] ?? ''),
        'titulo' => trim($_POST['titulo'] ?? ''),
        'autor' => trim($_POST['autor'] ?? ''),
        'editorial' => trim($_POST['editorial'] ?? ''),
        'isbn' => trim($_POST['isbn'] ?? ''),
        'categoria' => trim($_POST['categoria'] ?? ''),
        'año_publicacion' => !empty($_POST['año_publicacion']) ? (int)$_POST['año_publicacion'] : null,
        'cantidad_total' => !empty($_POST['cantidad_total']) ? (int)$_POST['cantidad_total'] : 1,
        'ubicacion' => trim($_POST['ubicacion'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'estado' => trim($_POST['estado'] ?? 'disponible')
    ];
    
    if (empty($form_data['codigo'])) $errors[] = "El código es requerido";
    if (empty($form_data['titulo'])) $errors[] = "El título es requerido";
    if (empty($form_data['autor'])) $errors[] = "El autor es requerido";
    if ($form_data['cantidad_total'] < 1) $errors[] = "La cantidad total debe ser al menos 1";
    
    if (empty($errors)) {
        try {
            $pdo = conectarDB();
            
            // Verificar código único
            $check = $pdo->prepare("SELECT COUNT(*) FROM libros WHERE codigo = ?");
            $check->execute([$form_data['codigo']]);
            if ($check->fetchColumn() > 0) {
                $errors[] = "El código ya existe";
            } else {
                $sql = "INSERT INTO libros (codigo, titulo, autor, editorial, isbn, categoria, año_publicacion, cantidad_total, cantidad_disponible, ubicacion, descripcion, estado, fecha_creacion) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([
                    $form_data['codigo'], $form_data['titulo'], $form_data['autor'], 
                    $form_data['editorial'], $form_data['isbn'], $form_data['categoria'],
                    $form_data['año_publicacion'], $form_data['cantidad_total'], $form_data['cantidad_total'],
                    $form_data['ubicacion'], $form_data['descripcion'], $form_data['estado']
                ])) {
                    header('Location: listar.php?success=' . urlencode('Libro agregado exitosamente'));
                    exit();
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Error al guardar: " . $e->getMessage();
        }
    }
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
                            <h2 class="mb-0"><i class="fas fa-plus me-2"></i><?php echo $page_title; ?></h2>
                            <p class="mb-0 mt-2">Agrega un nuevo libro a la biblioteca</p>
                        </div>
                        <a href="listar.php" class="btn btn-light">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-module">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <div class="content-card">
                    <div class="content-card-header">
                        <i class="fas fa-edit"></i>Formulario de Libro
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <input type="hidden" name="agregar_libro" value="1">
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="codigo" name="codigo" 
                                           value="<?php echo htmlspecialchars($form_data['codigo'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="titulo" name="titulo" 
                                           value="<?php echo htmlspecialchars($form_data['titulo'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="autor" class="form-label">Autor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="autor" name="autor" 
                                           value="<?php echo htmlspecialchars($form_data['autor'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="editorial" class="form-label">Editorial</label>
                                    <input type="text" class="form-control" id="editorial" name="editorial" 
                                           value="<?php echo htmlspecialchars($form_data['editorial'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="isbn" class="form-label">ISBN</label>
                                    <input type="text" class="form-control" id="isbn" name="isbn" 
                                           value="<?php echo htmlspecialchars($form_data['isbn'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="categoria" class="form-label">Categoría</label>
                                    <input type="text" class="form-control" id="categoria" name="categoria" 
                                           value="<?php echo htmlspecialchars($form_data['categoria'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="año_publicacion" class="form-label">Año Publicación</label>
                                    <input type="number" class="form-control" id="año_publicacion" name="año_publicacion" 
                                           value="<?php echo htmlspecialchars($form_data['año_publicacion'] ?? ''); ?>" min="1900" max="<?php echo date('Y'); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="cantidad_total" class="form-label">Cantidad Total <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="cantidad_total" name="cantidad_total" 
                                           value="<?php echo htmlspecialchars($form_data['cantidad_total'] ?? '1'); ?>" min="1" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="ubicacion" class="form-label">Ubicación</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" 
                                           value="<?php echo htmlspecialchars($form_data['ubicacion'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado">
                                        <option value="disponible" <?php echo ($form_data['estado'] ?? 'disponible') == 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                                        <option value="prestado" <?php echo ($form_data['estado'] ?? '') == 'prestado' ? 'selected' : ''; ?>>Prestado</option>
                                        <option value="reservado" <?php echo ($form_data['estado'] ?? '') == 'reservado' ? 'selected' : ''; ?>>Reservado</option>
                                        <option value="mantenimiento" <?php echo ($form_data['estado'] ?? '') == 'mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($form_data['descripcion'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="listar.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-school btn-library btn-lg">
                                    <i class="fas fa-save me-2"></i>Guardar Libro
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>



