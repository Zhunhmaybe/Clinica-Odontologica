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

    // 1. Pintar en Pantalla con soporte de figuras vectoriales
    pintarDienteEnPantalla(dienteSeleccionado, caras, color, tratamiento, estado);

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

function pintarDienteEnPantalla(numero, caras, color, tratamiento, estado, movilidad, recesion) {
    const svg = document.querySelector(`svg[data-pieza="${numero}"]`);
    if (!svg) return;

    const container = svg.closest('.diente-container');
    const simboloLayer = svg.querySelector('.simbolo-layer');
    const indMovRec = container ? container.querySelector('.indicador-movilidad-recesion') : null;

    // Resetear caras a blanco base
    svg.querySelectorAll('.vestibular, .lingual, .distal, .mesial, .oclusal, .palatina, .top, .bottom, .left, .right, .center').forEach(p => {
        p.style.fill = '#ffffff';
        p.style.stroke = '#222222';
    });

    if (simboloLayer) simboloLayer.innerHTML = '';
    if (indMovRec) {
        indMovRec.textContent = '';
        indMovRec.title = '';
    }

    if (!tratamiento || tratamiento === 'Sano') return;

    const t = tratamiento.toLowerCase();

    // Símbolos vectoriales MSP
    if (t.includes('extracción') || t.includes('extraccion')) {
        if (simboloLayer) {
            simboloLayer.innerHTML = `
                <line x1="5" y1="5" x2="45" y2="45" stroke="#dc3545" stroke-width="5" stroke-linecap="round"/>
                <line x1="45" y1="5" x2="5" y2="45" stroke="#dc3545" stroke-width="5" stroke-linecap="round"/>
            `;
        }
    } else if (t.includes('ausente') || t.includes('pérdida') || t.includes('perdida')) {
        if (simboloLayer) {
            if (t.includes('caries')) {
                simboloLayer.innerHTML = `
                    <circle cx="25" cy="25" r="21" fill="none" stroke="#0d6efd" stroke-width="3.5"/>
                    <line x1="10" y1="10" x2="40" y2="40" stroke="#0d6efd" stroke-width="3.5" stroke-linecap="round"/>
                    <line x1="40" y1="10" x2="10" y2="40" stroke="#0d6efd" stroke-width="3.5" stroke-linecap="round"/>
                `;
            } else {
                simboloLayer.innerHTML = `
                    <circle cx="25" cy="25" r="21" fill="none" stroke="#1e293b" stroke-width="3.5"/>
                    <line x1="6" y1="20" x2="44" y2="20" stroke="#1e293b" stroke-width="3.5" stroke-linecap="round"/>
                    <line x1="6" y1="30" x2="44" y2="30" stroke="#1e293b" stroke-width="3.5" stroke-linecap="round"/>
                `;
            }
        }
    } else if (t.includes('sellante')) {
        if (simboloLayer) {
            const cSell = (estado === 'malo') ? '#dc3545' : '#0d6efd';
            simboloLayer.innerHTML = `
                <line x1="25" y1="13" x2="25" y2="37" stroke="${cSell}" stroke-width="3.5" stroke-linecap="round"/>
                <line x1="13" y1="25" x2="37" y2="25" stroke="${cSell}" stroke-width="3.5" stroke-linecap="round"/>
                <line x1="16.5" y1="16.5" x2="33.5" y2="33.5" stroke="${cSell}" stroke-width="3.5" stroke-linecap="round"/>
                <line x1="33.5" y1="16.5" x2="16.5" y2="33.5" stroke="${cSell}" stroke-width="3.5" stroke-linecap="round"/>
            `;
        }
    } else if (t.includes('conducto') || t.includes('endodoncia') || t.includes('pulpotomía') || t.includes('pulpotomia')) {
        if (simboloLayer) {
            if (estado === 'malo') {
                simboloLayer.innerHTML = `<polygon points="25,6 44,43 6,43" fill="none" stroke="#dc3545" stroke-width="4" stroke-linejoin="round"/>`;
            } else {
                simboloLayer.innerHTML = `<polygon points="25,6 44,43 6,43" fill="#0d6efd" stroke="#0d6efd" stroke-width="2" stroke-linejoin="round"/>`;
            }
        }
    } else if (t.includes('corona')) {
        if (simboloLayer) {
            simboloLayer.innerHTML = `<rect x="3" y="3" width="44" height="44" rx="4" fill="none" stroke="${color || '#0d6efd'}" stroke-width="4"/>`;
        }
    } else if (t.includes('fija')) {
        if (simboloLayer) {
            simboloLayer.innerHTML = `
                <line x1="0" y1="25" x2="50" y2="25" stroke="${color || '#0d6efd'}" stroke-width="6"/>
                <circle cx="25" cy="25" r="9" fill="${color || '#0d6efd'}"/>
            `;
        }
    } else if (t.includes('removible')) {
        if (simboloLayer) {
            simboloLayer.innerHTML = `
                <line x1="4" y1="25" x2="46" y2="25" stroke="${color || '#0d6efd'}" stroke-width="4.5" stroke-dasharray="6,4" stroke-linecap="round"/>
                <path d="M 6,14 C 2,19 2,31 6,36" fill="none" stroke="${color || '#0d6efd'}" stroke-width="3" stroke-linecap="round"/>
                <path d="M 44,14 C 48,19 48,31 44,36" fill="none" stroke="${color || '#0d6efd'}" stroke-width="3" stroke-linecap="round"/>
            `;
        }
    } else {
        // Caries, Resina, Amalgama, Ionómero, etc. (se pintan las caras seleccionadas)
        if (Array.isArray(caras)) {
            caras.forEach(cara => {
                let selector = `.${cara}`;
                if (cara === 'palatina' || cara === 'lingual') selector = '.palatina, .lingual, .bottom';
                if (cara === 'vestibular') selector = '.vestibular, .top';
                if (cara === 'mesial') selector = '.mesial, .left';
                if (cara === 'distal') selector = '.distal, .right';
                if (cara === 'oclusal') selector = '.oclusal, .center';

                svg.querySelectorAll(selector).forEach(parte => {
                    parte.style.fill = color;
                });
            });
        }
    }

    if (indMovRec && (movilidad || recesion)) {
        let partes = [];
        if (movilidad) partes.push(`M${movilidad}`);
        if (recesion) partes.push(`R${recesion}`);
        indMovRec.textContent = partes.join(' ');
        indMovRec.title = `Movilidad: Grado ${movilidad || 0}, Recesión: ${recesion || 0}mm`;
    }
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
                        pintarDienteEnPantalla(
                            item.numero_pieza,
                            detalle.caras,
                            detalle.color,
                            detalle.tratamiento,
                            detalle.estado,
                            detalle.movilidad,
                            detalle.recesion
                        );
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
        document.querySelectorAll('.simbolo-layer').forEach(layer => layer.innerHTML = '');
        document.querySelectorAll('.indicador-movilidad-recesion').forEach(ind => {
            ind.textContent = '';
            ind.title = '';
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
