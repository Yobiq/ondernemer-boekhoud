<div class="flex justify-center mt-8 mb-4">
    <x-filament::button
        wire:click="submit"
        size="xl"
        icon="heroicon-o-check-circle"
        class="invoice-submit-wizard-btn"
    >
        <span class="flex items-center gap-3 text-lg font-bold">
            <span>Factuur Aanmaken</span>
        </span>
    </x-filament::button>
</div>

<style>
    .invoice-submit-wizard-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
        border: none !important;
        box-shadow: 0 4px 12px -4px rgba(59, 130, 246, 0.4) !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        border-radius: 0.75rem !important;
        padding: 1rem 2.5rem !important;
    }

    .invoice-submit-wizard-btn:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px -4px rgba(59, 130, 246, 0.5) !important;
    }

    .invoice-submit-wizard-btn:active {
        transform: translateY(0) !important;
    }

    .dark .invoice-submit-wizard-btn {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
    }
</style>

