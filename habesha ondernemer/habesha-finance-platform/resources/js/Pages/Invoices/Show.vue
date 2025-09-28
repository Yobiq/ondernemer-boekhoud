<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    invoice: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('nl-NL', {
        style: 'currency',
        currency: 'EUR'
    }).format(amount);
};
</script>

<template>
    <Head :title="`Factuur ${invoice.invoice_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Factuur {{ invoice.invoice_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium mb-4">Factuur Details</h3>
                        
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <p><strong>Factuurnummer:</strong> {{ invoice.invoice_number }}</p>
                                <p><strong>Klant:</strong> {{ invoice.client.name }}</p>
                                <p><strong>Status:</strong> {{ invoice.status }}</p>
                            </div>
                            <div>
                                <p><strong>Totaal:</strong> {{ formatCurrency(invoice.total_amount) }}</p>
                                <p><strong>Subtotaal:</strong> {{ formatCurrency(invoice.subtotal) }}</p>
                                <p><strong>BTW:</strong> {{ formatCurrency(invoice.tax_amount) }}</p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h4 class="text-md font-medium mb-2">Factuurregels</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Beschrijving</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aantal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prijs</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Totaal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="item in invoice.items" :key="item.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.description }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.quantity }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(item.unit_price) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(item.total_price) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-6 flex space-x-4">
                            <a :href="route('invoices.pdf', invoice.id)" target="_blank" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                PDF Download
                            </a>
                            <Link :href="route('invoices.edit', invoice.id)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Bewerken
                            </Link>
                            <Link :href="route('invoices.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Terug
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>