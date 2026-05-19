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
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { Link, usePage, useForm } from '@inertiajs/vue3';

const dispatch = defineEmits(['toast']);
const toast = ref({ type: '', message: '' });
const emit = (event, data) => {
  dispatch(event, data);
  toast.value = data;
};

const props = defineProps({
  company: Object,
  numero: String,
  tomador: String,
  prestador: String,
  notas: Object,
});

const formsearch = useForm({
  numero: props.numero || '',
  tomador: props.tomador || '',
  prestador: props.prestador || '',
});

const submit = () => {
  emit('toast', { type: 'connect', message: 'Iniciando conexão...' });
  formsearch.get(route('dashboard.nfse.index', {id: props.company.id}), {
    onStart: () => {
      emit('toast', { type: 'connect', message: 'Buscando notas...' });
      console.log('Buscando notas...');
    },
    onSuccess: () => {
      emit('toast', { type: 'success', message: 'Notas buscadas com sucesso!' });
      console.log('Notas buscadas com sucesso!');
    },
    onError: () => {
      emit('toast', { type: 'danger', message: 'Falha ao buscar notas!' });
      console.error('Falha ao buscar notas!');
    },
    onFinish: () => {
      console.log('Operação concluida');
    },
  });
};
</script>

<template>
  <AppLayout title="NFS-e" :toast="toast">
  <div class="w-full">
    <div class="w-full max-w-screen mx-auto">
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
      <template v-if="notas?.data?.length">
      <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-separate border-spacing-0.5 bg-white">
        <thead>
          <tr class="bg-gray-100 text-left text-sm font-semibold text-gray-700">
          <th class="px-4 py-2">Nota</th>
          <th class="px-4 py-2">ID de Envio</th>
          <th class="px-4 py-2">Tomador</th>
          <th class="px-4 py-2">CPF/CNPJ do Tomador</th>
          <th class="px-4 py-2">Prestador</th>
          <th class="px-4 py-2">CPF/CNPJ do Prestador</th>
          <th class="px-4 py-2">Enviada ?</th>
          <th class="px-4 py-2">Data do envio</th>
          <th class="px-4 py-2">Detalhes</th>
          </tr>
        </thead>
        <tbody id="accordion-collapse" data-accordion="collapse">
          <template v-for="(nota, i) in notas.data" :key="i">
          <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
            <td class="px-4 py-2 text-sm text-gray-700">
            {{ nota?.number }}
            </td>
            <td class="px-4 py-2 text-sm text-gray-700">
            {{ nota?.sending_id_accountings ?? "Indisponivel" }}
            </td>
            <td class="px-4 py-2 text-sm text-gray-700">
            {{ nota?.service_taker }}
            </td>
            <td class="px-4 py-2 text-sm text-gray-700">
            {{ nota?.service_taker_document }}
            </td>
            <td class="px-4 py-2 text-sm text-gray-700">
            {{ nota?.service_provider }}
            </td>
            <td class="px-4 py-2 text-sm text-gray-700">
            {{ nota?.service_provider_document }}
            </td>
            <td class="px-4 py-2 text-sm text-gray-700">
            <span v-if="nota?.accountings" class="text-green-500 font-bold">Sim</span>
            <span v-else class="text-red-500 font-bold">Não</span>
            </td>
            <td class="px-4 py-2 text-sm text-gray-700">
            {{ new Date(nota?.created_at).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' }) }}
            </td>
            <td class="px-4 py-2 text-sm text-gray-700">
            <button type="button" class="flex items-center justify-center items-center text-center w-full font-bold rtl:text-right text-gray-500 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-800 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 gap-3" :data-accordion-target="'#accordion-collapse-body-' + i" aria-expanded="false" :aria-controls="'#accordion-collapse-body-' + i">
              <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5 5 1 1 5" />
              </svg>
            </button>
            </td>
          </tr>
          <tr :id="'accordion-collapse-body-' + i" class="hidden" :aria-labelledby="'accordion-collapse-heading-' + i">
            <td colspan="8">
            <div class="py-2">
              <div class="border-t border-gray-200 pt-2 px-4">
              <div class="max-w-lg w-full text-break word-break">
                <strong>Resposta da API:</strong>
                <code class="max-w-lg w-full text-break word-break">{{ JSON.parse(nota?.accountings_response) }}</code>
                <br />
                <Link :href="nota?.sending_id_accountings ? route('dashboard.accounting.checkSend', { company: props.company.id, invoice: nota.id, id: nota?.sending_id_accountings }) : '#nota-sem-o-id-de-envio'" :disabled="!nota?.sending_id_accountings" :class="{'opacity-50 cursor-not-allowed': !nota?.sending_id_accountings}" class="mt-4 text-blue-800 font-bold hover:underline">Clique aqui para Atualizar a resposta da API / Verificar o status do envio</Link>
              </div>
              </div>
            </div>
            </td>
          </tr>
          </template>
        </tbody>
        </table>
      </div>

      <!-- Paginação -->
      <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200 bg-white">
        <div class="text-sm text-gray-600">
          Exibindo {{ notas.from }}–{{ notas.to }} de {{ notas.total }} notas
        </div>
        <div class="flex items-center gap-1">
          <template v-for="link in notas.links" :key="link.label">
            <Link
              v-if="link.url"
              :href="link.url"
              preserve-scroll
              class="px-3 py-1.5 rounded text-sm border"
              :class="link.active
                ? 'bg-blue-600 text-white border-blue-600 font-semibold'
                : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50'"
              v-html="link.label"
            />
            <span
              v-else
              class="px-3 py-1.5 rounded text-sm border border-gray-200 text-gray-400 cursor-not-allowed bg-gray-50"
              v-html="link.label"
            />
          </template>
        </div>
      </div>

      </template>
      <template v-else>
      <div>
        <p class="text-gray-600">
        Nenhuma nota fiscal encontrada.
        </p>
      </div>
      </template>
    </div>
    </div>
  </div>
  </AppLayout>
</template>
