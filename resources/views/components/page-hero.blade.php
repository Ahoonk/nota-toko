@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'backUrl' => null,
    'backLabel' => 'Kembali',
])

<section {{ $attributes->merge(['class' => 'page-hero p-4 p-lg-5 mb-4']) }}>
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-4">
        <div class="flex-grow-1">
            @if ($eyebrow)
                <span class="badge rounded-pill bg-white text-primary px-3 py-2 mb-3 shadow-sm">{{ $eyebrow }}</span>
            @endif

            <h1 class="display-6 fw-bold mb-2">{{ $title }}</h1>

            @if ($subtitle)
                <p class="hero-meta mb-0">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="d-flex flex-wrap gap-2">
            {{ $actions ?? '' }}

            @if ($backUrl)
                <a href="{{ $backUrl }}" class="btn btn-light btn-lg">
                    <i class="bi bi-arrow-left me-1"></i>{{ $backLabel }}
                </a>
            @endif
        </div>
    </div>

    @if (trim($stats ?? '') !== '')
        <div class="row g-3 mt-4">
            {{ $stats }}
        </div>
    @endif
</section>
