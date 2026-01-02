<x-filament-panels::page>
    @php
        $selectedClientId = $this->selectedClientId;
        
        // Get summary statistics
        if (!$selectedClientId) {
            // Client overview stats
            $totalClients = \App\Models\Client::count();
            $clientsWithPending = \App\Models\Client::whereHas('documents', fn($q) => 
                $q->whereIn('status', ['pending', 'ocr_processing', 'review_required'])
            )->count();
            $totalDocuments = \App\Models\Document::count();
            $pendingDocuments = \App\Models\Document::whereIn('status', ['pending', 'ocr_processing', 'review_required'])->count();
            $approvedDocuments = \App\Models\Document::where('status', 'approved')->count();
            $totalAmount = \App\Models\Document::where('status', 'approved')->sum('amount_incl');
            
            // Calculate percentages
            $approvalRate = $totalDocuments > 0 ? round(($approvedDocuments / $totalDocuments) * 100, 1) : 0;
            $pendingRate = $totalDocuments > 0 ? round(($pendingDocuments / $totalDocuments) * 100, 1) : 0;
        } else {
            // Client-specific stats
            $client = \App\Models\Client::findOrFail($selectedClientId);
            $clientDocuments = \App\Models\Document::where('client_id', $selectedClientId);
            $totalDocuments = $clientDocuments->count();
            $pendingDocuments = $clientDocuments->whereIn('status', ['pending', 'ocr_processing', 'review_required'])->count();
            $approvedDocuments = $clientDocuments->where('status', 'approved')->count();
            $totalAmount = $clientDocuments->where('status', 'approved')->sum('amount_incl');
            
            // Calculate percentages
            $approvalRate = $totalDocuments > 0 ? round(($approvedDocuments / $totalDocuments) * 100, 1) : 0;
            $pendingRate = $totalDocuments > 0 ? round(($pendingDocuments / $totalDocuments) * 100, 1) : 0;
            
            // Document type breakdown
            $typeBreakdown = \App\Models\Document::where('client_id', $selectedClientId)
                ->select('document_type', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('document_type')
                ->get()
                ->pluck('count', 'document_type')
                ->toArray();
            
            // Status breakdown
            $statusBreakdown = \App\Models\Document::where('client_id', $selectedClientId)
                ->select('status', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();
        }
    @endphp

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stat-card {
            animation: fadeInUp 0.5s ease-out;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        
        .progress-bar {
            transition: width 1s ease-out;
        }
        
        .type-card {
            transition: all 0.3s ease;
        }
        
        .type-card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        
        .breadcrumb-link {
            transition: color 0.2s ease;
        }
        
        .breadcrumb-link:hover {
            color: rgb(59, 130, 246);
        }
    </style>

    @if(!$selectedClientId)
        <!-- Breadcrumb -->
        <div class="mb-4 flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
            <a href="/admin" class="breadcrumb-link">Dashboard</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white font-medium">Klanten & Documenten</span>
        </div>

        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">👥 Klanten & Documenten</h1>
            <p class="text-gray-600 dark:text-gray-400">Overzicht van alle klanten en hun documenten</p>
        </div>

        <!-- Summary Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Clients Card -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Totaal Klanten</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalClients }}</p>
                        <div class="mt-2 flex items-center text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Actieve klanten
                        </div>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Clients Needing Action Card -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-warning-200 dark:border-warning-800 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Actie Nodig</p>
                        <p class="text-3xl font-bold text-warning-600 dark:text-warning-400">{{ $clientsWithPending }}</p>
                        <div class="mt-2">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-500 dark:text-gray-400">van {{ $totalClients }} klanten</span>
                                <span class="font-medium text-warning-600 dark:text-warning-400">
                                    {{ $totalClients > 0 ? round(($clientsWithPending / $totalClients) * 100, 1) : 0 }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="bg-warning-500 h-1.5 rounded-full progress-bar" 
                                     style="width: {{ $totalClients > 0 ? ($clientsWithPending / $totalClients) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 bg-warning-100 dark:bg-warning-900/20 rounded-lg">
                        <svg class="w-8 h-8 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Documents Card -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Totaal Documenten</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalDocuments }}</p>
                        <div class="mt-2">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-500 dark:text-gray-400">{{ $pendingDocuments }} in behandeling</span>
                                <span class="font-medium text-gray-600 dark:text-gray-400">{{ $pendingRate }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="bg-blue-500 h-1.5 rounded-full progress-bar" style="width: {{ $pendingRate }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Amount Card -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Totaal Bedrag</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">€{{ number_format($totalAmount ?? 0, 2, ',', '.') }}</p>
                        <div class="mt-2 flex items-center text-xs text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $approvedDocuments }} goedgekeurd
                        </div>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Breadcrumb -->
        <div class="mb-4 flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
            <a href="/admin" class="breadcrumb-link">Dashboard</a>
            <span>/</span>
            <a href="{{ static::getUrl() }}" class="breadcrumb-link">Klanten & Documenten</a>
            <span>/</span>
            <span class="text-gray-900 dark:text-white font-medium">{{ $client->name }}</span>
        </div>

        <!-- Client Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 dark:from-blue-700 dark:to-purple-800 rounded-lg shadow-lg p-6 mb-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">{{ $client->name }}</h1>
                    @if($client->company_name)
                        <p class="text-blue-100 text-lg">{{ $client->company_name }}</p>
                    @endif
                    <div class="flex items-center space-x-4 mt-3 text-sm">
                        @if($client->email)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                {{ $client->email }}
                            </div>
                        @endif
                        @if($client->vat_number)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                BTW: {{ $client->vat_number }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold">{{ $totalDocuments }}</div>
                    <div class="text-blue-100 text-sm">Documenten</div>
                </div>
            </div>
        </div>

        <!-- Client-Specific Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Documents Card -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Totaal Documenten</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalDocuments }}</p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Alle documenten
                        </div>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending Documents Card -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-warning-200 dark:border-warning-800 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Actie Nodig</p>
                        <p class="text-3xl font-bold text-warning-600 dark:text-warning-400">{{ $pendingDocuments }}</p>
                        <div class="mt-2">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-500 dark:text-gray-400">van totaal</span>
                                <span class="font-medium text-warning-600 dark:text-warning-400">{{ $pendingRate }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="bg-warning-500 h-1.5 rounded-full progress-bar" style="width: {{ $pendingRate }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 bg-warning-100 dark:bg-warning-900/20 rounded-lg">
                        <svg class="w-8 h-8 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Approved Documents Card -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-green-200 dark:border-green-800 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Goedgekeurd</p>
                        <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $approvedDocuments }}</p>
                        <div class="mt-2">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-500 dark:text-gray-400">van totaal</span>
                                <span class="font-medium text-green-600 dark:text-green-400">{{ $approvalRate }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="bg-green-500 h-1.5 rounded-full progress-bar" style="width: {{ $approvalRate }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Amount Card -->
            <div class="stat-card bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-purple-200 dark:border-purple-800 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Totaal Bedrag</p>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">€{{ number_format($totalAmount ?? 0, 2, ',', '.') }}</p>
                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Goedgekeurde documenten
                        </div>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Type Breakdown -->
        @if(!empty($typeBreakdown))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Document Type Overzicht
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    @foreach([
                        'receipt' => ['label' => '🧾 Bonnetjes', 'color' => 'gray', 'icon' => 'heroicon-o-receipt-percent'],
                        'purchase_invoice' => ['label' => '📄 Inkoopfacturen', 'color' => 'blue', 'icon' => 'heroicon-o-document-text'],
                        'bank_statement' => ['label' => '🏦 Bankafschriften', 'color' => 'green', 'icon' => 'heroicon-o-building-library'],
                        'sales_invoice' => ['label' => '🧑‍💼 Verkoopfacturen', 'color' => 'yellow', 'icon' => 'heroicon-o-currency-euro'],
                        'other' => ['label' => '📁 Overig', 'color' => 'gray', 'icon' => 'heroicon-o-document'],
                    ] as $type => $config)
                        @if(isset($typeBreakdown[$type]) && $typeBreakdown[$type] > 0)
                            <div class="type-card text-center p-4 bg-{{ $config['color'] }}-50 dark:bg-{{ $config['color'] }}-900/20 rounded-lg border border-{{ $config['color'] }}-200 dark:border-{{ $config['color'] }}-800 cursor-pointer">
                                <p class="text-3xl font-bold text-{{ $config['color'] }}-600 dark:text-{{ $config['color'] }}-400">{{ $typeBreakdown[$type] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $config['label'] }}</p>
                                @if($totalDocuments > 0)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ round(($typeBreakdown[$type] / $totalDocuments) * 100, 1) }}%
                                    </p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Status Breakdown -->
        @if(!empty($statusBreakdown))
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Status Overzicht
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    @foreach([
                        'pending' => ['label' => '⏳ In Wachtrij', 'color' => 'gray'],
                        'ocr_processing' => ['label' => '🔄 OCR Bezig', 'color' => 'blue'],
                        'review_required' => ['label' => '👀 Review Nodig', 'color' => 'yellow'],
                        'approved' => ['label' => '✅ Goedgekeurd', 'color' => 'green'],
                        'archived' => ['label' => '📦 Gearchiveerd', 'color' => 'gray'],
                    ] as $status => $config)
                        @if(isset($statusBreakdown[$status]) && $statusBreakdown[$status] > 0)
                            <div class="type-card text-center p-4 bg-{{ $config['color'] }}-50 dark:bg-{{ $config['color'] }}-900/20 rounded-lg border border-{{ $config['color'] }}-200 dark:border-{{ $config['color'] }}-800">
                                <p class="text-3xl font-bold text-{{ $config['color'] }}-600 dark:text-{{ $config['color'] }}-400">{{ $statusBreakdown[$status] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $config['label'] }}</p>
                                @if($totalDocuments > 0)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ round(($statusBreakdown[$status] / $totalDocuments) * 100, 1) }}%
                                    </p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    <!-- Quick Actions Bar -->
    @if($selectedClientId)
        @php
            $pendingCount = \App\Models\Document::where('client_id', $selectedClientId)
                ->whereIn('status', ['pending', 'ocr_processing', 'review_required'])
                ->count();
            $reviewCount = \App\Models\Document::where('client_id', $selectedClientId)
                ->where('status', 'review_required')
                ->count();
        @endphp
        
        @if($pendingCount > 0 || $reviewCount > 0)
            <div class="bg-gradient-to-r from-warning-50 to-warning-100 dark:from-warning-900/20 dark:to-warning-800/20 border border-warning-200 dark:border-warning-800 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        @if($reviewCount > 0)
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">👀</span>
                                <div>
                                    <p class="font-semibold text-warning-900 dark:text-warning-100">{{ $reviewCount }} Document(en) Wachten op Review</p>
                                    <p class="text-sm text-warning-700 dark:text-warning-300">Klaar voor beoordeling</p>
                                </div>
                            </div>
                        @endif
                        @if($pendingCount > $reviewCount)
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">⏳</span>
                                <div>
                                    <p class="font-semibold text-warning-900 dark:text-warning-100">{{ $pendingCount - $reviewCount }} In Verwerking</p>
                                    <p class="text-sm text-warning-700 dark:text-warning-300">OCR bezig</p>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        @if($reviewCount > 0)
                            <a href="{{ \App\Filament\Pages\DocumentReview::getUrl() }}" 
                               class="px-4 py-2 bg-warning-600 text-white rounded-lg hover:bg-warning-700 font-medium text-sm transition-colors">
                                👀 Start Review ({{ $reviewCount }})
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Keyboard Shortcuts Help -->
    <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
        <div class="flex items-start gap-2">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="flex-1">
                <p class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">⌨️ Sneltoetsen</p>
                <div class="text-xs text-blue-700 dark:text-blue-300 space-y-1">
                    @if($selectedClientId)
                        <p><kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-800 rounded border border-blue-300 dark:border-blue-700">B</kbd> = Terug naar Klanten</p>
                        <p><kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-800 rounded border border-blue-300 dark:border-blue-700">W</kbd> = BTW Workflow</p>
                        <p><kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-800 rounded border border-blue-300 dark:border-blue-700">E</kbd> = Export Excel</p>
                    @else
                        <p><kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-800 rounded border border-blue-300 dark:border-blue-700">E</kbd> = Export Excel</p>
                        <p><kbd class="px-1.5 py-0.5 bg-white dark:bg-gray-800 rounded border border-blue-300 dark:border-blue-700">Klik op klant</kbd> = Bekijk documenten</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
