<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    currentLanguage: {
        type: String,
        default: 'nl'
    }
});

const showDropdown = ref(false);

const languages = [
    { code: 'en', name: 'English', flag: '🇺🇸' },
    { code: 'nl', name: 'Nederlands', flag: '🇳🇱' },
    { code: 'am', name: 'አማርኛ', flag: '🇪🇹' },
];

const currentLang = languages.find(lang => lang.code === props.currentLanguage) || languages[1];

const switchLanguage = (languageCode) => {
    router.post(route('language.switch'), {
        language: languageCode
    }, {
        preserveState: true,
        preserveScroll: true,
    });
    showDropdown.value = false;
};
</script>

<template>
    <div class="relative">
        <button
            @click="showDropdown = !showDropdown"
            class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none focus:text-gray-900"
        >
            <span>{{ currentLang.flag }}</span>
            <span>{{ currentLang.name }}</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <div
            v-if="showDropdown"
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
            @click.stop
        >
            <button
                v-for="language in languages"
                :key="language.code"
                @click="switchLanguage(language.code)"
                class="flex items-center space-x-3 w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                :class="{ 'bg-blue-50 text-blue-700': language.code === currentLanguage }"
            >
                <span>{{ language.flag }}</span>
                <span>{{ language.name }}</span>
            </button>
        </div>
    </div>
</template>
