<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
  disckusage: String
    });
const currentDate = ref(new Date());
    const hours = ref(currentDate.value.getHours());
    let greeting = ref('');

    const updateGreeting = () => {
        if (hours.value < 12) {
            greeting.value = 'Bom dia';
        } else if (hours.value < 18) {
            greeting.value = 'Boa tarde';
        } else {
            greeting.value = 'Boa noite';
        }
    };

    const updateTime = () => {
        currentDate.value = new Date();
        hours.value = currentDate.value.getHours();
        updateGreeting();
    };

    onMounted(() => {
        updateGreeting();
        setInterval(updateTime, 1000);
    });
const formattedDate = computed(() => {
        return currentDate.value.toLocaleDateString('pt-BR', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        });
    });
</script>

<template>
    <div>
        <div class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div class="flex items-center text-star">
                    <ApplicationLogo class="block h-16 w-auto" />
                    <div class="ms-3 flex flex-col text-start">
                        <div class="text-start md:pe-4 md:me-4 text-sm font-semibold whitespace-nowrap dark:text-white">
                            {{ greeting }}, {{  $page?.props?.auth?.user?.name }}! Hoje é {{ formattedDate }}
                        </div>
                        <p class="text-sm font-normal text-gray-500 dark:text-gray-400">Instalado em: {{ new Date($page?.props?.settings?.created_at).toLocaleDateString() }} | Versão: 1.0</p>
                    </div>
                </div>
                <div class="flex items-center"></div>
            </div>

            <div class="text-gray-700 body-font">
  <div class="container px-5 py-10 mx-auto">
   
    <div class="grid gap-2 lg:grid-cols-8 -m-4 text-center">
      <Link :href="route('dashboard.admin.home')" class="p-3 w-full">
        <div class="border border-gray-400 px-3 py-3 rounded-lg transform transition duration-500 hover:scale-110">
          <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" class="shrink-0 w-6 h-6 text-orange-500 transition duration-75 dark:text-orange-400 group-hover:text-gray-900 dark:group-hover:text-white inline-block mb-3" viewBox="0 0 24 24">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"></path>
          </svg>
          <h2 class="title-font font-medium text-lg text-gray-900 dark:text-white">Usuários</h2>
        </div>
      </Link>

    </div>
  </div>
</div>
        
<div class="my-4 flex flex-col md:flex-row justify-between w-full p-4 bg-white border border-gray-100 rounded-lg shadow-sm dark:bg-gray-700 dark:border-gray-600">
    <div class="flex flex-col items-start mb-3 me-4">
        <div class="text-start mb-2 md:pe-4 md:me-4 md:mb-0 text-lg font-semibold whitespace-nowrap dark:text-white">
            Espaço em disco
        </div>
        <p class="flex items-center text-sm font-normal text-gray-500 dark:text-gray-400">Essa é uma estimativa da porcentagem atualmente usada para armazenar o sistema, além de documentos, áudios, vídeos e imagens contidas nele ou adicionadas via upload. Essa informação não substitui aquela fornecida pela empresa de hospedagem podendo haver distinção entre as estimativas.</p>

        <div class="w-full bg-gray-200 rounded-full dark:bg-gray-700 mt-2">
              <div class="bg-orange-500 text-xs font-bold text-orange-100 text-center p-0.5 leading-none rounded-full" :style="'width: ' + Number(disckusage).toFixed(2) + '%!important'"> {{ Number(disckusage).toFixed(2) }}%</div>
        </div>
    </div>
    <div class="flex items-center shrink-0">
        <button class="px-5 py-2 me-2 text-xs font-medium text-white bg-orange-500 rounded-lg hover:bg-orange-600 focus:ring-4 focus:ring-orange-300 dark:bg-orange-400 dark:hover:bg-orange-500 focus:outline-none dark:focus:ring-orange-600" type="button" data-drawer-target="free-up-disk-space" data-drawer-show="free-up-disk-space" data-drawer-placement="right" aria-controls="free-up-disk-space">Liberar Espaço</button>
    </div>
</div>

<!-- drawer component -->
<div id="free-up-disk-space" class="fixed top-0 right-0 z-50 h-screen p-4 overflow-y-auto transition-transform translate-x-full bg-white w-80 dark:bg-gray-800" tabindex="-1" aria-labelledby="free-up-disk-space-label">
    <h5 id="free-up-disk-space-label" class="inline-flex items-center mb-4 text-base font-semibold text-gray-500 dark:text-gray-400"><svg class="w-4 h-4 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"/>
  </svg>Como liberar espaço em disco</h5>
   <button type="button" data-drawer-hide="free-up-disk-space" aria-controls="free-up-disk-space" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center dark:hover:bg-gray-600 dark:hover:text-white" >
      <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
         <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
      </svg>
      <span class="sr-only">Close menu</span>
   </button>
   <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Para liberar espaço em disco, você pode excluir imagens não mais usadas na pasta storage/app/public/images que se localiza no diretorio do backend/api. Essa pasta pode ser acessada no gerenciador de arquivos ou FTP do seu serviço de hospedagem.</p>
</div>

<div v-if="false" class="grid gap-2 lg:grid-cols-3 my-4 -m-4 mx-auto">

<div class="flex items-start w-full gap-4 rounded-lg bg-white p-4 shadow-md ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
<div class="flex size-24 shrink-0 self-center items-center justify-center rounded-lg sm:size-12">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-12">
 <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" />
 <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z" />
</svg>
</div>
<div class="pt-3 sm:pt-5">
<h2 class="text-md font-semibold text-black dark:text-white">Explore the documentation</h2>
<p class="text-sm/relaxed">
Laravel has wonderful documentation covering every aspect of the framework
</p>
</div>
<svg class="ms-auto end-0 size-6 shrink-0 self-center stroke-[#FF2D20]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
</svg>
</div>

<div class="flex items-start w-full gap-4 rounded-lg bg-white p-4 shadow-md ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
<div class="flex size-24 shrink-0 self-center items-center justify-center rounded-lg sm:size-12">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-12">
 <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" />
 <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z" />
</svg>
</div>
<div class="pt-3 sm:pt-5">
<h2 class="text-md font-semibold text-black dark:text-white">Explore the documentation</h2>
<p class="text-sm/relaxed">
Laravel has wonderful documentation covering every aspect of the framework
</p>
</div>
<svg class="ms-auto end-0 size-6 shrink-0 self-center stroke-[#FF2D20]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
</svg>
</div>

<div class="flex items-start w-full gap-4 rounded-lg bg-white p-4 shadow-md ring-1 ring-white/[0.05] transition duration-300 hover:text-black/70 hover:ring-black/20 focus:outline-none focus-visible:ring-[#FF2D20] dark:bg-zinc-900 dark:ring-zinc-800 dark:hover:text-white/70 dark:hover:ring-zinc-700 dark:focus-visible:ring-[#FF2D20]">
<div class="flex size-24 shrink-0 self-center items-center justify-center rounded-lg sm:size-12">
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-12">
 <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" />
 <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z" />
</svg>
</div>
<div class="pt-3 sm:pt-5">
<h2 class="text-md font-semibold text-black dark:text-white">Explore the documentation</h2>
<p class="text-sm/relaxed">
Laravel has wonderful documentation covering every aspect of the framework
</p>
</div>
<svg class="ms-auto end-0 size-6 shrink-0 self-center stroke-[#FF2D20]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5">
<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" />
</svg>
</div>


</div>



            <h1 class="mt-8 text-xl font-medium text-gray-900 dark:text-white">
                Arquitetura do sistema
            </h1>

            <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
                Este site foi construído usando o Laravel Framework em conjunto com o pacote Jetstream e InertiaJS. 
                <br>
                O Laravel foi projetado para construir aplicativos web modernos usando um ambiente de desenvolvimento relativamente simples, poderoso e agradável.
                Isso torna fácil manter um sistema estável e seguro a longo prazo, enquanto também permite um maior nível de personalização e adição de recursos.
            </p>
        </div>
        <div class="bg-gray-200 dark:bg-gray-800 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
            <div>
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="size-6 stroke-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                <h2 class="ms-3 text-xl font-semibold text-gray-900 dark:text-white">
                <a href="https://laravel.com/docs">Documentação do Laravel</a>
                </h2>
            </div>

            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                O Laravel possui uma documentação maravilhosa que cobre todos os aspectos do framework. Se você é novo no framework ou já tem experiência, recomendamos ler toda a documentação do início ao fim.
            </p>

            <p class="mt-4 text-sm">
                <a href="https://laravel.com/docs" class="inline-flex items-center font-semibold text-indigo-700 dark:text-indigo-300">
                Explore a documentação

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ms-1 size-5 fill-indigo-500 dark:fill-indigo-200">
                    <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                </svg>
                </a>
            </p>
            </div>

            <div>
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="size-6 stroke-gray-400">
                <path stroke-linecap="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <h2 class="ms-3 text-xl font-semibold text-gray-900 dark:text-white">
                <a href="https://laracasts.com">Laracasts</a>
                </h2>
            </div>

            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                O Laracasts oferece milhares de tutoriais em vídeo sobre desenvolvimento Laravel, PHP e JavaScript. Confira, veja por si mesmo e aumente massivamente suas habilidades de desenvolvimento no processo.
            </p>

            <p class="mt-4 text-sm">
                <a href="https://laracasts.com" class="inline-flex items-center font-semibold text-indigo-700 dark:text-indigo-300">
                Comece a assistir Laracasts

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ms-1 size-5 fill-indigo-500 dark:fill-indigo-200">
                    <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                </svg>
                </a>
            </p>
            </div>

            <div>
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="size-6 stroke-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                <h2 class="ms-3 text-xl font-semibold text-gray-900 dark:text-white">
                <a href="https://tailwindcss.com/">Tailwind</a>
                </h2>
            </div>

            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                O Laravel Jetstream é construído com Tailwind, uma incrível estrutura CSS de utilidade que não atrapalha. Você ficará surpreso com a facilidade com que pode construir e manter designs modernos e frescos com essa maravilhosa estrutura ao seu alcance.
            </p>
            </div>

            <div>
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="size-6 stroke-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                <h2 class="ms-3 text-xl font-semibold text-gray-900 dark:text-white">
                Autenticação
                </h2>
            </div>

            <p class="mt-4 text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                As visualizações de autenticação e registro estão incluídas no Laravel Jetstream, bem como suporte para verificação de e-mail do usuário e redefinição de senhas esquecidas. Então, você está livre para começar com o que mais importa: construir seu aplicativo.
            </p>
            </div>
        </div>
    </div>
</template>
