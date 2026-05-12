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
import { ref,computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';                 

const dispatch = defineEmits(['toast']);
const toast = ref({ type: '', message: '' });
const emit = (event, data) => {
    dispatch(event, data);
    toast.value = data;
};

const props = defineProps({
    search: String,
    companies: Object,
    adminfirstaccess: Boolean
});

const search = ref(props.search);
const formsearch = useForm({ search: search.value ?? '' });

const deleteCompany = (companyId) => {
    emit('toast', { type: 'connect', message: 'Iniciando conexão...' });
    formsearch.delete(route('dashboard.home.destroy', {home: companyId}), { 
        onStart: () => {
            emit('toast', { type: 'connect', message: 'Excluindo empresa...' });
            console.log('Excluindo empresa...');
        },
        onSuccess: () => {
            emit('toast', { type: 'success', message: 'Empresa excluída com sucesso!' });
            console.log('Empresa excluída com sucesso!');
        },
        onError: () => {
            emit('toast', { type: 'danger', message: 'Falha ao excluir empresa!' });
            console.error('Falha ao excluir empresa!');
        },
        onFinish: () => {
            setTimeout(() => {
                emit('toast', { type: 'info', message: 'Tarefa encerrada' });
            }, 2000);       
            console.log('Tarefa encerrada');
        },
    });
};                              

const fetchSearch = async () => {
    emit('toast', { type: 'connect', message: 'Iniciando conexão...' });
    formsearch.get(route('dashboard.home.index'), {
        onStart: () => {
            emit('toast', { type: 'connect', message: 'Buscando empresa...' });
            console.log('Buscando empresas...');
        },
        onSuccess: (response) => {
          companies.value = response.companies;
          emit('toast', { type: '', message: '' }); // Clear toast
        },
        onError: () => {
            emit('toast', { type: 'danger', message: 'Falha ao buscar empresas!' });
            console.error('Falha ao buscar empresas!');
        },
        onFinish: () => {
            formsearch.reset();
            console.log('Operação concluída');
        },
    });
};

onMounted(() => {
if(props.adminfirstaccess) {
    emit('toast', { type: 'info', message: 'Bem vindo (a) ' + usePage().props?.auth?.user?.name });
    
    setTimeout(() => {
        emit('toast', { type: 'info', message: 'Redirecionando ao painel administrativo...' });
        window.location.href = route('dashboard.admin.home');
    }, 3000);
}      
});
</script>
<template>
    <AppLayout title="Minhas empresas" :toast="toast">
      <template #header>
            <h2 class="w-1/2 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Minhas empresas</h2>
            <form class="w-full flex justify-between" @submit.prevent="fetchSearch">
                <div class="w-full hidden md:block me-auto"></div>
                <div class="w-full md:w-1/2 flex justify-between items-center group">
                    <div class="relative z-0 w-full flex justify-center items-center group">
                        <input type="search" name="floating_search" v-model="formsearch.search" id="floating_search" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " />
                        <label for="floating_search" class="w-full peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Busque por empresa...</label>
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
            <div class="bg-white dark:bg-gray-400 dark:text-white p-6 rounded shadow-md" id="accordion-collapse" data-accordion="collapse">
                <Link :href="route('dashboard.home.create')" class="mb-4 flex ms-auto end-0 w-36 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Criar Empresa</Link>
                <template v-if="companies?.data?.length > 0">
                        <div class="p-5 border border-gray-200 dark:border-gray-700 dark:bg-gray-900">
                            <ol class="relative border-s border-gray-200 dark:border-gray-700">
                                <li class="mb-10 ms-6" v-for="(company, index) in companies.data" :key="company.id">
                                    <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">
                                        <svg class="w-2.5 h-2.5 text-blue-800 dark:text-blue-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                                        </svg>
                                    </span>
                                    <h3 class="flex items-center mb-1 text-lg font-semibold text-gray-900 dark:text-white">{{ company.name }} <span class="bg-blue-100 text-blue-800 text-sm font-medium me-2 px-2.5 py-0.5 rounded-sm dark:bg-blue-900 dark:text-blue-300 ms-3">{{ company.status ?? 'Ativa' }}</span></h3>
                                    <time class="block mb-2 text-sm font-normal leading-none text-gray-400 dark:text-white">Criada em: {{ new Date(company.created_at).toLocaleDateString() }}</time>
                                    <p class="mb-4 text-base font-normal text-gray-500 dark:text-gray-400">{{ company.description }}</p>
                                    <Link :href="route('dashboard.nfse.index', { home: company.id, webserver: 'tomadas'})" class="me-4 mb-3 md:mb-0 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:outline-none focus:ring-gray-100 focus:text-blue-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 me-2.5">
                                            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                                            <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd" />
                                        </svg> NFS-e Prestada
                                    </Link>
                                     <Link :href="route('dashboard.nfse.index', { home: company.id, webserver: 'prestadas'})" class="me-4 mb-3 md:mb-0 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:outline-none focus:ring-gray-100 focus:text-blue-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 me-2.5">
                                            <path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
                                            <path fill-rule="evenodd" d="M1.323 11.447C2.811 6.976 7.028 3.75 12.001 3.75c4.97 0 9.185 3.223 10.675 7.69.12.362.12.752 0 1.113-1.487 4.471-5.705 7.697-10.677 7.697-4.97 0-9.186-3.223-10.675-7.69a1.762 1.762 0 0 1 0-1.113ZM17.25 12a5.25 5.25 0 1 1-10.5 0 5.25 5.25 0 0 1 10.5 0Z" clip-rule="evenodd" />
                                        </svg> NFS-e Tomada
                                    </Link>
                                    <Link :href="route('dashboard.home.edit', { home: company.id})" class="me-4 mb-3 md:mb-0 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:outline-none focus:ring-gray-100 focus:text-blue-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 me-2.5">
                                            <path d="M21.731 2.269a2.625 2.625 0 0 0-3.712 0l-1.157 1.157 3.712 3.712 1.157-1.157a2.625 2.625 0 0 0 0-3.712ZM19.513 8.199l-3.712-3.712-12.15 12.15a5.25 5.25 0 0 0-1.32 2.214l-.8 2.685a.75.75 0 0 0 .933.933l2.685-.8a5.25 5.25 0 0 0 2.214-1.32L19.513 8.2Z" />
                                        </svg> Editar
                                    </Link>
                                    <button @click="deleteCompany(company.id)" type="button" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-4 focus:outline-none focus:ring-gray-100 focus:text-blue-700 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-3.5 h-3.5 me-2.5">
                                            <path fill-rule="evenodd" d="M5.625 1.5H9a3.75 3.75 0 0 1 3.75 3.75v1.875c0 1.036.84 1.875 1.875 1.875H16.5a3.75 3.75 0 0 1 3.75 3.75v7.875c0 1.035-.84 1.875-1.875 1.875H5.625a1.875 1.875 0 0 1-1.875-1.875V3.375c0-1.036.84-1.875 1.875-1.875ZM9.75 14.25a.75.75 0 0 0 0 1.5H15a.75.75 0 0 0 0-1.5H9.75Z" clip-rule="evenodd" />
                                            <path d="M14.25 5.25a5.23 5.23 0 0 0-1.279-3.434 9.768 9.768 0 0 1 6.963 6.963A5.23 5.23 0 0 0 16.5 7.5h-1.875a.375.375 0 0 1-.375-.375V5.25Z" />
                                        </svg> Excluir
                                    </button>
                                </li>
                            </ol>
                        </div>
                </template>
                <template v-else>
                    <div class="p-5 border border-gray-200 dark:border-gray-700 dark:bg-gray-900">
                        <p class="text-base font-normal text-gray-500 dark:text-gray-400">Nenhuma empresa encontrada.</p>
                    </div>                          
                </template>     
            </div>
        </div>
    </AppLayout>
</template>
