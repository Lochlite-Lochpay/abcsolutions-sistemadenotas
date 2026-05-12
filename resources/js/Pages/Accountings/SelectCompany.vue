
<script setup>
/****** Another website produced by The Lochlite & Lochpay Company
___
|   |
|   |             _______         ______       __      __
|   |            /       \       /  ____|     |  |    |  |
|   |______     /         \     /  /          |  |----|  |
|          |    \         /     \  \____      |  |----|  |
|__________|     \_______/       \______|     |__|    |__| Lite ®


Long live Lochlite! ******/
import { ref, computed } from 'vue';
import { Link, usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import Paginate from '@/Components/Paginate.vue';

const dispatch = defineEmits(['toast']);
const toast = ref({ type: '', message: '' });
const emit = (event, data) => {
    dispatch(event, data);
    toast.value = data;
};

const { props } = usePage();
const companies = ref(props.companies);
</script>
<template>
    <AppLayout :toast="toast">
        <div class="container mx-auto p-4">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold">Selecione uma empresa para exibir as notas</h1>
            </div>
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
                <template v-if="companies?.data?.length">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-white font-bold">
                        <tr>
                            <th scope="col" class="px-6 py-3">ID</th>
                            <th scope="col" class="px-6 py-3">Nome</th>
                            <th scope="col" class="px-6 py-3">CNPJ</th>
                            <th scope="col" class="px-6 py-3">Criada em:</th>
                            <th scope="col" class="px-6 py-3"><span class="sr-only">Ações</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="it in companies.data" :key="it.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ it.id }}</th>
                            <td class="px-6 py-4">{{ it.name }}</td>
                            <td class="px-6 py-4">{{ it.cnpj }}</td>
                            <td class="px-6 py-4">{{ new Date(it.created_at).toLocaleDateString() }}</td>
                            <td class="flex px-6 py-4 text-right">
                                <Link :href="route('dashboard.accounting.index', {home: it.id})" class="font-medium text-blue-600 dark:text-blue-500 hover:underline me-3">Selecionar</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <Paginate :paginate="companies" type="companies" @fetch="(data) => companies = data" @toast="(data) => emit('toast', data)" />  
                </template>
                <template v-else>
                    <div id="alert-border-1" class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 dark:text-blue-400 dark:bg-gray-800 dark:border-blue-800" role="alert">
                        <svg class="shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <div class="ms-3 text-sm font-medium">
                            Nenhuma empresa encontrada. Adicione uma empresa antes de tentar visualizar as notas.
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
