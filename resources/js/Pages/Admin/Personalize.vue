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
import { ref, computed, watch } from 'vue';
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
const settings = ref(props.settings);

const form = useForm({
    '_method': 'PUT',
    name: settings.value.name,
    logo: null,
    auth_logo: null,
    favicon: null,
    night: settings.value.night,
    auth_social_media: settings.value.auth_social_media,
    auth_client_id: settings.value.auth_client_id,  
    auth_client_secret: settings.value.auth_client_secret,
});

const imageChanged = ref(null);
const image = computed(() => imageChanged.value || form.logo || (settings.value.logo == '/logo.jpg' ? settings.value.logo : '/storage/' + settings.value.logo));
const imageAuthChanged = ref(null);
const imageAuth = computed(() => imageAuthChanged.value || form.auth_logo || (settings.value.auth_logo == '/logo.jpg' ? settings.value.auth_logo : '/storage/' + settings.value.auth_logo));
const isDarkMode = computed(() => form.night);

watch(isDarkMode, (newValue) => {
    console.log('Dark mode changed to:', newValue);
});

const changeImage = (event) => {
    var file = event.target.files[0];
    var reader = new FileReader();
    reader.onload = function(e) { 
        console.log('Image changed:', e.target.result);
        imageChanged.value = e.target.result;
    };
    reader.readAsDataURL(file);   

};

const changeImageAuth = (event) => {
    var file = event.target.files[0];
    var reader = new FileReader();
    reader.onload = function(e) { 
        console.log('Image changed:', e.target.result);
        imageAuthChanged.value = e.target.result;
    };
    reader.readAsDataURL(file);   

};

const saveSettings = () => {
   console.log('Saving settings:', form.data());
   emit('toast', { type: 'connect', message: 'Iniciando conexão...' });
   form.post(route('dashboard.admin.settings.update'), {
            onStart: () => {
            emit('toast', { type: 'connect', message: 'Salvando configurações...' });
            console.log('Salvando configurações...');
            },
            onSuccess: () => {
            emit('toast', { type: 'success', message: 'Configurações salvas com sucesso!' });
            console.log('Configurações salvas com sucesso!');
            setTimeout(() => {
                window.location.reload();
            }, 3000);
            },
            onError: () => {
            emit('toast', { type: 'danger', message: 'Falha ao salvar configurações!' });
            console.error('Falha ao salvar configurações!');
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
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight w-full md:w-1/2">
                Configurações disponiveis
            </h2>
        </template>             
        <div class="w-full mt-4">
            <form @submit.prevent="saveSettings" method="post" class="bg-white dark:bg-gray-700 dark:text-white p-6 rounded shadow-md">
                <input type="hidden" name="_method" value="PUT">
                <div class="mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="user_name">Nome da empresa</label>
                    <input v-model="form.name" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-white dark:text-white" id="user_name" type="text">
                    <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
                </div>
                
                <div class="flex flex-wrap md:flex-nowrap justify-between items-center">
                <img class="w-24 h-24 rounded-full mx-auto" :src="image ?? 'https://ui-avatars.com/api/?name=' + settings.name + '&color=7F9CF5&background=EBF4FF'" alt="Logo">
                <div class="max-w-lg mx-auto">
                     <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="logo">Upload da logo</label>
                     <input @input="(event) => {form.logo = event.target.files[0]; changeImage(event);}" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-white focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-white" aria-describedby="logo_help" id="logo" type="file">
                     <div v-if="form.errors.logo" class="text-red-500 text-sm">{{ form.errors.logo }}</div>
                     <div class="mt-1 text-sm text-gray-500 dark:text-white" id="logo_help">Essa é a imagem que aparece no menu.</div>
                </div>
                <img class="w-24 h-24 rounded-full mx-auto" :src="imageAuth ?? 'https://ui-avatars.com/api/?name=' + settings.name + '&color=7F9CF5&background=EBF4FF'" alt="Auth Logo">
                <div class="max-w-lg mx-auto">
                     <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="auth_logo">Upload da logo de autenticação</label>
                     <input @input="(event) => {form.auth_logo = event.target.files[0]; changeImageAuth(event);}" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-white focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-white" aria-describedby="auth_logo_help" id="auth_logo" type="file">
                     <div v-if="form.errors.auth_logo" class="text-red-500 text-sm">{{ form.errors.auth_logo }}</div>
                     <div class="mt-1 text-sm text-gray-500 dark:text-white" id="auth_logo_help">Essa é a imagem que aparece na tela de login.</div>
                </div>
                </div>
                <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                     {{ form.progress.percentage }}%
                </progress>
                <div class="mt-6 border-t border-gray-200 dark:border-gray-400"></div>
                <div class="my-6">
                <label class="inline-flex items-center mb-5 cursor-pointer">
                     <input v-model="form.night"
                      type="checkbox"
                      class="sr-only peer"
                      true-value="1"
                      false-value="0" />
                     <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
                     <span class="ms-3 text-sm font-medium text-gray-900 dark:text-white">Exibir o botão de modo escuro</span>
                </label>
                <div v-if="form.errors.night" class="text-red-500 text-sm">{{ form.errors.night }}</div>
                </div>
                <div class="my-4">
                <label class="inline-flex items-center mb-5 cursor-pointer">
                     <input v-model="form.auth_social_media"
                      type="checkbox"
                      class="sr-only peer"
                      true-value="1"
                      false-value="0" />
                     <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:w-5 after:h-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600"></div>
                     <span class="ms-3 text-sm font-medium text-gray-900 dark:text-white">Ativar o login com rede social</span>
                </label>
                <div v-if="form.errors.auth_social_media" class="text-red-500 text-sm">{{ form.errors.auth_social_media }}</div>
                </div>
                <div class="my-4" v-if="form.auth_social_media == 1"> 
                    <div id="alert-additional-content-1" class="p-4 mb-5 text-blue-800 border border-blue-300 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400 dark:border-blue-800" role="alert">
  <div class="flex items-center">
    <svg class="shrink-0 w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
      <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
    </svg>
    <span class="sr-only">Info</span>
    <h3 class="text-lg font-medium">Pré-requisito para uso</h3>
  </div>
  <div class="mt-2 mb-4 text-sm">
    Para utilizar o login com rede social, você precisa criar um projeto no Google Cloud e gerar um Client ID e Client Secret para uso do Google Auth Platform. Siga as instruções <a href="https://developers.google.com/identity/protocols/oauth2/web-server#creatingcred" class="text-blue-600 hover:underline dark:text-blue-500">aqui</a> para criar seu projeto e obter as credenciais necessárias.
  </div>
  <div class="flex">
    <a href="https://console.cloud.google.com/auth/clients?invt=AbutCA" target="_blank" class="text-white bg-blue-800 hover:bg-blue-900 focus:ring-4 focus:outline-none focus:ring-blue-200 font-medium rounded-lg text-xs px-3 py-1.5 me-2 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
      <svg class="me-2 h-3 w-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 14">
        <path d="M10 0C4.612 0 0 5.336 0 7c0 1.742 3.546 7 10 7 6.454 0 10-5.258 10-7 0-1.664-4.612-7-10-7Zm0 10a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"/>
      </svg>
      Ir para o Google Cloud
    </a>
  </div>
</div>                     
                    <div class="relative z-0 w-full mb-5 group">
                        <input type="text" name="floating_client" v-model="form.auth_client_id" id="floating_client" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                        <label for="floating_client" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Google Client ID</label>
                    </div>
                    <div class="relative z-0 w-full mb-5 group">
                       <input type="text" name="floating_secret" v-model="form.auth_client_secret" id="floating_secret" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                        <label for="floating_secret" class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">Google Client Secret</label>
                    </div>
                </div>    
                <button type="submit" :disabled="form.processing" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">{{ form.processing ? 'Salvando...' :  'Salvar' }}</button>
            </form>
        </div>
    </AppLayout>
</template>