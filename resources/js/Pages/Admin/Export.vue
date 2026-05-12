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
import { usePage, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayoutAdmin.vue';
import axios from 'axios';

const dispatch = defineEmits(['toast']);
const toast = ref({ type: '', message: '' });
const emit = (event, data) => {
    dispatch(event, data);
    toast.value = data;
};

const { props } = usePage();
const users = ref(props.users);
const search = props.search;

const fetchusers = async (url) => {
    const response = await axios.get(url);
    users.value = response.data;
};

const paginationRange = computed(() => {
    const totalPages = users.value.last_page;
    const currentPage = users.value.current_page;
    const range = [];

    for (let i = 1; i <= totalPages; i++) {
        range.push(i);
    }

    return range;
});

const getPageUrl = (page) => {
    return `${users.value.path}?page=${page}`;
};

const exportForm = useForm({ userId: null });

const exportBid = async (userId) => {
    emit('toast', { type: 'connect', message: 'Iniciando conexão...' });
    exportForm.userId = userId;
    exportForm.processing = true;

    try {
        const response = await axios.get(route('admin.export', { id: userId }), {
             responseType: 'blob',
             withCredentials: true,
             headers: {
                 'Content-Type': 'application/json',
                 'Accept': 'text/csv',
             },
        });

        if (response.status !== 200) {
            emit('toast', { type: 'danger', message: 'Erro ao exportar editais.' });
            return;
        }
        // Verifica o tipo de conteúdo retornado

        // Verifica se o conteúdo é realmente CSV
        const contentType = response.headers['content-type'];
        if (!contentType.includes('text/csv')) {
            const text = await response.data.text(); // tenta converter o blob pra texto legível
            console.error('Resposta inesperada ao exportar:', text);
            emit('toast', { type: 'danger', message: 'Erro ao exportar: resposta inválida do servidor.' });
            return;
        }

        const blob = new Blob([response.data], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `user_${userId}_bids.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        emit('toast', { type: 'success', message: 'Editais exportados com sucesso!' });
    } catch (error) {
        console.error('Erro na exportação:', error);
        emit('toast', { type: 'danger', message: 'Falha ao exportar editais!' });
    } finally {
        exportForm.processing = false;
    }
};

const formsearch = useForm({
    search: search ?? '',
});

const fetchUsersSearch = async () => {
    emit('toast', { type: 'connect', message: 'Buscando usuários...' });
    formsearch.get(route('admin.export'), {
        onStart: () => {
            emit('toast', { type: 'connect', message: 'Buscando usuários...' });
            console.log('Buscando usuários...');
        },
        onSuccess: (response) => {
            users.value = response;
            emit('toast', { type: '', message: '' }); // Clear toast
        },
        onError: () => {
            emit('toast', { type: 'danger', message: 'Falha ao buscar usuários!' });
            console.error('Falha ao buscar usuários!');
        },
        onFinish: () => {
            formsearch.reset();
            console.log('Operação concluída');
        },
    });
};
</script>
<template>
    <AppLayout :toast="toast">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight w-full md:w-1/2">
                Exportar Editais
            </h2>
            <form class="w-full flex justify-between" @submit.prevent="fetchUsersSearch">
                        <div class="w-full hidden md:block me-auto"></div>
                        <div class="w-full md:w-1/2 flex justify-between items-center group">
                         <div class="relative z-0 w-full flex justify-center items-center group">
                             <input type="search" name="floating_search" v-model="formsearch.search" id="floating_search" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " />
                             <label for="floating_search" class="w-full peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Busque por usuários...</label>
                         </div>
                         <button type="submit" class="bg-blue-500 text-white px-4 py-1 h-10 rounded hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                 <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                         </button>
                        </div>
                    </form>
        </template>
        <div class="w-full mt-4">
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
                <template v-if="users?.data?.length">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-white font-bold">
                            <tr>
                                <th scope="col" class="px-6 py-3">ID</th>
                                <th scope="col" class="px-6 py-3">Dominio</th>
                                <th scope="col" class="px-6 py-3">Nome</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">Data de Criação</th>
                                <th scope="col" class="px-6 py-3">Data de Atualização</th>
                                <th scope="col" class="px-6 py-3"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ user.id }}</th>
                                <td class="px-6 py-4">{{ user.domain }}</td>
                                <td class="px-6 py-4">{{ user.name }}</td>
                                <td class="px-6 py-4">{{ user.email }}</td>
                                <td class="px-6 py-4">{{ new Date(user.created_at).toLocaleDateString() }}</td>
                                <td class="px-6 py-4">{{ new Date(user.updated_at).toLocaleDateString() }}</td>
                                <td class="flex px-6 py-4 text-right">
                                    <button 
                                        @click="exportBid(user.id)"     
                                        :disabled="exportForm.processing" 
                                        class="font-medium text-blue-500 dark:text-blue-400 hover:underline ml-4"
                                    >
                                        {{ exportForm.processing ? 'Exportando...' : 'Baixar' }}
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="mt-4 flex justify-center items-center" v-if="users.prev_page_url || users.next_page_url">
                        <button 
                            v-if="users.prev_page_url" 
                            @click="fetchusers(users.prev_page_url)" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400"
                        >
                            Anterior
                        </button>
                        <button 
                            v-for="page in paginationRange" 
                            :key="page" 
                            @click="fetchusers(getPageUrl(page))" 
                            :class="['px-4 py-2 rounded', { 'bg-blue-500 text-white': page === users.current_page, 'bg-gray-300 text-gray-700 hover:bg-gray-400': page !== users.current_page }]"
                        >
                            {{ page }}
                        </button>
                        <button 
                            v-if="users.next_page_url" 
                            @click="fetchusers(users.next_page_url)" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400"
                        >
                            Próximo
                        </button>
                    </div>
                </template>
                <template v-else>
                    <div id="alert-border-1" class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 dark:text-blue-400 dark:bg-gray-800 dark:border-blue-800" role="alert">
                        <svg class="shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
                        </svg>
                        <div class="ms-3 text-sm font-medium">
                            Nenhum usuário encontrado.
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </AppLayout>
</template>