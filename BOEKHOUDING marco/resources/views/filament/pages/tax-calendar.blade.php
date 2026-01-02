<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            @php
                $overdue = \App\Models\VatPeriod::where('status', 'open')
                    ->orWhere('status', 'voorbereid')
                    ->get()
                    ->filter(function ($period) {
                        $deadline = $period->period_end->copy()->addMonth()->endOfMonth();
                        return now()->diffInDays($deadline, false) < 0;
                    })->count();
                
                $urgent = \App\Models\VatPeriod::where('status', 'open')
                    ->orWhere('status', 'voorbereid')
                    ->get()
                    ->filter(function ($period) {
                        $deadline = $period->period_end->copy()->addMonth()->endOfMonth();
                        $days = now()->diffInDays($deadline, false);
                        return $days >= 0 && $days < 7;
                    })->count();
                
                $soon = \App\Models\VatPeriod::where('status', 'open')
                    ->orWhere('status', 'voorbereid')
                    ->get()
                    ->filter(function ($period) {
                        $deadline = $period->period_end->copy()->addMonth()->endOfMonth();
                        $days = now()->diffInDays($deadline, false);
                        return $days >= 7 && $days < 30;
                    })->count();
                
                $total = \App\Models\VatPeriod::where('status', 'open')
                    ->orWhere('status', 'voorbereid')
                    ->count();
            @endphp
            
            <x-filament::section>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $overdue }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Verlopen</div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $urgent }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Urgent (< 7 dagen)</div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $soon }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Binnenkort (< 30 dagen)</div>
            </x-filament::section>
            
            <x-filament::section>
                <div class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $total }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Totaal Open</div>
            </x-filament::section>
        </div>
        
        {{-- Table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>

