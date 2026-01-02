<x-filament-widgets::widget>
    <div class="welcome-widget-enhanced">
        <div class="welcome-content-enhanced">
            <div class="welcome-header-enhanced">
                <div class="welcome-text-enhanced">
                    <h3 class="welcome-title-enhanced">Welkom bij MARCOFIC</h3>
                    <p class="welcome-subtitle-enhanced">Professionele boekhouding met meer dan 200 tevreden klanten</p>
                </div>
                    </div>
                    
            <div class="welcome-steps-enhanced">
                <div class="welcome-step-enhanced step-1">
                    <div class="step-icon-wrapper">
                        <div class="step-number-enhanced">1</div>
                        <div class="step-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="step-content-enhanced">
                        <div class="step-title-enhanced">Foto Maken</div>
                        <div class="step-description-enhanced">Maak een foto van uw bonnetje met uw telefoon</div>
                    </div>
                </div>
                <div class="welcome-step-enhanced step-2">
                    <div class="step-icon-wrapper">
                        <div class="step-number-enhanced">2</div>
                        <div class="step-icon">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="step-content-enhanced">
                        <div class="step-title-enhanced">Automatisch</div>
                        <div class="step-description-enhanced">Ons systeem verwerkt alles automatisch</div>
                    </div>
                </div>
                <div class="welcome-step-enhanced step-3">
                    <div class="step-icon-wrapper">
                        <div class="step-number-enhanced">3</div>
                    </div>
                    <div class="step-content-enhanced">
                        <div class="step-title-enhanced">Klaar!</div>
                        <div class="step-description-enhanced">Wij zorgen voor de rest</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>

<style>
.welcome-widget-enhanced {
    padding: 0;
    background: linear-gradient(135deg, rgb(59 130 246 / 0.05) 0%, rgb(147 51 234 / 0.05) 100%);
    border-radius: 12px;
    overflow: hidden;
}

.dark .welcome-widget-enhanced {
    background: linear-gradient(135deg, rgb(59 130 246 / 0.1) 0%, rgb(147 51 234 / 0.1) 100%);
}

.welcome-content-enhanced {
    padding: 24px;
}

@media (max-width: 640px) {
    .welcome-content-enhanced {
        padding: 20px;
    }
}

.welcome-header-enhanced {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 1px solid rgb(229 231 235);
    position: relative;
}

.dark .welcome-header-enhanced {
    border-bottom-color: rgb(75 85 99);
}

.welcome-header-enhanced::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 2px;
    background: linear-gradient(90deg, rgb(59 130 246), rgb(147 51 234));
    border-radius: 2px;
}


.welcome-text-enhanced {
    flex: 1;
    padding-top: 4px;
}

.welcome-title-enhanced {
    font-size: 20px;
    font-weight: 700;
    color: rgb(17 24 39);
    margin: 0 0 6px 0;
    letter-spacing: -0.02em;
}

.dark .welcome-title-enhanced {
    color: rgb(255 255 255);
}

.welcome-subtitle-enhanced {
    font-size: 14px;
    color: rgb(107 114 128);
    margin: 0;
    line-height: 1.5;
}

.dark .welcome-subtitle-enhanced {
    color: rgb(156 163 175);
}

.welcome-steps-enhanced {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .welcome-steps-enhanced {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}

.welcome-step-enhanced {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 20px;
    background: white;
    border: 1px solid rgb(229 231 235);
    border-radius: 12px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.dark .welcome-step-enhanced {
    background: rgb(31 41 55);
    border-color: rgb(75 85 99);
}

.welcome-step-enhanced::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, rgb(59 130 246), rgb(147 51 234));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.welcome-step-enhanced:hover::before {
    transform: scaleX(1);
}

.welcome-step-enhanced:hover {
    border-color: rgb(59 130 246);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    transform: translateY(-2px);
}

.step-icon-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
}

.step-number-enhanced {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, rgb(59 130 246), rgb(147 51 234));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.step-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: rgb(239 246 255);
    color: rgb(59 130 246);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.dark .step-icon {
    background: rgb(30 58 138);
    color: rgb(147 197 253);
}

.step-icon svg {
    width: 18px;
    height: 18px;
}

.step-content-enhanced {
    flex: 1;
}

.step-title-enhanced {
    font-size: 16px;
    font-weight: 600;
    color: rgb(17 24 39);
    margin-bottom: 4px;
    letter-spacing: -0.01em;
}

.dark .step-title-enhanced {
    color: rgb(255 255 255);
}

.step-description-enhanced {
    font-size: 13px;
    color: rgb(107 114 128);
    line-height: 1.5;
}

.dark .step-description-enhanced {
    color: rgb(156 163 175);
}

/* Step-specific colors */
.step-1 .step-icon {
    background: rgb(239 246 255);
    color: rgb(59 130 246);
}

.dark .step-1 .step-icon {
    background: rgb(30 58 138);
    color: rgb(147 197 253);
}

.step-2 .step-icon {
    background: rgb(240 253 244);
    color: rgb(34 197 94);
}

.dark .step-2 .step-icon {
    background: rgb(20 83 45);
    color: rgb(134 239 172);
}

.step-3 .step-icon {
    display: none !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
}

.dark .step-3 .step-icon {
    display: none !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
}
</style>
