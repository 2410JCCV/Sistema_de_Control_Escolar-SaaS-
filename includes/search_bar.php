<!-- Barra de Búsqueda Global -->
<div class="search-container mb-4">
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="position-relative">
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="searchGlobal" 
                               placeholder="Buscar estudiantes, profesores, materias, grupos..." 
                               autocomplete="off">
                        <div class="position-absolute top-50 end-0 translate-middle-y pe-3">
                            <i class="fas fa-search text-muted"></i>
                        </div>
                        <!-- Sugerencias -->
                        <div id="sugerencias" class="list-group position-absolute w-100" style="z-index: 1000; display: none;">
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-lg" id="moduloFiltro">
                        <option value="todos">Todos</option>
                        <option value="estudiantes">Estudiantes</option>
                        <option value="profesores">Profesores</option>
                        <option value="materias">Materias</option>
                        <option value="grupos">Grupos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary btn-lg w-100" onclick="realizarBusqueda()">
                        <i class="fas fa-search me-1"></i> Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Resultados -->
<div class="modal fade" id="resultadosModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resultados de Búsqueda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="resultadosBusqueda">
                    <!-- Los resultados se cargarán aquí -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let timeoutId;

// Búsqueda en tiempo real
document.getElementById('searchGlobal').addEventListener('input', function() {
    const termino = this.value.trim();
    
    // Limpiar timeout anterior
    clearTimeout(timeoutId);
    
    if (termino.length >= 2) {
        // Esperar 300ms antes de buscar
        timeoutId = setTimeout(() => {
            obtenerSugerencias(termino);
        }, 300);
    } else {
        ocultarSugerencias();
    }
});

// Ocultar sugerencias al hacer clic fuera
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-container')) {
        ocultarSugerencias();
    }
});

function obtenerSugerencias(termino) {
    const modulo = document.getElementById('moduloFiltro').value;
    
    fetch(`ajax/buscar.php?accion=sugerencias&termino=${encodeURIComponent(termino)}&modulo=${modulo}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarSugerencias(data.sugerencias);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function mostrarSugerencias(sugerencias) {
    const container = document.getElementById('sugerencias');
    container.innerHTML = '';
    
    if (sugerencias.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    sugerencias.forEach(sugerencia => {
        const item = document.createElement('a');
        item.href = '#';
        item.className = 'list-group-item list-group-item-action';
        item.textContent = sugerencia;
        item.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('searchGlobal').value = sugerencia.split(' (')[0];
            ocultarSugerencias();
            realizarBusqueda();
        });
        container.appendChild(item);
    });
    
    container.style.display = 'block';
}

function ocultarSugerencias() {
    document.getElementById('sugerencias').style.display = 'none';
}

function realizarBusqueda() {
    const termino = document.getElementById('searchGlobal').value.trim();
    const modulo = document.getElementById('moduloFiltro').value;
    
    if (termino.length < 2) {
        alert('Por favor ingresa al menos 2 caracteres para buscar');
        return;
    }
    
    // Mostrar modal de carga
    const modal = new bootstrap.Modal(document.getElementById('resultadosModal'));
    document.getElementById('resultadosBusqueda').innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Buscando...</span>
            </div>
            <p class="mt-2">Buscando "${termino}"...</p>
        </div>
    `;
    modal.show();
    
    // Realizar búsqueda
    fetch(`ajax/buscar.php?accion=buscar&termino=${encodeURIComponent(termino)}&modulo=${modulo}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarResultados(data.resultados, termino);
            } else {
                mostrarError(data.error || 'Error en la búsqueda');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarError('Error de conexión');
        });
}

function mostrarResultados(resultados, termino) {
    const container = document.getElementById('resultadosBusqueda');
    
    if (resultados.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-search fa-3x mb-3"></i>
                <h5>No se encontraron resultados</h5>
                <p>No hay resultados para "${termino}"</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6>Se encontraron ${resultados.length} resultado(s) para "${termino}"</h6>
            <button class="btn btn-sm btn-outline-primary" onclick="exportarResultados()">
                <i class="fas fa-download me-1"></i> Exportar
            </button>
        </div>
    `;
    
    // Agrupar resultados por módulo
    const agrupados = {};
    resultados.forEach(item => {
        if (!agrupados[item.modulo]) {
            agrupados[item.modulo] = [];
        }
        agrupados[item.modulo].push(item);
    });
    
    // Mostrar resultados agrupados
    Object.keys(agrupados).forEach(modulo => {
        const items = agrupados[modulo];
        const titulo = {
            'estudiantes': 'Estudiantes',
            'profesores': 'Profesores', 
            'materias': 'Materias',
            'grupos': 'Grupos'
        }[modulo] || modulo;
        
        html += `
            <div class="mb-4">
                <h6 class="text-primary">${titulo} (${items.length})</h6>
                <div class="row">
        `;
        
        items.forEach(item => {
            html += `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            ${generarCardItem(item, modulo)}
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function generarCardItem(item, modulo) {
    switch (modulo) {
        case 'estudiantes':
            return `
                <h6 class="card-title">${item.nombre}</h6>
                <p class="card-text">
                    <small class="text-muted">Grupo: ${item.grupo}</small><br>
                    <small class="text-muted">Promedio: ${item.promedio}</small><br>
                    <small class="text-muted">Email: ${item.email}</small>
                </p>
                <a href="modules/estudiantes/ver.php?id=${item.id}" class="btn btn-sm btn-outline-primary">Ver Detalles</a>
            `;
        case 'profesores':
            return `
                <h6 class="card-title">${item.nombre}</h6>
                <p class="card-text">
                    <small class="text-muted">Materia: ${item.materia}</small><br>
                    <small class="text-muted">Grupos: ${item.grupos}</small><br>
                    <small class="text-muted">Email: ${item.email}</small>
                </p>
                <a href="modules/profesores/ver.php?id=${item.id}" class="btn btn-sm btn-outline-primary">Ver Detalles</a>
            `;
        case 'materias':
            return `
                <h6 class="card-title">${item.nombre}</h6>
                <p class="card-text">
                    <small class="text-muted">Código: ${item.codigo}</small><br>
                    <small class="text-muted">Nivel: ${item.nivel}</small><br>
                    <small class="text-muted">Créditos: ${item.creditos}</small>
                </p>
                <a href="modules/materias/ver.php?id=${item.id}" class="btn btn-sm btn-outline-primary">Ver Detalles</a>
            `;
        case 'grupos':
            return `
                <h6 class="card-title">${item.nombre}</h6>
                <p class="card-text">
                    <small class="text-muted">Nivel: ${item.nivel}</small><br>
                    <small class="text-muted">Estudiantes: ${item.estudiantes}</small><br>
                    <small class="text-muted">Profesor: ${item.profesor}</small>
                </p>
                <a href="modules/grupos/ver.php?id=${item.id}" class="btn btn-sm btn-outline-primary">Ver Detalles</a>
            `;
        default:
            return `<p>${JSON.stringify(item)}</p>`;
    }
}

function mostrarError(mensaje) {
    document.getElementById('resultadosBusqueda').innerHTML = `
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ${mensaje}
        </div>
    `;
}

function exportarResultados() {
    const termino = document.getElementById('searchGlobal').value.trim();
    const modulo = document.getElementById('moduloFiltro').value;
    
    // Abrir ventana de exportación
    window.open(`modules/reportes/exportar.php?tipo=excel&reporte=busqueda&termino=${encodeURIComponent(termino)}&modulo=${modulo}`, '_blank');
}
</script>
