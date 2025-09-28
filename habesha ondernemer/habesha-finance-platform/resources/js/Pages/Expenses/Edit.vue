<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    expense: Object,
    projects: Array,
});

const form = useForm({
    project_id: props.expense.project_id,
    description: props.expense.description,
    category: props.expense.category,
    amount: props.expense.amount,
    expense_date: props.expense.expense_date,
    receipt_path: props.expense.receipt_path,
    notes: props.expense.notes,
    is_billable: props.expense.is_billable,
});

const categories = [
    'Marketing',
    'Reizen',
    'Kantoor',
    'Software',
    'Hardware',
    'Telefoon',
    'Internet',
    'Overig'
];

const submit = () => {
    form.put(route('expenses.update', props.expense.id));
};
</script>

<template>
    <Head title="Uitgave Bewerken" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Uitgave Bewerken
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('expenses.show', expense.id)" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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
                                <!-- Description -->
                                <div class="sm:col-span-2">
                                    <label for="description" class="block text-sm font-medium text-gray-700">
                                        Beschrijving *
                                    </label>
                                    <input
                                        id="description"
                                        v-model="form.description"
                                        type="text"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.description }"
                                    />
                                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.description }}
                                    </div>
                                </div>

                                <!-- Category -->
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700">
                                        Categorie *
                                    </label>
                                    <select
                                        id="category"
                                        v-model="form.category"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.category }"
                                    >
                                        <option value="">Selecteer categorie</option>
                                        <option v-for="category in categories" :key="category" :value="category">
                                            {{ category }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.category" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.category }}
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
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.amount }"
                                    />
                                    <div v-if="form.errors.amount" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.amount }}
                                    </div>
                                </div>

                                <!-- Project -->
                                <div>
                                    <label for="project_id" class="block text-sm font-medium text-gray-700">
                                        Project
                                    </label>
                                    <select
                                        id="project_id"
                                        v-model="form.project_id"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.project_id }"
                                    >
                                        <option value="">Geen project</option>
                                        <option v-for="project in projects" :key="project.id" :value="project.id">
                                            {{ project.name }}
                                        </option>
                                    </select>
                                    <div v-if="form.errors.project_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.project_id }}
                                    </div>
                                </div>

                                <!-- Expense Date -->
                                <div>
                                    <label for="expense_date" class="block text-sm font-medium text-gray-700">
                                        Uitgave Datum *
                                    </label>
                                    <input
                                        id="expense_date"
                                        v-model="form.expense_date"
                                        type="date"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.expense_date }"
                                    />
                                    <div v-if="form.errors.expense_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.expense_date }}
                                    </div>
                                </div>

                                <!-- Receipt Path -->
                                <div>
                                    <label for="receipt_path" class="block text-sm font-medium text-gray-700">
                                        Bon Pad
                                    </label>
                                    <input
                                        id="receipt_path"
                                        v-model="form.receipt_path"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        :class="{ 'border-red-300': form.errors.receipt_path }"
                                    />
                                    <div v-if="form.errors.receipt_path" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.receipt_path }}
                                    </div>
                                </div>

                                <!-- Is Billable -->
                                <div class="sm:col-span-2">
                                    <div class="flex items-center">
                                        <input
                                            id="is_billable"
                                            v-model="form.is_billable"
                                            type="checkbox"
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        />
                                        <label for="is_billable" class="ml-2 block text-sm text-gray-900">
                                            Deze uitgave is factureerbaar
                                        </label>
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
                                    {{ form.processing ? 'Opslaan...' : 'Wijzigingen Opslaan' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
