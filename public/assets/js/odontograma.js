/**
 * ODONTOGRAMA INTERACTIVO v3.0 (paginaClinica)
 * - URLs dinámicas via data-attributes
 * - Tooltips informativos
 * - Toast notifications (sin alert())
 * - Carga por historia_id específico
 */

// 1. CATÁLOGO DE TRATAMIENTOS
const CATALOGO = {
    'Restauradora': ['Caries', 'Resina', 'Amalgama', 'Ionómero', 'Incrustación', 'Restauración temporal'],
    'Endodoncia': ['Tratamiento de conducto', 'Pulpotomía', 'Apicoformación'],
    'Cirugía': ['Extracción Indicada', 'Pieza Ausente (Perdida)', 'Cirugía apical'],
    'Prótesis': ['Corona', 'Prótesis Fija', 'Prótesis Removible', 'Perno muñón'],
    'Periodoncia': ['Movilidad', 'Recesión'],
    'Prevención': ['Sellante', 'Profilaxis', 'Flúor', 'Sano']
};

// Estado global
let estadoOdontograma = {};
let dienteSeleccionado = null;

// ============================================================
// INICIALIZACIÓN
// ============================================================

document.addEventListener('DOMContentLoaded', function() {
    console.log("Odontograma v3.0 cargado.");
    cargarDatosExistentes();
    cargarCategoriasEnModal();
    inicializarTooltips();
});

// ============================================================
// TOOLTIPS
// ============================================================

function inicializarTooltips() {
    document.querySelectorAll('.diente-svg').forEach(svg => {
        const pieza = svg.getAttribute('data-pieza');
        svg.addEventListener('mouseenter', function() {
            actualizarTooltipDiente(pieza);
        });
    });
}

function actualizarTooltipDiente(pieza) {
    const svg = document.querySelector(`svg[data-pieza="${pieza}"]`);
    if (!svg) return;

    let tooltipText = `Pieza ${pieza} - Sano`;

    if (estadoOdontograma[pieza] && estadoOdontograma[pieza].tratamientos.length > 0) {
        const ultimo = estadoOdontograma[pieza].tratamientos[estadoOdontograma[pieza].tratamientos.length - 1];
        tooltipText = `Pieza ${pieza} - ${ultimo.tratamiento}`;
        if (ultimo.caras && ultimo.caras.length > 0) {
            tooltipText += ` (${ultimo.caras.join(', ')})`;
        }
    }

    // Actualizar el <title> nativo del SVG
    let titleEl = svg.querySelector('title');
    if (titleEl) {
        titleEl.textContent = tooltipText;
    }
}

// ============================================================
// LÓGICA DEL MODAL
// ============================================================

function abrirModalDiente(numeroDiente) {
    dienteSeleccionado = numeroDiente;
    document.getElementById('lbl-diente-seleccionado').innerText = numeroDiente;

    // Resetear formulario
    document.getElementById('form-tratamiento').reset();
    const selectTrat = document.getElementById('select-tratamiento');
    selectTrat.innerHTML = '<option value="">Seleccione categoría primero...</option>';
    selectTrat.disabled = true;

    // Abrir Modal
    const modalEl = document.getElementById('modalTratamiento');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function cargarCategoriasEnModal() {
    const selectCat = document.getElementById('select-categoria');
    if (!selectCat) return;
    selectCat.innerHTML = '<option value="">Seleccione...</option>';
    for (const cat in CATALOGO) {
        let option = document.createElement('option');
        option.value = cat;
        option.innerText = cat;
        selectCat.appendChild(option);
    }
}

function cargarTratamientos() {
    const cat = document.getElementById('select-categoria').value;
    const selectTrat = document.getElementById('select-tratamiento');
    selectTrat.innerHTML = '';

    if (cat && CATALOGO[cat]) {
        selectTrat.disabled = false;
        CATALOGO[cat].forEach(trat => {
            let option = document.createElement('option');
            option.value = trat;
            option.innerText = trat;
            selectTrat.appendChild(option);
        });
    } else {
        selectTrat.innerHTML = '<option value="">Seleccione categoría primero...</option>';
        selectTrat.disabled = true;
    }
}

// ============================================================
// APLICAR TRATAMIENTO
// ============================================================

function aplicarTratamiento() {
    const categoria = document.getElementById('select-categoria').value;
    const tratamiento = document.getElementById('select-tratamiento').value;
    const observacion = document.getElementById('txt-observacion') ? document.getElementById('txt-observacion').value : '';

    const radioEstado = document.querySelector('input[name="estado_tratamiento"]:checked');
    const estado = radioEstado ? radioEstado.value : 'malo';

    let caras = [];
    document.querySelectorAll('.cara-checkbox:checked').forEach(chk => caras.push(chk.value));

    if (!tratamiento) {
        mostrarToast('Seleccione un tratamiento.', 'error');
        return;
    }

    // Definir Color
    let color = (estado === 'malo') ? '#dc3545' : '#0d6efd';

    // Casos Especiales
    if (tratamiento.includes('Ausente') || tratamiento.includes('Extracción')) {
        color = '#333333';
        caras = ['vestibular', 'lingual', 'distal', 'mesial', 'oclusal', 'palatina', 'center', 'top', 'bottom', 'left', 'right'];
    } else if (tratamiento === 'Sano') {
        color = '#ffffff';
        caras = ['vestibular', 'lingual', 'distal', 'mesial', 'oclusal', 'palatina', 'center', 'top', 'bottom', 'left', 'right'];
    } else if (caras.length === 0) {
        caras = ['oclusal', 'center'];
    }

    // 1. Pintar en Pantalla
    pintarDienteEnPantalla(dienteSeleccionado, caras, color);

    // 2. Guardar en Memoria
    if (!estadoOdontograma[dienteSeleccionado]) {
        estadoOdontograma[dienteSeleccionado] = { tratamientos: [] };
    }

    estadoOdontograma[dienteSeleccionado].tratamientos.push({
        categoria, tratamiento, estado, caras, observacion, color
    });

    actualizarListaLateral();
    actualizarTooltipDiente(dienteSeleccionado);

    // 3. Cerrar Modal
    bootstrap.Modal.getInstance(document.getElementById('modalTratamiento')).hide();

    mostrarToast(`Tratamiento aplicado a pieza ${dienteSeleccionado}`, 'success');
}

function pintarDienteEnPantalla(numero, caras, color) {
    const svg = document.querySelector(`svg[data-pieza="${numero}"]`);
    if (!svg) return;

    caras.forEach(cara => {
        let selector = `.${cara}`;
        if (cara === 'palatina' || cara === 'lingual') selector = '.palatina, .lingual, .bottom';
        if (cara === 'vestibular') selector = '.vestibular, .top';
        if (cara === 'mesial') selector = '.mesial, .left';
        if (cara === 'distal') selector = '.distal, .right';
        if (cara === 'oclusal') selector = '.oclusal, .center';

        svg.querySelectorAll(selector).forEach(parte => {
            parte.style.fill = color;
            parte.style.stroke = 'black';
        });
    });
}

function actualizarListaLateral() {
    const lista = document.getElementById('lista-tratamientos');
    if (!lista) return;
    lista.innerHTML = '';

    let count = 0;
    for (const [numero, data] of Object.entries(estadoOdontograma)) {
        if (data.tratamientos.length > 0) {
            const t = data.tratamientos[data.tratamientos.length - 1];
            if (t.color === '#ffffff') continue;

            count++;
            let badgeClass = (t.estado === 'malo') ? 'bg-danger' : 'bg-primary';
            if (t.color === '#333333') badgeClass = 'bg-dark';

            lista.innerHTML += `
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1 fw-bold">Pieza ${numero}</h6>
                        <span class="badge ${badgeClass}">${t.estado === 'malo' ? 'Patología' : 'Realizado'}</span>
                    </div>
                    <p class="mb-1 small fw-bold">${t.tratamiento}</p>
                    <small class="text-muted">Caras: ${t.caras.join(', ') || 'General'}</small>
                </div>`;
        }
    }

    if (count === 0) {
        lista.innerHTML = `<div class="text-center p-4 text-muted">
            <i class="fas fa-info-circle mb-2"></i><br>
            Haga clic en un diente para agregar un diagnóstico o tratamiento.
        </div>`;
    }
}

// ============================================================
// GUARDAR EN BD
// ============================================================

function guardarOdontograma() {
    const container = document.querySelector('[data-historia-id]');
    if (!container) {
        mostrarToast('Error: No se encuentra el ID de la historia.', 'error');
        return;
    }

    const saveUrl = container.dataset.saveUrl;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    let dataToSend = [];

    for (const [numero, data] of Object.entries(estadoOdontograma)) {
        if (data.tratamientos.length > 0) {
            let ultimo = data.tratamientos[data.tratamientos.length - 1];
            dataToSend.push({
                numero_pieza: numero,
                estado: (ultimo.tratamiento === 'Sano') ? 'sano' : ultimo.tratamiento,
                observaciones: JSON.stringify(ultimo)
            });
        }
    }

    const getVal = (id) => {
        const el = document.getElementById(id);
        return (el && el.value) ? el.value : "0";
    };

    const indices = {
        cpo_cariados: getVal('cpo_cariados'),
        cpo_perdidos: getVal('cpo_perdidos'),
        cpo_obturados: getVal('cpo_obturados'),
        ceo_cariados: getVal('ceo_cariados'),
        ceo_extraccion: getVal('ceo_extraccion'),
        ceo_obturados: getVal('ceo_obturados'),
        placa_bacteriana: getVal('placa_bacteriana'),
        calculo_dental: getVal('calculo_dental'),
        gingivitis: getVal('gingivitis'),
        nivel_fluorosis: getVal('nivel_fluorosis'),
        tipo_oclusion: getVal('tipo_oclusion')
    };

    fetch(saveUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ odontograma: dataToSend, indices: indices })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            mostrarToast('¡Odontograma guardado correctamente!', 'success');
        } else {
            mostrarToast('Error del servidor: ' + data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        mostrarToast('Error de conexión. Revisa la consola (F12) para detalles.', 'error');
    });
}

// ============================================================
// CARGAR DATOS EXISTENTES
// ============================================================

function cargarDatosExistentes() {
    const container = document.querySelector('[data-historia-id]');
    if (!container) return;

    const loadUrl = container.dataset.loadUrl;
    if (!loadUrl) return;

    fetch(loadUrl)
    .then(res => res.json())
    .then(data => {
        if (Array.isArray(data)) {
            data.forEach(item => {
                try {
                    if (item.observaciones && item.observaciones.startsWith('{')) {
                        let detalle = JSON.parse(item.observaciones);
                        if (!estadoOdontograma[item.numero_pieza]) estadoOdontograma[item.numero_pieza] = { tratamientos: [] };
                        estadoOdontograma[item.numero_pieza].tratamientos.push(detalle);
                        pintarDienteEnPantalla(item.numero_pieza, detalle.caras, detalle.color);
                        actualizarTooltipDiente(item.numero_pieza);
                    }
                } catch(e) {
                    console.warn('Error parsing odontogram data for piece:', item.numero_pieza, e);
                }
            });
            actualizarListaLateral();
        }
    })
    .catch(err => {
        console.warn('No se pudieron cargar datos del odontograma:', err);
    });
}

// ============================================================
// RESETEAR
// ============================================================

function resetearOdontograma() {
    if (confirm('¿Borrar visualmente todo el odontograma?')) {
        estadoOdontograma = {};
        actualizarListaLateral();
        document.querySelectorAll('svg polygon, svg rect, svg path, svg circle').forEach(el => {
            if (!el.closest('defs')) { // No resetear clipPath defs
                el.style.fill = 'white';
                el.style.stroke = 'black';
            }
        });
        // Reset tooltips
        document.querySelectorAll('.diente-svg title').forEach(t => {
            const svg = t.closest('.diente-svg');
            const pieza = svg ? svg.getAttribute('data-pieza') : '';
            t.textContent = `Pieza ${pieza} - Sano`;
        });
        mostrarToast('Odontograma limpiado visualmente.', 'success');
    }
}

// ============================================================
// TOAST NOTIFICATIONS
// ============================================================

function mostrarToast(mensaje, tipo) {
    const container = document.getElementById('toast-container');
    if (!container) {
        // Fallback si no hay container
        alert(mensaje);
        return;
    }

    const toast = document.createElement('div');
    toast.className = `hc-toast ${tipo}`;
    toast.innerHTML = `<i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${mensaje}`;

    container.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.4s ease';
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

// Hacer funciones globales para que el HTML las vea
window.abrirModalDiente = abrirModalDiente;
window.cargarCategoriasEnModal = cargarCategoriasEnModal;
window.cargarTratamientos = cargarTratamientos;
window.aplicarTratamiento = aplicarTratamiento;
window.guardarOdontograma = guardarOdontograma;
window.resetearOdontograma = resetearOdontograma;
