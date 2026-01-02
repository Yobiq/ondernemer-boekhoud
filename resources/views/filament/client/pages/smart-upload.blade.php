<x-filament-panels::page>
    <style>
        /* Modern White Theme - Primary Design */
        :root {
            --primary-bg: #ffffff;
            --secondary-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-tertiary: #94a3b8;
            --accent-blue: #3b82f6;
            --accent-purple: #8b5cf6;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .dark {
            --primary-bg: #0f172a;
            --secondary-bg: #1e293b;
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
        }

        /* Upload Page Container */
        .upload-page-container {
            background: var(--secondary-bg);
            min-height: 100vh;
            padding: 0.75rem;
        }

        @media (min-width: 640px) {
            .upload-page-container {
                padding: 1rem;
            }
        }

        @media (min-width: 1024px) {
            .upload-page-container {
                padding: 1.5rem;
            }
        }

        /* Hero Header */
        .upload-hero {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 640px) {
            .upload-hero {
                margin-bottom: 2rem;
            }
        }

        .upload-icon-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3.5rem;
            height: 3.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 1rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-lg);
        }

        @media (min-width: 640px) {
            .upload-icon-wrapper {
                width: 4rem;
                height: 4rem;
                border-radius: 1.25rem;
            }
        }

        .upload-icon {
            font-size: 2rem;
        }

        @media (min-width: 640px) {
            .upload-icon {
                font-size: 2.5rem;
            }
        }

        .upload-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.5rem 0;
            line-height: 1.3;
        }

        @media (min-width: 640px) {
            .upload-title {
                font-size: 1.75rem;
            }
        }

        @media (min-width: 1024px) {
            .upload-title {
                font-size: 2rem;
            }
        }

        .upload-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.5;
            max-width: 32rem;
            margin: 0 auto;
        }

        @media (min-width: 640px) {
            .upload-subtitle {
                font-size: 0.9375rem;
            }
        }

        /* Wizard Card */
        .wizard-card {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.25rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 1.25rem;
        }

        @media (min-width: 640px) {
            .wizard-card {
            padding: 1.5rem;
                border-radius: 1.25rem;
            }
        }

        @media (min-width: 1024px) {
            .wizard-card {
                padding: 2rem;
            }
        }

        /* Radio Button Styling - Enhanced */
        .fi-fo-radio .fi-fo-field-wrp-label {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: 0.875rem;
            padding: 1rem 1.25rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            margin-bottom: 0.75rem;
        }
        
        .dark .fi-fo-radio .fi-fo-field-wrp-label {
            background: var(--card-bg);
            border-color: var(--border-color);
        }
        
        .fi-fo-radio input:checked + .fi-fo-field-wrp-label {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: var(--accent-blue);
            border-width: 2px;
            box-shadow: 0 4px 12px -4px rgba(59, 130, 246, 0.3), 0 0 0 3px rgba(59, 130, 246, 0.1);
            transform: translateX(2px);
        }
        
        .dark .fi-fo-radio input:checked + .fi-fo-field-wrp-label {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            border-color: #60a5fa;
            box-shadow: 0 4px 12px -4px rgba(96, 165, 250, 0.4), 0 0 0 3px rgba(96, 165, 250, 0.15);
        }

        /* Radio Option Text */
        .fi-fo-radio .fi-fo-field-wrp-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        @media (min-width: 640px) {
            .fi-fo-radio .fi-fo-field-wrp-label {
                font-size: 0.9375rem;
            }
        }

        /* Radio Description */
        .fi-fo-radio .fi-fo-hint {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        @media (min-width: 640px) {
            .fi-fo-radio .fi-fo-hint {
                font-size: 0.8125rem;
            }
        }

        /* Section Headings */
        .fi-section-heading {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        @media (min-width: 640px) {
            .fi-section-heading {
                font-size: 1.125rem;
            }
        }

        .fi-section-description {
            font-size: 0.8125rem;
            color: var(--text-secondary);
        }

        @media (min-width: 640px) {
            .fi-section-description {
                font-size: 0.875rem;
            }
        }

        /* File Upload Area */
        .fi-fo-file-upload {
            border: 2px dashed var(--border-color);
            border-radius: 0.875rem;
            padding: 1.5rem;
            background: var(--secondary-bg);
            transition: all 0.3s;
        }

        .fi-fo-file-upload:hover {
            border-color: var(--accent-blue);
            background: var(--card-bg);
        }

        .dark .fi-fo-file-upload {
            background: var(--secondary-bg);
        }

        /* Helper Text */
        .fi-fo-helper-text {
            font-size: 0.75rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        @media (min-width: 640px) {
            .fi-fo-helper-text {
                font-size: 0.8125rem;
            }
        }

        /* Info Banner */
        .info-banner {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1.5px solid var(--accent-blue);
            border-radius: 0.875rem;
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
        }

        .dark .info-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            border-color: #60a5fa;
        }

        .info-banner-content {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .info-icon {
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        @media (min-width: 640px) {
            .info-icon {
                font-size: 2rem;
            }
        }

        .info-text {
            flex: 1;
        }

        .info-title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.375rem 0;
            line-height: 1.3;
        }

        @media (min-width: 640px) {
            .info-title {
                font-size: 1rem;
            }
        }

        .info-description {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        @media (min-width: 640px) {
            .info-description {
                font-size: 0.875rem;
            }
        }

        .info-description strong {
            color: var(--accent-blue);
            font-weight: 700;
        }

        .dark .info-description strong {
            color: #60a5fa;
        }

        /* Wizard Steps */
        .fi-wizard-step {
            padding: 1rem 0;
        }

        @media (min-width: 640px) {
            .fi-wizard-step {
                padding: 1.5rem 0;
            }
        }

        /* Wizard Navigation */
        .fi-wizard-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1.5px solid var(--border-color);
        }

        /* Mobile Optimizations */
        @media (max-width: 639px) {
            .upload-page-container {
                padding: 0.75rem;
            }

            .upload-hero {
                margin-bottom: 1.25rem;
            }

            .upload-icon-wrapper {
                width: 3rem;
                height: 3rem;
            }

            .upload-icon {
                font-size: 1.75rem;
            }

            .upload-title {
                font-size: 1.25rem;
            }

            .upload-subtitle {
                font-size: 0.8125rem;
            }

            .wizard-card {
                padding: 1rem;
            }

            .fi-fo-radio .fi-fo-field-wrp-label {
                padding: 0.875rem 1rem;
                font-size: 0.8125rem;
            }

            .info-banner {
                padding: 1rem;
            }

            .info-icon {
                font-size: 1.5rem;
            }

            .info-title {
                font-size: 0.875rem;
            }

            .info-description {
                font-size: 0.75rem;
            }
        }

        /* Better contrast for dark mode */
        .dark .upload-title,
        .dark .info-title {
            color: #f1f5f9 !important;
        }

        .dark .upload-subtitle,
        .dark .info-description {
            color: #cbd5e1 !important;
        }
    </style>

    <div class="upload-page-container">
        {{-- Hero Header --}}
        <div class="upload-hero">
            <div class="upload-icon-wrapper">
                <span class="upload-icon">🧠</span>
            </div>
            <h1 class="upload-title">Slim Document Uploaden</h1>
            <p class="upload-subtitle">
                In 3 eenvoudige stappen. Wij zorgen voor de rest.
            </p>
        </div>

        {{-- Wizard Card --}}
        <div class="wizard-card">
            <form wire:submit="submit">
                {{ $this->form }}
            </form>
        </div>
        
        {{-- Help Text Banner --}}
        <div class="info-banner">
            <div class="info-banner-content">
                <div class="info-icon">💡</div>
                <div class="info-text">
                    <h3 class="info-title">Waarom vragen we dit?</h3>
                    <p class="info-description">
                        Door te weten wat voor document u uploadt, kan ons AI-systeem 
                        <strong>nauwkeuriger</strong> verwerken. 
                        Dit verhoogt de kans op <strong>directe goedkeuring</strong> naar 95%+!
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
