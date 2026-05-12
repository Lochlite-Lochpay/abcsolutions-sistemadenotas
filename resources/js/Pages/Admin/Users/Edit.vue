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
import { useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayoutAdmin.vue';

const dispatch = defineEmits(['toast']);
const toast = ref({ type: '', message: '' });
const emit = (event, data) => {
    dispatch(event, data);
    toast.value = data;
};

const { props } = usePage();
const id = props.id;
const user = props.user;
const form = useForm({
    _method: 'PUT',
    id: props.user.id ?? id,
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    emit('toast', { type: 'connect', message: 'Iniciando conexão...' });
    form.post(route('dashboard.admin.users.update', props.user.id), {
        onStart: () => {
                emit('toast', { type: 'connect', message: 'Atualizando usuário...' });
                console.log('Atualizando usuário...');
            },
            onSuccess: () => {
                emit('toast', { type: 'success', message: 'Usuário atualizado com sucesso!' });
                console.log('Usuário atualizado com sucesso!');
            },
            onError: (e) => {
                emit('toast', { type: 'danger', message: 'Falha ao salvar usuário!' });
                console.error('Falha ao salvar usuário!', e);
            },
            onFinish: () => {
                form.id = '';
                //emit('toast', { type: 'info', message: 'Operação concluida' });
                console.log('Operação concluida');
            },
    });
};
</script>
<template>
    <AppLayout :toast="toast">
        <div class="container mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">Editar Usuário</h1>
            <form @submit.prevent="submit" class="bg-white dark:bg-gray-700 dark:text-white p-6 rounded shadow-md">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 dark:text-white">Nome</label>
                    <input type="text" id="name" v-model="form.name" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <span v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</span>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 dark:text-white">E-mail</label>
                    <input type="email" id="email" v-model="form.email" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <span v-if="form.errors.email" class="text-red-500 text-sm">{{ form.errors.email }}</span>
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 dark:text-white">Senha</label>
                    <input type="password" id="password" v-model="form.password" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <span v-if="form.errors.password" class="text-red-500 text-sm">{{ form.errors.password }}</span>
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-gray-700 dark:text-white">Confirme a senha</label>
                    <input type="password" id="password_confirmation" v-model="form.password_confirmation" class="dark:bg-gray-300 dark:text-gray-800 mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <span v-if="form.errors.password_confirmation" class="text-red-500 text-sm">{{ form.errors.password_confirmation }}</span>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">Salvar</button>
            </form>
        </div>
    </AppLayout>
</template>