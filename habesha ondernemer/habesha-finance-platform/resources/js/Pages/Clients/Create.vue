<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    company: '',
    vat_number: '',
    address: '',
    city: '',
    postal_code: '',
    country: 'NL',
    notes: '',
});

const submit = () => {
    form.post(route('clients.store'));
};
</script>

<template>
    <Head title="Nieuwe Klant" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Nieuwe Klant
                </h2>
                <Link :href="route('clients.index')" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Terug
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basis Informatie</h3>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">
                                        Naam *
                                    </label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        id="name"
                                        required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">
                                        Email
                                    </label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        id="email"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.email }"
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">
                                        Telefoon
                                    </label>
                                    <input
                                        v-model="form.phone"
                                        type="tel"
                                        id="phone"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.phone }"
                                    />
                                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.phone }}
                                    </p>
                                </div>

                                <div>
                                    <label for="company" class="block text-sm font-medium text-gray-700">
                                        Bedrijf
                                    </label>
                                    <input
                                        v-model="form.company"
                                        type="text"
                                        id="company"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.company }"
                                    />
                                    <p v-if="form.errors.company" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.company }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Business Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Bedrijfsinformatie</h3>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <label for="vat_number" class="block text-sm font-medium text-gray-700">
                                        BTW Nummer
                                    </label>
                                    <input
                                        v-model="form.vat_number"
                                        type="text"
                                        id="vat_number"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.vat_number }"
                                    />
                                    <p v-if="form.errors.vat_number" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.vat_number }}
                                    </p>
                                </div>

                                <div>
                                    <label for="country" class="block text-sm font-medium text-gray-700">
                                        Land
                                    </label>
                                    <select
                                        v-model="form.country"
                                        id="country"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.country }"
                                    >
                                        <option value="NL">Nederland</option>
                                        <option value="BE">België</option>
                                        <option value="DE">Duitsland</option>
                                        <option value="FR">Frankrijk</option>
                                        <option value="UK">Verenigd Koninkrijk</option>
                                    </select>
                                    <p v-if="form.errors.country" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.country }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Adres Informatie</h3>
                            <div class="space-y-6">
                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700">
                                        Adres
                                    </label>
                                    <textarea
                                        v-model="form.address"
                                        id="address"
                                        rows="3"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                        :class="{ 'border-red-500': form.errors.address }"
                                    ></textarea>
                                    <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.address }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    <div>
                                        <label for="city" class="block text-sm font-medium text-gray-700">
                                            Stad
                                        </label>
                                        <input
                                            v-model="form.city"
                                            type="text"
                                            id="city"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                            :class="{ 'border-red-500': form.errors.city }"
                                        />
                                        <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.city }}
                                        </p>
                                    </div>

                                    <div>
                                        <label for="postal_code" class="block text-sm font-medium text-gray-700">
                                            Postcode
                                        </label>
                                        <input
                                            v-model="form.postal_code"
                                            type="text"
                                            id="postal_code"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                            :class="{ 'border-red-500': form.errors.postal_code }"
                                        />
                                        <p v-if="form.errors.postal_code" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.postal_code }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">
                                Notities
                            </label>
                            <textarea
                                v-model="form.notes"
                                id="notes"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': form.errors.notes }"
                                placeholder="Extra informatie over deze klant..."
                            ></textarea>
                            <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600">
                                {{ form.errors.notes }}
                            </p>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex items-center justify-end space-x-4">
                            <Link :href="route('clients.index')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Annuleren
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                            >
                                {{ form.processing ? 'Opslaan...' : 'Klant Opslaan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
