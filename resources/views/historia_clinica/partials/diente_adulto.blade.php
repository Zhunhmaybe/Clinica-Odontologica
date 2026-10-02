<div class="diente-container" onclick="abrirModalDiente({{ $pieza }})">
    <span class="numero-pieza">{{ $pieza }}</span>
    <svg class="diente-svg" data-pieza="{{ $pieza }}" width="40" height="40" viewBox="0 0 50 50">
        <title>Pieza {{ $pieza }} - Sano</title>
        <polygon class="vestibular" points="0,0 50,0 35,15 15,15" fill="white" stroke="black" stroke-width="1"/>
        <polygon class="lingual" points="15,35 35,35 50,50 0,50" fill="white" stroke="black" stroke-width="1"/>
        <polygon class="distal" points="50,0 50,50 35,35 35,15" fill="white" stroke="black" stroke-width="1"/>
        <polygon class="mesial" points="0,0 0,50 15,35 15,15" fill="white" stroke="black" stroke-width="1"/>
        <rect class="oclusal" x="15" y="15" width="20" height="20" fill="white" stroke="black" stroke-width="1"/>
    </svg>
</div>
