<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    invoice: Object,
    clients: Array,
    projects: Array,
});

const form = useForm({
    client_id: props.invoice.client_id,
    project_id: props.invoice.project_id,
    issue_date: props.invoice.issue_date,
    due_date: props.invoice.due_date,
    status: props.invoice.status,
    items: props.invoice.items.map(item => ({
        id: item.id,
        description: item.description,
        quantity: item.quantity,
        unit_price: item.unit_price,
    })),
    tax_rate: props.invoice.tax_rate,
    notes: props.invoice.notes || '',
    terms: props.invoice.terms || 'Betaling binnen 30 dagen na factuurdatum.',
});

const addItem = () => {
    form.items.push({
        description: '',
        quantity: 1,
        unit_price: 0,
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const subtotal = computed(() => {
    return form.items.reduce((total, item) => {
        return total + (item.quantity * item.unit_price);
    }, 0);
});

const taxAmount = computed(() => {
    return subtotal.value * (form.tax_rate / 100);
});

const totalAmount = computed(() => {
    return subtotal.value + taxAmount.value;
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
};

const submit = () => {
    form.put(route('invoices.update', props.invoice.id));
};
</script>

<template>
    <Head :title="`Factuur ${invoice.invoice_number} Bewerken`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Factuur {{ invoice.invoice_number }} Bewerken
                </h2>
                <Link :href="route('invoices.show', invoice.id)" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Annuleren
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Invoice Header -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Factuur Details</h3>
                            
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <!-- Client Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Klant</label>
                                    <select v-model="form.client_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Selecteer een klant</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.client_id" class="text-red-500 text-sm mt-1">{{ form.errors.client_id }}</div>
                                </div>

                                <!-- Project Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Project (optioneel)</label>
                                    <select v-model="form.project_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Geen project</option>
                                        <option v-for="project in projects" :key="project.id" :value="project.id">
                                            {{ project.name }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Issue Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Factuurdatum</label>
                                    <input v-model="form.issue_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <div v-if="form.errors.issue_date" class="text-red-500 text-sm mt-1">{{ form.errors.issue_date }}</div>
                                </div>

                                <!-- Due Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Vervaldatum</label>
                                    <input v-model="form.due_date" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <div v-if="form.errors.due_date" class="text-red-500 text-sm mt-1">{{ form.errors.due_date }}</div>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select v-model="form.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="draft">Concept</option>
                                        <option value="sent">Verzonden</option>
                                        <option value="paid">Betaald</option>
                                        <option value="overdue">Achterstallig</option>
                                        <option value="cancelled">Geannuleerd</option>
                                    </select>
                                </div>

                                <!-- Tax Rate -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">BTW Percentage</label>
                                    <input v-model.number="form.tax_rate" type="number" step="0.01" min="0" max="100" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Factuurregels</h3>
                                <button type="button" @click="addItem" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    + Item Toevoegen
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Beschrijving</label>
                                        <input v-model="item.description" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Aantal</label>
                                        <input v-model.number="item.quantity" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Prijs per stuk</label>
                                        <input v-model.number="item.unit_price" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div class="flex items-end">
                                        <div class="flex-1">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Totaal</label>
                                            <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                                                {{ formatCurrency(item.quantity * item.unit_price) }}
                                            </div>
                                        </div>
                                        <button v-if="form.items.length > 1" type="button" @click="removeItem(index)" class="ml-2 bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded">
                                            ×
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Totals -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Totaaloverzicht</h3>
                            
                            <div class="max-w-md ml-auto">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">Subtotaal:</span>
                                    <span class="text-sm font-medium text-gray-900">{{ formatCurrency(subtotal) }}</span>
                                </div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">BTW ({{ form.tax_rate }}%):</span>
                                    <span class="text-sm font-medium text-gray-900">{{ formatCurrency(taxAmount) }}</span>
                                </div>
                                <div class="flex justify-between items-center border-t border-gray-300 pt-2">
                                    <span class="text-lg font-semibold text-gray-900">Totaal:</span>
                                    <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(totalAmount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes and Terms -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Notities</label>
                                    <textarea v-model="form.notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Betalingsvoorwaarden</label>
                                    <textarea v-model="form.terms" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50">
                            {{ form.processing ? 'Opslaan...' : 'Factuur Opslaan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
