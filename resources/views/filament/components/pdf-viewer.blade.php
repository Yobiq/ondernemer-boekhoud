<div class="w-full h-[800px] border rounded-lg overflow-hidden bg-gray-100">
    @if(str_ends_with($filename, '.pdf'))
        <iframe 
            src="{{ $url }}" 
            class="w-full h-full"
            frameborder="0"
        ></iframe>
    @else
        <div class="flex items-center justify-center h-full">
            <div class="text-center">
                <div class="text-6xl mb-4">📄</div>
                <p class="text-gray-600 mb-4">{{ $filename }}</p>
                <a 
                    href="{{ $url }}" 
                    target="_blank"
                    class="text-primary-600 hover:underline"
                >
                    Download bestand
                </a>
            </div>
        </div>
    @endif
</div>

