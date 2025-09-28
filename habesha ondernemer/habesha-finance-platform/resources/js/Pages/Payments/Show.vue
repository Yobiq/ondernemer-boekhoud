<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    payment: Object,
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

const getPaymentMethodText = (method) => {
    const methods = {
        'bank_transfer': 'Bankoverschrijving',
        'cash': 'Contant',
        'credit_card': 'Creditcard',
        'paypal': 'PayPal',
        'other': 'Anders',
    };
    return methods[method] || method;
};

const getPaymentMethodColor = (method) => {
    const colors = {
        'bank_transfer': 'bg-blue-100 text-blue-800',
        'cash': 'bg-green-100 text-green-800',
        'credit_card': 'bg-purple-100 text-purple-800',
        'paypal': 'bg-yellow-100 text-yellow-800',
        'other': 'bg-gray-100 text-gray-800',
    };
    return colors[method] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head :title="`Betaling ${payment.id}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Betaling Details
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('payments.edit', payment.id)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Bewerken
                    </Link>
                    <Link :href="route('payments.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Terug
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Payment Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <!-- Payment Info -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Betaling Informatie</h3>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Betaling ID</dt>
                                        <dd class="mt-1 text-sm text-gray-900">#{{ payment.id }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Bedrag</dt>
                                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ formatCurrency(payment.amount) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Betaaldatum</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(payment.payment_date) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Betaalmethode</dt>
                                        <dd class="mt-1">
                                            <span :class="getPaymentMethodColor(payment.payment_method)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ getPaymentMethodText(payment.payment_method) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div v-if="payment.reference">
                                        <dt class="text-sm font-medium text-gray-500">Referentie</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ payment.reference }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Geregistreerd op</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(payment.created_at) }}</dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Invoice Info -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Factuur Informatie</h3>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Factuurnummer</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            <Link :href="route('invoices.show', payment.invoice.id)" class="text-blue-600 hover:text-blue-800">
                                                {{ payment.invoice.invoice_number }}
                                            </Link>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Klant</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ payment.invoice.client.name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Factuur Totaal</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatCurrency(payment.invoice.total_amount) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                                        <dd class="mt-1">
                                            <span :class="payment.invoice.status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ payment.invoice.status === 'paid' ? 'Betaald' : 'Deels Betaald' }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Vervaldatum</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ formatDate(payment.invoice.due_date) }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div v-if="payment.notes" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Notities</h3>
                        <p class="text-sm text-gray-600 whitespace-pre-line">{{ payment.notes }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
