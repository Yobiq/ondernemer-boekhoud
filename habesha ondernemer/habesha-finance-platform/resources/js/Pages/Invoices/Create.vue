<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    clients: Array,
    projects: Array,
});

const form = useForm({
    client_id: '',
    project_id: '',
    issue_date: new Date().toISOString().split('T')[0],
    due_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    items: [
        {
            description: '',
            quantity: 1,
            unit_price: 0,
        }
    ],
    tax_rate: 21,
    notes: '',
    terms: 'Betaling binnen 30 dagen na factuurdatum.',
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
    form.post(route('invoices.store'));
};
</script>

<template>
    <Head title="Nieuwe Factuur" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Nieuwe Factuur
                </h2>
                <Link :href="route('invoices.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Terug
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Client and Project Selection -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="client_id" class="block text-sm font-medium text-gray-700">
                                    Klant *
                                </label>
                                <select
                                    v-model="form.client_id"
                                    id="client_id"
                                    required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.client_id }"
                                >
                                    <option value="">Selecteer een klant</option>
                                    <option v-for="client in clients" :key="client.id" :value="client.id">
                                        {{ client.name }} {{ client.company ? `(${client.company})` : '' }}
                                    </option>
                                </select>
                                <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.client_id }}
                                </p>
                            </div>

                            <div>
                                <label for="project_id" class="block text-sm font-medium text-gray-700">
                                    Project
                                </label>
                                <select
                                    v-model="form.project_id"
                                    id="project_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.project_id }"
                                >
                                    <option value="">Geen project</option>
                                    <option v-for="project in projects" :key="project.id" :value="project.id">
                                        {{ project.name }} - {{ project.client.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.project_id" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.project_id }}
                                </p>
                            </div>
                        </div>

                        <!-- Dates -->
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="issue_date" class="block text-sm font-medium text-gray-700">
                                    Factuurdatum *
                                </label>
                                <input
                                    v-model="form.issue_date"
                                    type="date"
                                    id="issue_date"
                                    required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.issue_date }"
                                />
                                <p v-if="form.errors.issue_date" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.issue_date }}
                                </p>
                            </div>

                            <div>
                                <label for="due_date" class="block text-sm font-medium text-gray-700">
                                    Vervaldatum *
                                </label>
                                <input
                                    v-model="form.due_date"
                                    type="date"
                                    id="due_date"
                                    required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.due_date }"
                                />
                                <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.due_date }}
                                </p>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Factuurregels</h3>
                                <button
                                    type="button"
                                    @click="addItem"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm"
                                >
                                    Regel Toevoegen
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-1 gap-4 sm:grid-cols-12 items-end">
                                    <div class="sm:col-span-5">
                                        <label :for="`description_${index}`" class="block text-sm font-medium text-gray-700">
                                            Beschrijving *
                                        </label>
                                        <input
                                            v-model="item.description"
                                            type="text"
                                            :id="`description_${index}`"
                                            required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                            :class="{ 'border-red-500': form.errors[`items.${index}.description`] }"
                                        />
                                        <p v-if="form.errors[`items.${index}.description`]" class="mt-1 text-sm text-red-600">
                                            {{ form.errors[`items.${index}.description`] }}
                                        </p>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label :for="`quantity_${index}`" class="block text-sm font-medium text-gray-700">
                                            Aantal *
                                        </label>
                                        <input
                                            v-model.number="item.quantity"
                                            type="number"
                                            :id="`quantity_${index}`"
                                            min="1"
                                            required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                            :class="{ 'border-red-500': form.errors[`items.${index}.quantity`] }"
                                        />
                                        <p v-if="form.errors[`items.${index}.quantity`]" class="mt-1 text-sm text-red-600">
                                            {{ form.errors[`items.${index}.quantity`] }}
                                        </p>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label :for="`unit_price_${index}`" class="block text-sm font-medium text-gray-700">
                                            Prijs per stuk *
                                        </label>
                                        <input
                                            v-model.number="item.unit_price"
                                            type="number"
                                            :id="`unit_price_${index}`"
                                            step="0.01"
                                            min="0"
                                            required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                            :class="{ 'border-red-500': form.errors[`items.${index}.unit_price`] }"
                                        />
                                        <p v-if="form.errors[`items.${index}.unit_price`]" class="mt-1 text-sm text-red-600">
                                            {{ form.errors[`items.${index}.unit_price`] }}
                                        </p>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Totaal
                                        </label>
                                        <div class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-sm">
                                            {{ formatCurrency(item.quantity * item.unit_price) }}
                                        </div>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <button
                                            v-if="form.items.length > 1"
                                            type="button"
                                            @click="removeItem(index)"
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-3 rounded text-sm"
                                        >
                                            Verwijder
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tax Rate -->
                        <div>
                            <label for="tax_rate" class="block text-sm font-medium text-gray-700">
                                BTW Percentage
                            </label>
                            <input
                                v-model.number="form.tax_rate"
                                type="number"
                                id="tax_rate"
                                step="0.01"
                                min="0"
                                max="100"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.tax_rate }"
                            />
                            <p v-if="form.errors.tax_rate" class="mt-1 text-sm text-red-600">
                                {{ form.errors.tax_rate }}
                            </p>
                        </div>

                        <!-- Totals -->
                        <div class="bg-gray-50 p-4 rounded-lg">
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

                        <!-- Notes and Terms -->
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">
                                    Notities
                                </label>
                                <textarea
                                    v-model="form.notes"
                                    id="notes"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.notes }"
                                ></textarea>
                                <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.notes }}
                                </p>
                            </div>

                            <div>
                                <label for="terms" class="block text-sm font-medium text-gray-700">
                                    Betalingsvoorwaarden
                                </label>
                                <textarea
                                    v-model="form.terms"
                                    id="terms"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': form.errors.terms }"
                                ></textarea>
                                <p v-if="form.errors.terms" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.terms }}
                                </p>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <Link :href="route('invoices.index')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Annuleren
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                            >
                                {{ form.processing ? 'Opslaan...' : 'Factuur Aanmaken' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
