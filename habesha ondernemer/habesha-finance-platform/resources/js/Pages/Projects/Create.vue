<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    clients: Array,
});

const form = useForm({
    client_id: '',
    name: '',
    description: '',
    status: 'active',
    start_date: '',
    end_date: '',
    budget: '',
    hourly_rate: '',
    notes: '',
});

const statusOptions = [
    { value: 'active', label: 'Actief' },
    { value: 'completed', label: 'Voltooid' },
    { value: 'on_hold', label: 'On Hold' },
    { value: 'cancelled', label: 'Geannuleerd' },
];

const submit = () => {
    form.post(route('projects.store'));
};
</script>

<template>
    <Head title="Nieuw Project" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Nieuw Project
                </h2>
                <Link :href="route('projects.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Terug
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <!-- Project Name -->
                                <div class="sm:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700">
                                        Project Naam *
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.name }"
                                    />
                                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <!-- Client -->
                                <div>
                                    <label for="client_id" class="block text-sm font-medium text-gray-700">
                                        Klant *
                                    </label>
                                    <select
                                        id="client_id"
                                        v-model="form.client_id"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.client_id }"
                                    >
                                        <option value="">Selecteer klant</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.client_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.client_id }}
                                    </div>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">
                                        Status *
                                    </label>
                                    <select
                                        id="status"
                                        v-model="form.status"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.status }"
                                    >
                                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.status" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.status }}
                                    </div>
                                </div>

                                <!-- Start Date -->
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">
                                        Start Datum
                                    </label>
                                    <input
                                        id="start_date"
                                        v-model="form.start_date"
                                        type="date"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.start_date }"
                                    />
                                    <div v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.start_date }}
                                    </div>
                                </div>

                                <!-- End Date -->
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">
                                        Eind Datum
                                    </label>
                                    <input
                                        id="end_date"
                                        v-model="form.end_date"
                                        type="date"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.end_date }"
                                    />
                                    <div v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.end_date }}
                                    </div>
                                </div>

                                <!-- Budget -->
                                <div>
                                    <label for="budget" class="block text-sm font-medium text-gray-700">
                                        Budget (€)
                                    </label>
                                    <input
                                        id="budget"
                                        v-model="form.budget"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.budget }"
                                    />
                                    <div v-if="form.errors.budget" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.budget }}
                                    </div>
                                </div>

                                <!-- Hourly Rate -->
                                <div>
                                    <label for="hourly_rate" class="block text-sm font-medium text-gray-700">
                                        Uurtarief (€)
                                    </label>
                                    <input
                                        id="hourly_rate"
                                        v-model="form.hourly_rate"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.hourly_rate }"
                                    />
                                    <div v-if="form.errors.hourly_rate" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.hourly_rate }}
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="sm:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700">
                                        Beschrijving
                                    </label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.description }"
                                    ></textarea>
                                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.description }}
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
                                    {{ form.processing ? 'Opslaan...' : 'Project Aanmaken' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
