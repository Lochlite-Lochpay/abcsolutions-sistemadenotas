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
import axios from 'axios';

const dispatch = defineEmits(['toast', 'fetch']);
const toast = ref({ type: '', message: '' });
const emit = (event, data) => {
    dispatch(event, data);
    toast.value = data;
};

const props = defineProps({
    paginate: Object,
    type: String,
});

 const paginate = ref(props.paginate);

const fetch = async (url) => {
    emit('toast', { type: 'connect', message: 'Carregando...' });
    const response = await axios.get(url).then(response => {
        if (props.type === 'boxes') {
            paginate.value = response.data.boxes;
        } else if (props.type === 'roulettes') {
            paginate.value = response.data.roulettes;
        } else if (props.type === 'users') {
            paginate.value = response.data.users;    
        } else if (props.type === 'orders') {
            paginate.value = response.data.orders;
        } else if (props.type === 'products') {
            paginate.value = response.data.products;
        } else if (props.type === 'news') {
            paginate.value = response.data.news;
        } else if (props.type === 'contacts') {
            paginate.value = response.data.contacts;
        } else if (props.type === 'awards') {
            paginate.value = response.data.awards;
        } else if (props.type === 'cashbacks') {
            paginate.value = response.data.cashbacks;
        } else if (props.type === 'affiliates') {
            paginate.value = response.data.affiliates;
        } else if (props.type === 'withdraws') {
            paginate.value = response.data.withdraws;
        } else if (props.type === 'slides') {
            paginate.value = response.data.slides;
        } else if (props.type === 'gateways') {
            paginate.value = response.data.gateways;
        } else {
            paginate.value = response.data;
        }
        emit('toast', { type: '', message: '' }); // Clear toast
        emit('fetch', paginate.value);
    }).catch(error => {
        emit('toast', { type: 'danger', message: 'Falha ao carregar!' });
        console.error(error);
    });
};

const paginationRange = computed(() => {
    const totalPages = paginate.value.last_page;
    const currentPage = paginate.value.current_page;
    const range = [];

    for (let i = 1; i <= totalPages; i++) {
        range.push(i);
    }

    return range;
});

const getPageUrl = (page) => {
    return `${paginate.value.path}?page=${page}`;
};
</script>
<template>
<div class="my-4 flex justify-center items-center" v-if="paginate.prev_page_url || paginate.next_page_url">
<button 
    v-if="paginate.prev_page_url" 
    @click="fetch(paginate.prev_page_url)" 
    class="bg-gray-300 text-gray-700 px-4 py-2 me-2 rounded hover:bg-gray-400"
>
    Anterior
</button>
<button 
    v-for="page in paginationRange" 
    :key="page" 
    @click="fetch(getPageUrl(page))" 
    :class="['px-4 py-2 mx-2 rounded', { 'bg-blue-500 text-white': page === paginate.current_page, 'bg-gray-300 text-gray-700 hover:bg-gray-400': page !== paginate.current_page }]"
>
    {{ page }}
</button>
<button 
    v-if="paginate.next_page_url" 
    @click="fetch(paginate.next_page_url)" 
    class="bg-gray-300 text-gray-700 px-4 py-2 ms-2 rounded hover:bg-gray-400"
>
    Próximo
</button>
</div>
</template>
