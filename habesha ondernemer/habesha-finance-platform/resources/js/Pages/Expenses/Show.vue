<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    expense: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('nl-NL');
};

const getCategoryColor = (category) => {
    const colors = {
        'Marketing': 'bg-blue-100 text-blue-800',
        'Reizen': 'bg-green-100 text-green-800',
        'Kantoor': 'bg-purple-100 text-purple-800',
        'Software': 'bg-yellow-100 text-yellow-800',
        'Hardware': 'bg-red-100 text-red-800',
        'Telefoon': 'bg-indigo-100 text-indigo-800',
        'Internet': 'bg-pink-100 text-pink-800',
        'Overig': 'bg-gray-100 text-gray-800',
    };
    return colors[category] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`Uitgave - ${expense.description}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Uitgave Details
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('expenses.edit', expense.id)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Bewerken
                    </Link>
                    <Link :href="route('expenses.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Terug
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Expense Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">{{ expense.description }}</h3>
                                <div class="mt-2 flex items-center space-x-4">
                                    <span :class="getCategoryColor(expense.category)" class="inline-flex px-3 py-1 text-sm font-semibold rounded-full">
                                        {{ expense.category }}
                                    </span>
                                    <span v-if="expense.is_billable" class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                        Factureerbaar
                                    </span>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-3xl font-bold text-gray-900">{{ formatCurrency(expense.amount) }}</div>
                                <div class="text-sm text-gray-500">{{ formatDate(expense.expense_date) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Expense Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Details</h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Beschrijving</label>
                                <p class="mt-1 text-sm text-gray-900">{{ expense.description }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Categorie</label>
                                <p class="mt-1 text-sm text-gray-900">{{ expense.category }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Bedrag</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatCurrency(expense.amount) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Uitgave Datum</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDate(expense.expense_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Project</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ expense.project ? expense.project.name : 'Geen project' }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Factureerbaar</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ expense.is_billable ? 'Ja' : 'Nee' }}
                                </p>
                            </div>
                            <div v-if="expense.receipt_path">
                                <label class="block text-sm font-medium text-gray-700">Bon Pad</label>
                                <p class="mt-1 text-sm text-gray-900">{{ expense.receipt_path }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="expense.notes" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Notities</h3>
                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ expense.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
