@if ($paginator->hasPages())
    <nav class="flex flex-wrap items-center justify-between gap-4 text-sm" aria-label="Páginas de proyectos">
        <p class="text-tenue">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</p>
        <div class="flex gap-4">
            @if ($paginator->previousPageUrl())
                <a class="aris-enlace" href="{{ $paginator->previousPageUrl() }}" rel="prev">Anterior</a>
            @endif
            @if ($paginator->nextPageUrl())
                <a class="aris-enlace" href="{{ $paginator->nextPageUrl() }}" rel="next">Siguiente</a>
            @endif
        </div>
    </nav>
@endif
