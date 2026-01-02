@php
    $fileExtension = strtolower(pathinfo($document->original_filename, PATHINFO_EXTENSION));
    $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    $isPdf = $fileExtension === 'pdf';
@endphp

<div class="document-viewer-container">
    @if($isImage)
        <div class="image-viewer">
            <img 
                src="{{ route('documents.file', $document) }}" 
                alt="{{ $document->original_filename }}" 
                class="max-w-full h-auto rounded-lg shadow-lg"
                style="max-height: 70vh;"
            />
        </div>
    @elseif($isPdf)
        <div class="pdf-viewer">
            <iframe 
                src="{{ route('documents.file', $document) }}#toolbar=1&navpanes=1&scrollbar=1" 
                class="w-full rounded-lg shadow-lg"
                style="height: 70vh; border: 1px solid #e5e7eb;"
                frameborder="0"
                title="PDF Preview"
            ></iframe>
            <div class="mt-2 flex gap-2">
                <a 
                    href="{{ route('documents.file', $document) }}" 
                    target="_blank" 
                    class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Open in Nieuw Tabblad
                </a>
                <a 
                    href="{{ route('documents.download', $document) }}" 
                    download="{{ $document->original_filename }}"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download
                </a>
            </div>
        </div>
    @else
        <div class="unsupported-viewer p-8 text-center bg-gray-50 dark:bg-gray-900 rounded-lg">
            <div class="text-4xl mb-4">📄</div>
            <h3 class="text-lg font-semibold mb-2">Preview niet beschikbaar</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Dit bestandstype ({{ strtoupper($fileExtension) }}) kan niet worden gepreviewd in de browser.
            </p>
            <a 
                href="{{ route('documents.download', $document) }}" 
                download="{{ $document->original_filename }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Download Bestand
            </a>
        </div>
    @endif
</div>

