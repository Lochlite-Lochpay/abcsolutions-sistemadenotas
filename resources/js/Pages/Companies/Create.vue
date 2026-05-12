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
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const dispatch = defineEmits(['toast']);
const toast = ref({ type: '', message: '' });
const emit = (event, data) => {
    dispatch(event, data);
    toast.value = data;
};

const form = useForm({
    name: '',
    cnpj: '',
    token: '',
    token_qive: '',
    api_id: '',
    api_key: '',
    client_id_accountings: '',          
    client_key_accountings: '',     
    client_audience_accountings: '',    
    access_token_accountings: '',
    personal_integration_id_accountings: '',
    api_generate_integration_id_accountings: '',  
    accounting: '0', // Default to false                 
});

const submit = () => {
    emit('toast', { type: 'connect', message: 'Iniciando conexão...' });
    form.post(route('dashboard.home.store'), {
        onStart: () => {
            emit('toast', { type: 'connect', message: 'Salvando empresa...' });
            console.log('Salvando empresa...');
        },
        onSuccess: () => {
            emit('toast', { type: 'success', message: 'Empresa salva com sucesso!' });
            console.log('Empresa salva com sucesso!');
        },
        onError: () => {
            emit('toast', { type: 'danger', message: 'Falha ao salvar empresa!' });
            console.error('Falha ao salvar empresa!');
        },
        onFinish: () => {
            form.id = '';
            console.log('Operação concluida');
        },
    });
};
</script>
<template>
    <AppLayout :toast="toast">
        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">Criar Empresa</h1>
            <form @submit.prevent="submit" class="bg-white dark:bg-gray-700 dark:text-white p-6 rounded shadow-md">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 dark:text-white">Nome</label>
                    <input type="text" id="name" v-model="form.name" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm" autocomplete="organization" required>
                    <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
                </div>
                <div class="mb-4">
                    <label for="cnpj" class="block text-gray-700 dark:text-white">CNPJ</label>
                    <input type="text" id="cnpj" v-model="form.cnpj" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm" autocomplete="taxid" required pattern="\d{14}" title="Digite um CNPJ válido com 14 números" @input="form.cnpj = form.cnpj.replace(/\D/g, '').slice(0, 14)">
                    <span v-if="form.errors.cnpj" class="text-red-500 text-sm">{{ form.errors.cnpj }}</span>
                </div>
                <div class="mb-4">
                    <label for="token" class="block text-gray-700 dark:text-white">Token Emitente (IntegraNotas)</label>
                    <input type="password" id="token" v-model="form.token" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <span v-if="form.errors.token" class="text-red-500 text-sm">{{ form.errors.token }}</span>
                </div>
                <div class="mb-4 hidden">
                    <label for="tokenqive" class="block text-gray-700 dark:text-white">Token Tomador (Qive)</label>
                    <input type="password" id="tokenqive" v-model="form.token_qive" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <span v-if="form.errors.token_qive" class="text-red-500 text-sm">{{ form.errors.token_qive }}</span>
                </div>
                 <div class="mb-4">
                    <label for="apiid" class="block text-gray-700 dark:text-white">API ID Tomador (Qive)</label>
                    <input type="password" id="apiid" v-model="form.api_id" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <span v-if="form.errors.api_id" class="text-red-500 text-sm">{{ form.errors.api_id }}</span>
                </div>
                <div class="mb-4">
                    <label for="apikey" class="block text-gray-700 dark:text-white">API Key Tomador (Qive)</label>
                    <input type="password" id="apikey" v-model="form.api_key" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <span v-if="form.errors.api_key" class="text-red-500 text-sm">{{ form.errors.api_key }}</span>
                </div>
                <hr class="my-4" />
                <div class="my-3">
                     <label class="inline-flex items-center mb-5 cursor-pointer">
                     <input v-model="form.accounting"
                      type="checkbox"
                      class="sr-only peer"
                      true-value="1"
                      false-value="0" />
                     <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
                     <span class="ms-3 text-sm font-medium text-gray-900 dark:text-white">Ativar o envio das notas fiscais para a contabilidade</span>
                     </label>
                     <div v-if="form.errors.accounting" class="text-red-500 text-sm">{{ form.errors.accounting }}</div>
                </div>
                <div v-show="form.accounting == 1 || form.accounting == true">
                    <p class="text-gray-900 font-extrabold mb-4">Para enviar as notas fiscais para a contabilidade, preencha os campos abaixo com as informações fornecidas pelo seu contador.</p>
                <div class="mb-4">
                    <label for="personal_integration_id_accountings" class="block text-gray-700 dark:text-white">Envio para contabilidade - X-Integration Fornecido Pelo Contador (Onvio)</label>
                    <input type="password" id="personal_integration_id_accountings" v-model="form.personal_integration_id_accountings" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <span v-if="form.errors.personal_integration_id_accountings" class="text-red-500 text-sm">{{ form.errors.personal_integration_id_accountings }}</span>
                </div>
                <div class="mb-4">
                    <label for="client_id_accountings" class="block text-gray-700 dark:text-white">Envio para contabilidade - Client ID (Onvio)</label>
                    <input type="password" id="client_id_accountings" v-model="form.client_id_accountings" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <span v-if="form.errors.client_id_accountings" class="text-red-500 text-sm">{{ form.errors.client_id_accountings }}</span>
                </div>
                <div class="mb-4">
                    <label for="client_key_accountings" class="block text-gray-700 dark:text-white">Envio para contabilidade - Client Key (Onvio)</label>
                    <input type="password" id="client_key_accountings" v-model="form.client_key_accountings" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <span v-if="form.errors.client_key_accountings" class="text-red-500 text-sm">{{ form.errors.client_key_accountings }}</span>
                </div>
                <div class="mb-4">
                    <label for="client_audience_accountings" class="block text-gray-700 dark:text-white">Envio para contabilidade - Audience ID (Onvio)</label>
                    <input type="password" id="client_audience_accountings" v-model="form.client_audience_accountings" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <span v-if="form.errors.client_audience_accountings" class="text-red-500 text-sm">{{ form.errors.client_audience_accountings }}</span>
                </div>
                <div class="mb-4">
                    <label for="access_token_accountings" class="block text-gray-700 dark:text-white">Envio para contabilidade - Access Token (Onvio)</label>
                    <input type="password" id="access_token_accountings" v-model="form.access_token_accountings" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <span v-if="form.errors.access_token_accountings" class="text-red-500 text-sm">{{ form.errors.access_token_accountings }}</span>
                    <div class="text-gray-900 font-extrabold text-sm">*Este campo é preenchido automaticamente. Só altere em caso de erro na geração do token.</div>
                </div>
                <div class="mb-4">
                    <label for="api_generate_integration_id_accountings" class="block text-gray-700 dark:text-white">Envio para contabilidade - IntegrationKey Gerado pela API (Onvio)</label>
                    <input type="password" id="api_generate_integration_id_accountings" v-model="form.api_generate_integration_id_accountings" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <span v-if="form.errors.api_generate_integration_id_accountings" class="text-red-500 text-sm">{{ form.errors.api_generate_integration_id_accountings }}</span>
                    <div class="text-gray-900 font-extrabold text-sm">*Este campo é preenchido automaticamente. Só altere em caso de erro na geração do IntegrationKey.</div>
                </div>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Salvar</button>
            </form>
        </div>
    </AppLayout>
</template>
