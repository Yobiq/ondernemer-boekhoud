<x-filament-panels::page>
    <style>
        /* Unified Design System - Same as other pages */
        .profile-page-container {
            --primary-bg: #ffffff;
            --secondary-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-tertiary: #94a3b8;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --accent-purple: #8b5cf6;
            --accent-red: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            padding: 0.75rem;
            background: var(--secondary-bg);
            min-height: 100vh;
            width: 100%;
            max-width: 100%;
        }

        /* Dark Mode Support */
        .dark .profile-page-container {
            --primary-bg: #0f172a;
            --secondary-bg: #1e293b;
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
        }

        @media (min-width: 640px) {
            .profile-page-container {
                padding: 1rem;
            }
        }

        @media (min-width: 1024px) {
            .profile-page-container {
                padding: 1.5rem 2rem;
                max-width: 1200px;
                margin: 0 auto;
            }
        }

        /* Hero Section */
        .profile-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .profile-hero-content {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .profile-avatar {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            border: 2px solid rgba(255, 255, 255, 0.3);
            flex-shrink: 0;
        }

        .profile-hero-text {
            flex: 1;
        }

        .profile-hero-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }

        .profile-hero-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin: 0;
        }

        /* Form Section */
        .profile-form-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }
        
        .dark .profile-form-section {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(51, 65, 85, 0.8);
        }

        @media (min-width: 1024px) {
            .profile-form-section {
                padding: 2rem;
            }
        }

        /* Info Cards */
        .profile-info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        @media (min-width: 640px) {
            .profile-info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .profile-info-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .profile-info-card {
            background: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            border-left: 4px solid var(--accent-blue);
            transition: all 0.2s ease;
        }

        .profile-info-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-left-color: var(--accent-purple);
        }

        .profile-info-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            letter-spacing: 0.05em;
        }

        .profile-info-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            word-break: break-word;
        }

        .profile-info-value.empty {
            color: var(--text-tertiary);
            font-style: italic;
            font-weight: 400;
        }

        /* Save Button */
        .profile-save-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--accent-blue) 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            margin-top: 1.5rem;
        }

        .profile-save-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        .profile-save-btn:active {
            transform: translateY(0);
        }
    </style>

    <div class="profile-page-container">
        {{-- Hero Header --}}
        <div class="profile-hero">
            <div class="profile-hero-content">
                <div class="profile-avatar">
                    @php
                        $user = auth()->user();
                        $initials = strtoupper(substr($user->name ?? 'U', 0, 1));
                    @endphp
                    {{ $initials }}
                </div>
                <div class="profile-hero-text">
                    <h1 class="profile-hero-title">👤 Mijn Profiel</h1>
                    <p class="profile-hero-subtitle">Beheer uw persoonlijke en bedrijfsgegevens</p>
                </div>
            </div>
        </div>

        {{-- Form Section --}}
        <div class="profile-form-section">
            <form wire:submit="submit">
                {{ $this->form }}
                
                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="profile-save-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Profiel Opslaan
                    </button>
                </div>
            </form>

            {{-- Business Info Cards --}}
            @php
                $user = auth()->user();
                $client = $user->client;
            @endphp
            <div class="profile-info-grid">
                <div class="profile-info-card">
                    <div class="profile-info-label">Account Type</div>
                    <div class="profile-info-value">Klant Account</div>
                </div>
                <div class="profile-info-card">
                    <div class="profile-info-label">Lid Sinds</div>
                    <div class="profile-info-value">{{ $user->created_at ? $user->created_at->format('d-m-Y') : 'N/A' }}</div>
                </div>
                @if($client)
                    @if($client->company_name)
                    <div class="profile-info-card">
                        <div class="profile-info-label">Bedrijfsnaam</div>
                        <div class="profile-info-value">{{ $client->company_name }}</div>
                    </div>
                    @endif
                    @if($client->kvk_number)
                    <div class="profile-info-card">
                        <div class="profile-info-label">KVK Nummer</div>
                        <div class="profile-info-value">{{ $client->kvk_number }}</div>
                    </div>
                    @endif
                    @if($client->vat_number)
                    <div class="profile-info-card">
                        <div class="profile-info-label">BTW Nummer</div>
                        <div class="profile-info-value">{{ $client->vat_number }}</div>
                    </div>
                    @endif
                    @if($client->phone)
                    <div class="profile-info-card">
                        <div class="profile-info-label">Telefoon</div>
                        <div class="profile-info-value">{{ $client->phone }}</div>
                    </div>
                    @endif
                    @if($client->city)
                    <div class="profile-info-card">
                        <div class="profile-info-label">Plaats</div>
                        <div class="profile-info-value">{{ $client->city }}</div>
                    </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
