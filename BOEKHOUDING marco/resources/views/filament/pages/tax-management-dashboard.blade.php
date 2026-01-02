<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Widgets --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @foreach ($this->getHeaderWidgets() as $widget)
                @livewire($widget, ['lazy' => true])
            @endforeach
        </div>
        
        {{-- Footer Widgets --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            @foreach ($this->getFooterWidgets() as $widget)
                @livewire($widget, ['lazy' => true])
            @endforeach
        </div>
    </div>
</x-filament-panels::page>

