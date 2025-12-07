/**
 * JavaScript del Sistema de Control Escolar
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Inicializar popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Confirmar eliminación
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || '¿Está seguro de realizar esta acción?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Auto-hide alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Search functionality
    const searchInputs = document.querySelectorAll('[data-search]');
    searchInputs.forEach(input => {
        input.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const targetTable = document.querySelector(this.getAttribute('data-search'));
            
            if (targetTable) {
                const rows = targetTable.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
        });
    });

    // Auto-generate matrícula
    const matriculaInput = document.getElementById('matricula');
    if (matriculaInput && !matriculaInput.value) {
        const prefix = 'EST';
        const timestamp = Date.now().toString().slice(-6);
        matriculaInput.value = prefix + timestamp;
    }

    // Format phone numbers
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length >= 10) {
                value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
            }
            this.value = value;
        });
    });

    // Format currency
    const currencyInputs = document.querySelectorAll('input[data-currency]');
    currencyInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^\d.]/g, '');
            if (value) {
                value = parseFloat(value).toFixed(2);
            }
            this.value = value;
        });
    });

    // Date picker enhancements
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Set max date to today for birth dates
        if (input.name === 'fecha_nacimiento') {
            input.max = new Date().toISOString().split('T')[0];
        }
        
        // Set min date to today for future dates
        if (input.name === 'fecha_ingreso') {
            input.min = new Date().toISOString().split('T')[0];
        }
    });

    // Loading states for forms
    const submitButtons = document.querySelectorAll('button[type="submit"]');
    submitButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            if (form && form.checkValidity()) {
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Procesando...';
                this.disabled = true;
                
                // Restaurar el botón después de 10 segundos si no hay respuesta
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 10000);
            }
        });
    });

    // Table row selection
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('input[name="selected[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Bulk actions
    const bulkActionForm = document.getElementById('bulkActionForm');
    if (bulkActionForm) {
        const bulkActionSelect = document.getElementById('bulkAction');
        const bulkActionButton = document.getElementById('bulkActionButton');
        
        if (bulkActionSelect && bulkActionButton) {
            bulkActionButton.addEventListener('click', function() {
                const selectedCheckboxes = document.querySelectorAll('input[name="selected[]"]:checked');
                const action = bulkActionSelect.value;
                
                if (selectedCheckboxes.length === 0) {
                    alert('Por favor seleccione al menos un elemento');
                    return;
                }
                
                if (!action) {
                    alert('Por favor seleccione una acción');
                    return;
                }
                
                if (confirm(`¿Está seguro de ${action.toLowerCase()} los elementos seleccionados?`)) {
                    bulkActionForm.submit();
                }
            });
        }
    }

    // Print functionality
    const printButtons = document.querySelectorAll('[data-print]');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-print');
            const element = document.querySelector(target);
            if (element) {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Imprimir - ${document.title}</title>
                            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                        </head>
                        <body>
                            ${element.outerHTML}
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.print();
            }
        });
    });

    // Export functionality
    const exportButtons = document.querySelectorAll('[data-export]');
    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const format = this.getAttribute('data-export');
            const table = document.querySelector('table');
            
            if (table) {
                if (format === 'csv') {
                    exportToCSV(table);
                } else if (format === 'excel') {
                    exportToExcel(table);
                }
            }
        });
    });
});

// Función para exportar a CSV
function exportToCSV(table) {
    const rows = Array.from(table.querySelectorAll('tr'));
    const csvContent = rows.map(row => {
        const cells = Array.from(row.querySelectorAll('th, td'));
        return cells.map(cell => `"${cell.textContent.trim()}"`).join(',');
    }).join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'datos.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Función para exportar a Excel (simplificada)
function exportToExcel(table) {
    const rows = Array.from(table.querySelectorAll('tr'));
    let excelContent = '<table>';
    rows.forEach(row => {
        excelContent += '<tr>';
        const cells = Array.from(row.querySelectorAll('th, td'));
        cells.forEach(cell => {
            excelContent += `<td>${cell.textContent.trim()}</td>`;
        });
        excelContent += '</tr>';
    });
    excelContent += '</table>';
    
    const blob = new Blob([excelContent], { type: 'application/vnd.ms-excel' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'datos.xls';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Función para mostrar notificaciones
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(notification);
        bsAlert.close();
    }, 5000);
}

// Función para confirmar acciones
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// Función para cargar contenido dinámico
function loadContent(url, container) {
    const targetContainer = document.querySelector(container);
    if (targetContainer) {
        targetContainer.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
        
        fetch(url)
            .then(response => response.text())
            .then(data => {
                targetContainer.innerHTML = data;
            })
            .catch(error => {
                targetContainer.innerHTML = '<div class="alert alert-danger">Error al cargar el contenido</div>';
                console.error('Error:', error);
            });
    }
}

