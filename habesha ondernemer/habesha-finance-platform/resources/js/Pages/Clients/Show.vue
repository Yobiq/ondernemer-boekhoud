<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    client: Object,
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

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-green-100 text-green-800',
        completed: 'bg-blue-100 text-blue-800',
        on_hold: 'bg-yellow-100 text-yellow-800',
        cancelled: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
    const texts = {
        active: 'Actief',
        completed: 'Voltooid',
        on_hold: 'On Hold',
        cancelled: 'Geannuleerd',
    };
    return texts[status] || status;
};
</script>

<template>
    <Head :title="`Klant: ${client.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    {{ client.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('clients.edit', client.id)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Bewerken
                    </Link>
                    <Link :href="route('clients.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Terug
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Client Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Klant Informatie</h3>
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Naam</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ client.name }}</p>
                                    </div>
                                    <div v-if="client.company">
                                        <label class="block text-sm font-medium text-gray-700">Bedrijf</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ client.company }}</p>
                                    </div>
                                    <div v-if="client.email">
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <a :href="`mailto:${client.email}`" class="text-blue-600 hover:text-blue-800">
                                                {{ client.email }}
                                            </a>
                                        </p>
                                    </div>
                                    <div v-if="client.phone">
                                        <label class="block text-sm font-medium text-gray-700">Telefoon</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            <a :href="`tel:${client.phone}`" class="text-blue-600 hover:text-blue-800">
                                                {{ client.phone }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div class="space-y-4">
                                    <div v-if="client.vat_number">
                                        <label class="block text-sm font-medium text-gray-700">BTW Nummer</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ client.vat_number }}</p>
                                    </div>
                                    <div v-if="client.address">
                                        <label class="block text-sm font-medium text-gray-700">Adres</label>
                                        <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ client.address }}</p>
                                    </div>
                                    <div v-if="client.postal_code || client.city">
                                        <label class="block text-sm font-medium text-gray-700">Plaats</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ client.postal_code }} {{ client.city }}
                                        </p>
                                    </div>
                                    <div v-if="client.country">
                                        <label class="block text-sm font-medium text-gray-700">Land</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ client.country }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="client.notes" class="mt-6">
                            <label class="block text-sm font-medium text-gray-700">Notities</label>
                            <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ client.notes }}</p>
                        </div>
                    </div>
                </div>

                <!-- Projects -->
                <div v-if="client.projects && client.projects.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Projecten</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Naam
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Start Datum
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Budget
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Uur Tarief
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="project in client.projects" :key="project.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ project.name }}</div>
                                            <div v-if="project.description" class="text-sm text-gray-500">
                                                {{ project.description }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusColor(project.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ getStatusText(project.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ project.start_date ? formatDate(project.start_date) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ project.budget ? formatCurrency(project.budget) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ project.hourly_rate ? formatCurrency(project.hourly_rate) : '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Invoices -->
                <div v-if="client.invoices && client.invoices.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Facturen</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Factuurnummer
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Datum
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Vervaldatum
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Bedrag
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Acties
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="invoice in client.invoices" :key="invoice.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ invoice.invoice_number }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(invoice.issue_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(invoice.due_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(invoice.total_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusColor(invoice.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                                {{ getStatusText(invoice.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link :href="route('invoices.show', invoice.id)" class="text-blue-600 hover:text-blue-900">
                                                Bekijk
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Empty State for Projects and Invoices -->
                <div v-if="(!client.projects || client.projects.length === 0) && (!client.invoices || client.invoices.length === 0)" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Geen projecten of facturen</h3>
                        <p class="mt-1 text-sm text-gray-500">Deze klant heeft nog geen projecten of facturen.</p>
                        <div class="mt-6">
                            <Link :href="route('invoices.create')" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Nieuwe Factuur
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
