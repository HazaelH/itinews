@props(['revista'])

<div class="revista" data-revista-id="{{ $revista->id }}">
    <div class="revista-img-container">
        <img src="{{ asset('storage/' . $revista->portada) }}" 
             alt="{{ $revista->titulo }}"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <div class="imagen-error" style="display: none;">
            <i class="fas fa-image"></i>
            <span>Imagen no disponible</span>
        </div>
        
        <!-- Botón favoritos -->
        <button class="favorito-btn" data-revista-id="{{ $revista->id }}">
            <i class="far fa-heart"></i>
        </button>
        
        <!-- Badge de estación -->
        <div class="estacion-badge">{{ $revista->estacion }}</div>
    </div>
    <p>{{ $revista->titulo }}</p>
</div>