<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    payment: Object,
    invoices: Array,
});

const form = useForm({
    invoice_id: props.payment.invoice_id,
    amount: props.payment.amount,
    payment_date: props.payment.payment_date,
    payment_method: props.payment.payment_method,
    reference: props.payment.reference || '',
    notes: props.payment.notes || '',
});

const paymentMethods = [
    { value: 'bank_transfer', label: 'Bankoverschrijving' },
    { value: 'cash', label: 'Contant' },
    { value: 'credit_card', label: 'Creditcard' },
    { value: 'paypal', label: 'PayPal' },
    { value: 'other', label: 'Anders' },
];

const selectedInvoice = computed(() => {
    return props.invoices.find(invoice => invoice.id == form.invoice_id);
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
};

const submit = () => {
    form.put(route('payments.update', props.payment.id));
};
</script>

<template>
    <Head title="Betaling Bewerken" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Betaling Bewerken
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('payments.show', payment.id)" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Annuleren
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Invoice Selection -->
                                <div class="sm:col-span-2">
                                    <label for="invoice_id" class="block text-sm font-medium text-gray-700">
                                        Factuur *
                                    </label>
                                    <select
                                        id="invoice_id"
                                        v-model="form.invoice_id"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.invoice_id }"
                                    >
                                        <option value="">Selecteer factuur</option>
                                        <option v-for="invoice in invoices" :key="invoice.id" :value="invoice.id">
                                            {{ invoice.invoice_number }} - {{ invoice.client.name }} ({{ formatCurrency(invoice.total_amount) }})
                                        </option>
                                    </select>
                                    <div v-if="form.errors.invoice_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.invoice_id }}
                                    </div>
                                </div>

                                <!-- Amount -->
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700">
                                        Bedrag (€) *
                                    </label>
                                    <input
                                        id="amount"
                                        v-model="form.amount"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        :max="selectedInvoice ? selectedInvoice.total_amount : ''"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.amount }"
                                    />
                                    <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.amount }}
                                    </div>
                                    <div v-if="selectedInvoice" class="mt-1 text-sm text-gray-500">
                                        Factuur totaal: {{ formatCurrency(selectedInvoice.total_amount) }}
                                    </div>
                                </div>

                                <!-- Payment Date -->
                                <div>
                                    <label for="payment_date" class="block text-sm font-medium text-gray-700">
                                        Betaaldatum *
                                    </label>
                                    <input
                                        id="payment_date"
                                        v-model="form.payment_date"
                                        type="date"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.payment_date }"
                                    />
                                    <div v-if="form.errors.payment_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.payment_date }}
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div>
                                    <label for="payment_method" class="block text-sm font-medium text-gray-700">
                                        Betaalmethode *
                                    </label>
                                    <select
                                        id="payment_method"
                                        v-model="form.payment_method"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.payment_method }"
                                    >
                                        <option v-for="method in paymentMethods" :key="method.value" :value="method.value">
                                            {{ method.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.payment_method" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.payment_method }}
                                    </div>
                                </div>

                                <!-- Reference -->
                                <div>
                                    <label for="reference" class="block text-sm font-medium text-gray-700">
                                        Referentie
                                    </label>
                                    <input
                                        id="reference"
                                        v-model="form.reference"
                                        type="text"
                                        placeholder="Bijv. transactie ID, cheque nummer"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.reference }"
                                    />
                                    <div v-if="form.errors.reference" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.reference }}
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="sm:col-span-2">
                                    <label for="notes" class="block text-sm font-medium text-gray-700">
                                        Notities
                                    </label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="3"
                                        placeholder="Extra informatie over de betaling"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.notes }"
                                    ></textarea>
                                    <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.notes }}
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Opslaan...' : 'Betaling Bijwerken' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
