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
import { Link, usePage, useForm  } from '@inertiajs/vue3';
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
const authid = ref(props.authid);
const apiUrl = props.apiUrl;
const token = props.token;
const appid = props.appid;
const search = props.search;

const fetchusers = async (url) => {
    emit('toast', { type: 'connect', message: 'Carregando usuários...' });
    const response = await axios.get(url, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'X-App-ID': appid,
        }
    }).then(response => {
        users.value = response.data;
        emit('toast', { type: '', message: '' }); // Clear toast
    }).catch(error => {
        emit('toast', { type: 'danger', message: 'Falha ao carregar usuários!' });
        console.error(error);
    });
};

const form = useForm({
    '_method': 'delete',
    id: '',
});

const formsearch = useForm({
    search: search ?? '',
});

const fetchUsersSearch = async () => {
    emit('toast', { type: 'connect', message: 'Buscando usuários...' });
    formsearch.get(route('dashboard.admin.users'), {
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
            console.log('Operação concluida');
        },
    });
};

const deleteuser = (userId) => {
    form.id = userId;
    emit('toast', { type: 'connect', message: 'Iniciando conexão...' });
    form.delete(route('dashboard.admin.users.destroy', userId), {
            onStart: () => {
            emit('toast', { type: 'connect', message: 'Excluindo usuário...' });
            console.log('Excluindo usuário...');
            },
            onSuccess: () => {
            emit('toast', { type: 'success', message: 'Usuário excluído com sucesso!' });
            console.log('Usuário excluído com sucesso!');
            setTimeout(() => {
                window.location.reload();
            }, 3000);
            },
            onError: () => {
            emit('toast', { type: 'danger', message: 'Falha ao excluir usuário!' });
            console.error('Falha ao excluir usuário!');
            },
            onFinish: () => {
            form.id = '';
            console.log('Operação concluida');
            },
        });
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
</script>
<template>
    <AppLayout :toast="toast">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight w-full md:w-1/2">
                Usuários
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
                <Link :href="route('dashboard.admin.users.create')" class="flex ms-auto end-0 w-36 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Criar Usuário</Link>
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
                                    <Link :href="route('dashboard.admin.users.edit', user.id)" class="font-medium text-blue-600 dark:text-blue-500 hover:underline me-3">Editar</Link>
                                    <button :disabled="user.id == authid || form.processing" :title="user.id !== authid ? 'Excluir usuário' : 'Não se pode excluir o usuário logado'" :class="{'opacity-50': user.id == authid || form.processing}" @click="deleteuser(user.id)" class="font-medium text-red-500 dark:text-red-400 hover:underline ml-4">{{ form.processing ? 'Excluindo...' :  'Excluir' }}</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="my-4 flex justify-center items-center" v-if="users.prev_page_url || users.next_page_url">
                        <button 
                            v-if="users.prev_page_url" 
                            @click="fetchusers(users.prev_page_url)" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 me-2 rounded hover:bg-gray-400"
                        >
                            Anterior
                        </button>
                        <button 
                            v-for="page in paginationRange" 
                            :key="page" 
                            @click="fetchusers(getPageUrl(page))" 
                            :class="['px-4 py-2 mx-2 rounded', { 'bg-blue-500 text-white': page === users.current_page, 'bg-gray-300 text-gray-700 hover:bg-gray-400': page !== users.current_page }]"
                        >
                            {{ page }}
                        </button>
                        <button 
                            v-if="users.next_page_url" 
                            @click="fetchusers(users.next_page_url)" 
                            class="bg-gray-300 text-gray-700 px-4 py-2 ms-2 rounded hover:bg-gray-400"
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

