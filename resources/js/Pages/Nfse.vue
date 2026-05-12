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
    nfse: Object,
    success: Boolean,
    message: String,
    webserver: String,
    code: Number,
    notasarray: Array,
    notas: String,
    nfse_numero_inicial: String,
    nfse_numero_final: String,
    rps_numero: String,
    rps_serie: String,
    rps_tipo: String,
    data_emissao_inicial: String,
    data_emissao_final: String,
    data_competencia_inicial: String,
    data_competencia_final: String,
    tomador_cnpj: String,
    tomador_cpf: String,
    tomador_im: String,
    intermediario_cnpj: String,
    intermediario_cpf: String,
    intermediario_im: String,
    nfse_numero: String,
});

const formsearch = useForm({
  webserver: 'prestadas',
  nfse_numero: props.nfse_numero || '',
  nfse_numero_inicial: props.nfse_numero_inicial || '',
  nfse_numero_final: props.nfse_numero_final || '',
  rps_numero: props.rps_numero || '',
  rps_serie: props.rps_serie || '',
  rps_tipo: props.rps_tipo || '',
  data_emissao_inicial: props.data_emissao_inicial || new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
  data_emissao_final: props.data_emissao_final || new Date().toISOString().split('T')[0],
  data_competencia_inicial: props.data_competencia_inicial || new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
  data_competencia_final: props.data_competencia_final || new Date().toISOString().split('T')[0],
  tomador_cnpj: props.tomador_cnpj || '',
  tomador_cpf: props.tomador_cpf || '',
  tomador_im: props.tomador_im || '',
  intermediario_cnpj: props.intermediario_cnpj || '',
  intermediario_cpf: props.intermediario_cpf || '',
  intermediario_im: props.intermediario_im || ''
});

const formsearchreceived = useForm({
  webserver: 'tomadas',
  document: props.document || ''
});

const activePartyRole = props.webserver === 'tomadas' ? 'emitter' : 'taker';

const formatMoney = (value) => {
  if (value === null || value === undefined || value === '') {
    return '-';
  }

  const number = Number(value);
  if (Number.isNaN(number)) {
    return value;
  }

  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(number);
};

const safeJson = (value) => {
  if (value === null || value === undefined) {
    return '-';
  }

  if (typeof value === 'string') {
    return value;
  }

  try {
    return JSON.stringify(value, null, 2);
  } catch (error) {
    return String(value);
  }
};

const toArray = (value) => (Array.isArray(value) ? value : (value ? [value] : []));

const noteJson = (nota) => nota?.jsonDocument || nota?.raw || {};

const noteNumber = (nota) => {
  return (
    nota?.number ||
    noteJson(nota)?.Nfse?.InfNfse?.Numero ||
    noteJson(nota)?.numero ||
    noteJson(nota)?.number ||
    nota?.id ||
    '-'
  );
};

const noteValue = (nota) => {
  return (
    nota?.value ||
    noteJson(nota)?.Nfse?.InfNfse?.ValoresNfse?.ValorLiquidoNfse ||
    noteJson(nota)?.Nfse?.InfNfse?.ValoresNfse?.ValorServicos ||
    noteJson(nota)?.value ||
    '-'
  );
};

const noteEmissionDate = (nota) => {
  return (
    nota?.emissionDate ||
    noteJson(nota)?.Nfse?.InfNfse?.DataEmissao ||
    noteJson(nota)?.emissionDate ||
    null
  );
};

const partyFromNota = (nota, role) => {
  return role === 'emitter'
    ? (nota?.emitter || noteJson(nota)?.Nfse?.InfNfse?.PrestadorServico || {})
    : (nota?.taker || noteJson(nota)?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico || {});
};

const partyDocument = (party) => {
  return (
    party?.CpfCnpj?.Cnpj ||
    party?.CpfCnpj?.Cpf ||
    party?.IdentificacaoTomador?.CpfCnpj?.Cnpj ||
    party?.IdentificacaoTomador?.CpfCnpj?.Cpf ||
    party?.cnpj ||
    party?.cpf ||
    '-'
  );
};

const partyName = (party) => {
  return party?.RazaoSocial || party?.NomeFantasia || party?.name || '-';
};

const noteXmlHref = (nota) => {
  const xml = nota?.xmlBase64 || nota?.xml || null;
  return xml ? `data:text/xml;base64,${xml}` : null;
};

const noteJsonPretty = (nota) => safeJson(noteJson(nota));

const submit = () => {
  emit('toast', { type: 'connect', message: 'Iniciando conexão...' });
    formsearch.get(route('dashboard.nfse.index', {home: props.company.id}), {
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
  }
const submitreceived = () => {
  emit('toast', { type: 'connect', message: 'Iniciando conexão...' });
    formsearchreceived.get(route('dashboard.nfse.index', {home: props.company.id}), {
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
  }
</script>

<template>
  <AppLayout title="NFS-e" :toast="toast">
    <div class="w-full">
      <div class="w-full max-w-screen mx-auto">
        <div
          class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg"
        >
          <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
            <ul
              class="flex flex-wrap -mb-px text-sm font-medium text-center"
              id="default-tab"
              data-tabs-toggle="#default-tab-content"
              role="tablist"
            >
              <li class="me-2" role="presentation">
                <Link
                  :href="route('dashboard.nfse.index', {home: props.company.id, webserver: 'prestadas'})"
                  class="inline-block p-4 border-b-2 rounded-t-lg"
                  id="profile-tab"
                  data-tabs-target="#profile"
                  type="button"
                  role="tab"
                  aria-controls="profile"
                  :aria-selected="webserver == 'prestadas' ? true : false"
                  >Prestadas</Link
                >
              </li>
              <li class="me-2" role="presentation">
                <Link
                  :href="route('dashboard.nfse.index', {home: props.company.id, webserver: 'tomadas'})"
                  class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300"
                  id="dashboard-tab"
                  data-tabs-target="#dashboard"
                  type="button"
                  role="tab"
                  aria-controls="dashboard"
                  :aria-selected="webserver == 'tomadas' ? true : false"
                  >Tomadas</Link
                >
              </li>
            </ul>
          </div>
          <div id="default-tab-content">
            <div
              class="hidden p-4 rounded-lg bg-white dark:bg-gray-800"
              id="profile"
              role="tabpanel"
              aria-labelledby="profile-tab"
            >
              <div>
                <div
                  class="p-6 lg:p-8 bg-white dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700"
                >
                  <form
                    class="w-full mx-auto mb-16"
                    data-accordion="collapse"
                    @submit.prevent="submit"
                  >
                    <div class="max-w-lg mx-auto flex mb-4">
                      <label
                        for="default-search"
                        class="me-3 mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white"
                        >Search</label
                      >
                      <div class="relative w-full">
                        <div
                          class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none"
                        >
                          <svg
                            class="w-4 h-4 text-gray-500 dark:text-gray-400"
                            aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 20 20"
                          >
                            <path
                              stroke="currentColor"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"
                            />
                          </svg>
                        </div>
                        <input
                          type="search"
                          id="default-search"
                          v-model="formsearch.nfse_numero"
                          class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                          placeholder="Localizar nota pelo número..."
                        />
                        <button
                          type="submit"
                          class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                        >
                          Buscar
                        </button>
                      </div>
                      <button
                        type="button"
                        data-accordion-target="#accordion-collapse-filter"
                        aria-expanded="false"
                        aria-controls="accordion-collapse-filter"
                        class="text-blue-700 bg-white hover:text-white hover:bg-blue-400 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm h-14 px-3 py-1 ms-3 dark:bg-gray-600 dark:hover:bg-blue-400 dark:focus:ring-blue-800"
                      >
                        Filtro
                      </button>
                    </div>
                    <div
                      id="accordion-collapse-filter"
                      class="hidden"
                      aria-labelledby="accordion-collapse-filter"
                    >
                      <div
                        class="p-5 border-gray-200 dark:border-gray-700 dark:bg-gray-900"
                      >
                        <div
                          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                        >
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.nfse_numero_inicial"
                              type="text"
                              name="nfse_numero_inicial"
                              id="nfse_numero_inicial"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="nfse_numero_inicial"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >NFS-e Número Inicial</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.nfse_numero_final"
                              type="text"
                              name="nfse_numero_final"
                              id="nfse_numero_final"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="nfse_numero_final"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >NFS-e Número Final</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.rps_numero"
                              type="text"
                              name="rps_numero"
                              id="rps_numero"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="rps_numero"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >RPS Número</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.rps_serie"
                              type="text"
                              name="rps_serie"
                              id="rps_serie"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="rps_serie"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >RPS Série</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.rps_tipo"
                              type="text"
                              name="rps_tipo"
                              id="rps_tipo"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="rps_tipo"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >RPS Tipo</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.data_emissao_inicial"
                              type="date"
                              name="data_emissao_inicial"
                              id="data_emissao_inicial"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="data_emissao_inicial"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >Data Emissão Inicial</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.data_emissao_final"
                              type="date"
                              name="data_emissao_final"
                              id="data_emissao_final"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="data_emissao_final"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >Data Emissão Final</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.data_competencia_inicial"
                              type="date"
                              name="data_competencia_inicial"
                              id="data_competencia_inicial"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="data_competencia_inicial"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >Data Competência Inicial</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.data_competencia_final"
                              type="date"
                              name="data_competencia_final"
                              id="data_competencia_final"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="data_competencia_final"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >Data Competência Final</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.tomador_cnpj"
                              type="text"
                              name="tomador_cnpj"
                              id="tomador_cnpj"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="tomador_cnpj"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >Tomador CNPJ</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.tomador_cpf"
                              type="text"
                              name="tomador_cpf"
                              id="tomador_cpf"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="tomador_cpf"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >Tomador CPF</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.tomador_im"
                              type="text"
                              name="tomador_im"
                              id="tomador_im"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="tomador_im"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >Tomador IM</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.intermediario_cnpj"
                              type="text"
                              name="intermediario_cnpj"
                              id="intermediario_cnpj"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="intermediario_cnpj"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >Intermediário CNPJ</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.intermediario_cpf"
                              type="text"
                              name="intermediario_cpf"
                              id="intermediario_cpf"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="intermediario_cpf"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >Intermediário CPF</label
                            >
                          </div>
                          <div class="relative z-0 w-full mb-5 group">
                            <input
                              v-model="formsearch.intermediario_im"
                              type="text"
                              name="intermediario_im"
                              id="intermediario_im"
                              class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none dark:text-white dark:border-gray-600 dark:focus:border-blue-500 focus:outline-none focus:ring-0 focus:border-blue-600 peer"
                              placeholder=" "
                            />
                            <label
                              for="intermediario_im"
                              class="peer-focus:font-medium absolute text-sm text-gray-500 dark:text-gray-400 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 rtl:peer-focus:translate-x-1/4 rtl:peer-focus:left-auto peer-focus:text-blue-600 peer-focus:dark:text-blue-500 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6"
                              >Intermediário IM</label
                            >
                          </div>
                        </div>
                      </div>
                    </div>
                  </form>

                  <div
                    class="w-full flex flex-row items-center justify-between self-center mb-4"
                  >
                    <h2
                      class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                      Localizamos os seguintes resultados
                    </h2>

                    <a
                      :href="'data:application/json;base64,' + notas"
                      class="font-bold shadow-sm rounded-lg py-1.5 px-3 bg-orange-600 text-white flex items-center justify-center transition-all duration-300 ease-in-out focus:outline-none hover:shadow focus:shadow-sm focus:shadow-outline"
                      download="notas-qive.json"
                    >
                      Baixar o retorno JSON
                    </a>
                  </div>
                  <template v-if="success && notasarray?.length">
                    <div v-if="notasarray.length">
                      <div class="overflow-x-auto">
                        <table
                          class="min-w-full table-auto border-separate border-spacing-0.5 bg-white"
                        >
                          <thead>
                            <tr
                              class="bg-gray-100 text-left text-sm font-semibold text-gray-700"
                            >
                              <th class="px-4 py-2">Nota</th>
                              <th class="px-4 py-2">Valor</th>
                              <th class="px-4 py-2">CPF/CNPJ</th>
                              <th class="px-4 py-2">Nome/Razão Social</th>
                              <th class="px-4 py-2">Emissão</th>
                              <th class="px-4 py-2">Detalhes</th>
                            </tr>
                          </thead>
                          <tbody
                            id="accordion-collapse"
                            data-accordion="collapse"
                          >
                            <template v-for="(nota, i) in notasarray" :key="i">
                              <tr
                                class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
                              >
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  {{ nota?.Nfse?.InfNfse?.Numero }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  R$
                                  {{ nota?.Nfse?.InfNfse?.ValoresNfse?.ValorLiquidoNfse }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.IdentificacaoTomador?.CpfCnpj?.Cnpj || nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.IdentificacaoTomador?.CpfCnpj?.Cpf }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.RazaoSocial }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  {{ new Date(nota?.Nfse?.InfNfse?.DataEmissao).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' }) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  <button
                                    type="button"
                                    class="flex items-center justify-center items-center text-center w-full font-bold rtl:text-right text-gray-500 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-800 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 gap-3"
                                    :data-accordion-target="'#accordion-collapse-body-' + i"
                                    aria-expanded="false"
                                    :aria-controls="'#accordion-collapse-body-' + i"
                                  >
                                    <svg
                                      data-accordion-icon
                                      class="w-3 h-3 rotate-180 shrink-0"
                                      aria-hidden="true"
                                      xmlns="http://www.w3.org/2000/svg"
                                      fill="none"
                                      viewBox="0 0 10 6"
                                    >
                                      <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5 5 1 1 5"
                                      />
                                    </svg>
                                  </button>
                                </td>
                              </tr>
                              <tr
                                :id="'accordion-collapse-body-' + i"
                                class="hidden"
                                :aria-labelledby="'accordion-collapse-heading-' + i"
                              >
                                <td colspan="8">
                                  <div class="py-2">
                                    <div
                                      class="border-t border-gray-200 pt-2 px-4"
                                    >
                                      <div>
                                        <strong>Descrição:</strong>
                                        {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Discriminacao }}
                                      </div>
                                      <div>
                                        <strong>Serviço:</strong>
                                        {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.CodigoTributacaoMunicipio }}
                                        -
                                        {{ nota?.Nfse?.InfNfse?.DescricaoCodigoTributacaoMunicípio }}
                                      </div>
                                      <div>
                                        <strong>Código de Verificação:</strong>
                                        {{ nota?.Nfse?.InfNfse?.CodigoVerificacao }}
                                      </div>
                                      <div>
                                        <strong>Exigibilidade ISS:</strong>
                                        {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.ExigibilidadeISS === '1' ? 'Sim' : 'Não' }}
                                      </div>
                                      <div>
                                        <strong>Data de Emissão:</strong>
                                        {{ nota?.Nfse?.InfNfse?.DataEmissao }}
                                      </div>
                                      <div>
                                        <strong>Outras Informações:</strong>
                                        <div>
                                          <span
                                            v-for="(line, lnindex) in nota?.Nfse?.InfNfse?.OutrasInformacoes?.split(/\\s\\n/) || []"
                                            :key="lnindex"
                                          >
                                            {{ line }}
                                            <br
                                              v-if="lnindex < nota?.Nfse?.InfNfse?.OutrasInformacoes?.split(/\\s\\n/).length - 1"
                                            />
                                          </span>
                                        </div>
                                      </div>
                                      <div
                                        class="mt-2 border-t border-gray-200 pt-2"
                                      >
                                        <strong>Valores da NFSe:</strong>
                                        <div>
                                          <strong>Base de Cálculo:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.ValoresNfse?.BaseCalculo }}
                                        </div>
                                        <div>
                                          <strong>Valor Líquido:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.ValoresNfse?.ValorLiquidoNfse }}
                                        </div>
                                        <div>
                                          <strong>Valor Crédito:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.ValorCredito }}
                                        </div>
                                      </div>
                                      <div
                                        class="mt-2 border-t border-gray-200 pt-2"
                                      >
                                        <strong>Dados do Prestador:</strong>
                                        <div>
                                          <strong>Razão Social:</strong>
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.RazaoSocial }}
                                        </div>
                                        <div>
                                          <strong>Nome Fantasia:</strong>
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.NomeFantasia }}
                                        </div>
                                        <div>
                                          <strong>Endereço:</strong>
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Endereco
                                          }},
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Numero }}
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Complemento }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Bairro }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.CodigoMunicipio }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Uf }}
                                          - CEP:
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Cep }}
                                        </div>
                                        <div>
                                          <strong>Telefone:</strong>
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Contato?.Telefone }}
                                        </div>
                                        <div>
                                          <strong>Email:</strong>
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Contato?.Email }}
                                        </div>
                                      </div>
                                      <div
                                        class="mt-2 border-t border-gray-200 pt-2"
                                      >
                                        <strong>Dados do Tomador:</strong>
                                        <div>
                                          <strong>Razão Social:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.RazaoSocial }}
                                        </div>
                                        <div>
                                          <strong>CPF/CNPJ:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.IdentificacaoTomador?.CpfCnpj?.Cpf || nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.IdentificacaoTomador?.CpfCnpj?.Cnpj }}
                                        </div>
                                        <div>
                                          <strong>Endereço:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.Endereco?.Endereco
                                          }},
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.Endereco?.Numero }}
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.Endereco?.Complemento }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.Endereco?.Bairro }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.Endereco?.CodigoMunicipio }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.Endereco?.Uf }}
                                          - CEP:
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.Endereco?.Cep }}
                                        </div>
                                        <div>
                                          <strong>Email:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.TomadorServico?.Contato?.Email }}
                                        </div>
                                      </div>
                                      <div
                                        class="mt-2 border-t border-gray-200 pt-2"
                                      >
                                        <strong>Dados do Serviço:</strong>
                                        <div>
                                          <strong>Valor dos Serviços:</strong>
                                          R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorServicos }}
                                        </div>
                                        <div>
                                          <strong>Valor das Deduções:</strong>
                                          R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorDeducoes }}
                                        </div>
                                        <div>
                                          <strong>Valor do PIS:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorPis }}
                                        </div>
                                        <div>
                                          <strong>Valor da COFINS:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorCofins }}
                                        </div>
                                        <div>
                                          <strong>Valor do INSS:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorInss }}
                                        </div>
                                        <div>
                                          <strong>Valor do IR:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorIr }}
                                        </div>
                                        <div>
                                          <strong>Valor do CSLL:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorCsll }}
                                        </div>
                                        <div>
                                          <strong>Outras Retenções:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.OutrasRetencoes }}
                                        </div>
                                        <div>
                                          <strong>Valor do ISS:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorIss }}
                                        </div>
                                        <div>
                                          <strong>Alíquota:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.Aliquota }}
                                        </div>
                                        <div>
                                          <strong>ISS Retido:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.IssRetido == 1 ? 'Sim' : 'Não' }}
                                        </div>
                                        <div>
                                          <strong
                                            >Item da Lista de Serviço:</strong
                                          >
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.ItemListaServico }}
                                        </div>
                                        <div>
                                          <strong>Código CNAE:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.CodigoCnae }}
                                        </div>
                                        <div>
                                          <strong
                                            >Município de Incidência:</strong
                                          >
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.MunicipioIncidencia }}
                                        </div>
                                      </div>
                                      <div
                                        class="mt-2 border-t border-gray-200 pt-2"
                                      >
                                        <strong>Dados Adicionais:</strong>
                                        <div>
                                          <strong
                                            >Optante pelo Simples
                                            Nacional:</strong
                                          >
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.OptanteSimplesNacional == 1 ? 'Sim' : 'Não' }}
                                        </div>
                                        <div>
                                          <strong>Incentivo Fiscal:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.IncentivoFiscal == 1 ? 'Sim' : 'Não' }}
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                            </template>
                          </tbody>
                        </table>
                      </div>
                    </div>

                    <div v-else>
                      <p class="text-gray-600">
                        Nenhuma nota fiscal encontrada.
                      </p>
                    </div>
                  </template>
                  <template v-else>
                    <div
                      id="alert-border-1"
                      class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 dark:text-blue-400 dark:bg-gray-800 dark:border-blue-800"
                      role="alert"
                    >
                      <svg
                        class="shrink-0 w-4 h-4"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path
                          d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"
                        />
                      </svg>
                      <div class="ms-3 text-sm font-medium">
                        {{ code + ': ' + message }}
                      </div>
                    </div>
                  </template>
                </div>
              </div>
            </div>
            <div
              class="hidden p-4 rounded-lg bg-white dark:bg-gray-800"
              id="dashboard"
              role="tabpanel"
              aria-labelledby="dashboard-tab"
            >
              <form
                class="w-full mx-auto mb-16"
                @submit.prevent="submitreceived"
              >
                <div class="max-w-lg mx-auto flex mb-4">
                  <label
                    for="default-search"
                    class="me-3 mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white"
                    >Search</label
                  >
                  <div class="relative w-full">
                    <div
                      class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none"
                    >
                      <svg
                        class="w-4 h-4 text-gray-500 dark:text-gray-400"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 20 20"
                      >
                        <path
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"
                        />
                      </svg>
                    </div>
                    <input
                      type="search"
                      id="default-search"
                      v-model="formsearchreceived.document"
                      class="block w-full p-4 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                      placeholder="Localizar nota por CPF/CNPJ..."
                    />
                    <button
                      type="submit"
                      class="text-white absolute end-2.5 bottom-2.5 bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                    >
                      Buscar
                    </button>
                  </div>
                </div>
              </form>
              <template v-if="success && notasarray?.length">
                <div v-if="notasarray.length">
                      <div class="overflow-x-auto">
                        <table
                          class="min-w-full table-auto border-separate border-spacing-0.5 bg-white"
                        >
                          <thead>
                            <tr
                              class="bg-gray-100 text-left text-sm font-semibold text-gray-700"
                            >
                              <th class="px-4 py-2">Nota</th>
                              <th class="px-4 py-2">Valor</th>
                              <th class="px-4 py-2">CPF/CNPJ</th>
                              <th class="px-4 py-2">Nome/Razão Social</th>
                              <th class="px-4 py-2">Emissão</th>
                              <th class="px-4 py-2">XML</th>
                              <th class="px-4 py-2">Detalhes</th>
                            </tr>
                          </thead>
                          <tbody
                            id="accordion-collapse"
                            data-accordion="collapse"
                          >
                            <template v-for="(nota, i) in notasarray" :key="i">
                              <tr
                                class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
                              >
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  {{ nota?.Nfse?.InfNfse?.Numero }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  R$
                                  {{ nota?.Nfse?.InfNfse?.ValoresNfse?.ValorLiquidoNfse }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Prestador?.CpfCnpj?.Cnpj || nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Prestador?.CpfCnpj?.Cpf }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  {{ nota?.Nfse?.InfNfse?.PrestadorServico?.RazaoSocial }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  {{ new Date(nota?.Nfse?.InfNfse?.DataEmissao).toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' }) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  <a :href="'data:text/xml;base64,' + nota?.xml" class="text-blue-700 hover:text-violet-800" :download="'nota_' + nota?.Nfse?.InfNfse?.Numero + '.xml'">Baixar</a>
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700">
                                  <button
                                    type="button"
                                    class="flex items-center justify-center items-center text-center w-full font-bold rtl:text-right text-gray-500 focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-800 dark:border-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 gap-3"
                                    :data-accordion-target="'#accordion-collapse-body-prestadas-' + i"
                                    aria-expanded="false"
                                    :aria-controls="'#accordion-collapse-body-prestadas-' + i"
                                  >
                                    <svg
                                      data-accordion-icon
                                      class="w-3 h-3 rotate-180 shrink-0"
                                      aria-hidden="true"
                                      xmlns="http://www.w3.org/2000/svg"
                                      fill="none"
                                      viewBox="0 0 10 6"
                                    >
                                      <path
                                        stroke="currentColor"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 5 5 1 1 5"
                                      />
                                    </svg>
                                  </button>
                                </td>
                              </tr>
                              <tr
                                :id="'accordion-collapse-body-prestadas-' + i"
                                class="hidden"
                                :aria-labelledby="'accordion-collapse-heading-prestadas-' + i"
                              >
                                <td colspan="8">
                                  <div class="py-2">
                                    <div
                                      class="border-t border-gray-200 pt-2 px-4"
                                    >
                                      <div>
                                        <strong>Descrição:</strong>
                                        {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Discriminacao }}
                                      </div>
                                      <div>
                                        <strong>Serviço:</strong>
                                        {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.CodigoTributacaoMunicipio }}
                                        -
                                        {{ nota?.Nfse?.InfNfse?.DescricaoCodigoTributacaoMunicípio }}
                                      </div>
                                      <div>
                                        <strong>Código de Verificação:</strong>
                                        {{ nota?.Nfse?.InfNfse?.CodigoVerificacao }}
                                      </div>
                                      <div>
                                        <strong>Exigibilidade ISS:</strong>
                                        {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.ExigibilidadeISS === '1' ? 'Sim' : 'Não' }}
                                      </div>
                                      <div>
                                        <strong>Data de Emissão:</strong>
                                        {{ nota?.Nfse?.InfNfse?.DataEmissao }}
                                      </div>
                                      <div>
                                        <strong>Outras Informações:</strong>
                                        <div>
                                          <span
                                            v-for="(line, lnindex) in nota?.Nfse?.InfNfse?.OutrasInformacoes?.split(/\\s\\n/) || []"
                                            :key="lnindex"
                                          >
                                            {{ line }}
                                            <br
                                              v-if="lnindex < nota?.Nfse?.InfNfse?.OutrasInformacoes?.split(/\\s\\n/).length - 1"
                                            />
                                          </span>
                                        </div>
                                      </div>
                                      <div
                                        class="mt-2 border-t border-gray-200 pt-2"
                                      >
                                        <strong>Valores da NFSe:</strong>
                                        <div>
                                          <strong>Base de Cálculo:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.ValoresNfse?.BaseCalculo }}
                                        </div>
                                        <div>
                                          <strong>Valor Líquido:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.ValoresNfse?.ValorLiquidoNfse }}
                                        </div>
                                        <div>
                                          <strong>Valor Crédito:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.ValorCredito }}
                                        </div>
                                      </div>
                                      <div
                                        class="mt-2 border-t border-gray-200 pt-2"
                                      >
                                        <strong>Dados do Prestador:</strong>
                                        <div>
                                          <strong>Razão Social:</strong>
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.RazaoSocial }}
                                        </div>
                                        <div>
                                          <strong>Nome Fantasia:</strong>
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.NomeFantasia }}
                                        </div>
                                        <div>
                                          <strong>Endereço:</strong>
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Endereco
                                          }},
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Numero }}
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Complemento }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Bairro }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.CodigoMunicipio }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Uf }}
                                          - CEP:
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Endereco?.Cep }}
                                        </div>
                                        <div>
                                          <strong>Telefone:</strong>
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Contato?.Telefone }}
                                        </div>
                                        <div>
                                          <strong>Email:</strong>
                                          {{ nota?.Nfse?.InfNfse?.PrestadorServico?.Contato?.Email }}
                                        </div>
                                      </div>
                                      <div
                                        class="mt-2 border-t border-gray-200 pt-2"
                                      >
                                        <strong>Dados do Tomador:</strong>
                                        <div>
                                          <strong>Razão Social:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Tomador?.RazaoSocial }}
                                        </div>
                                        <div>
                                          <strong>CPF/CNPJ:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Tomador?.IdentificacaoTomador?.CpfCnpj?.Cpf || nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Tomador?.IdentificacaoTomador?.CpfCnpj?.Cnpj }}
                                        </div>
                                        <div>
                                          <strong>Endereço:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Tomador?.Endereco?.Endereco
                                          }},
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Tomador?.Endereco?.Numero }}
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Tomador?.Endereco?.Complemento }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Tomador?.Endereco?.Bairro }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Tomador?.Endereco?.CodigoMunicipio }}
                                          -
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Tomador?.Endereco?.Uf }}
                                          - CEP:
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Tomador?.Endereco?.Cep }}
                                        </div>
                                        <div>
                                          <strong>Email:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Tomador?.Contato?.Email }}
                                        </div>
                                      </div>
                                      <div
                                        class="mt-2 border-t border-gray-200 pt-2"
                                      >
                                        <strong>Dados do Serviço:</strong>
                                        <div>
                                          <strong>Valor dos Serviços:</strong>
                                          R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorServicos }}
                                        </div>
                                        <div>
                                          <strong>Valor das Deduções:</strong>
                                          R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorDeducoes }}
                                        </div>
                                        <div>
                                          <strong>Valor do PIS:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorPis }}
                                        </div>
                                        <div>
                                          <strong>Valor da COFINS:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorCofins }}
                                        </div>
                                        <div>
                                          <strong>Valor do INSS:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorInss }}
                                        </div>
                                        <div>
                                          <strong>Valor do IR:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorIr }}
                                        </div>
                                        <div>
                                          <strong>Valor do CSLL:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorCsll }}
                                        </div>
                                        <div>
                                          <strong>Outras Retenções:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.OutrasRetencoes }}
                                        </div>
                                        <div>
                                          <strong>Valor do ISS:</strong> R$
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.ValorIss }}
                                        </div>
                                        <div>
                                          <strong>Alíquota:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.Valores?.Aliquota }}
                                        </div>
                                        <div>
                                          <strong>ISS Retido:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.IssRetido == 1 ? 'Sim' : 'Não' }}
                                        </div>
                                        <div>
                                          <strong
                                            >Item da Lista de Serviço:</strong
                                          >
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.ItemListaServico }}
                                        </div>
                                        <div>
                                          <strong>Código CNAE:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.CodigoCnae }}
                                        </div>
                                        <div>
                                          <strong
                                            >Município de Incidência:</strong
                                          >
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.Servico?.MunicipioIncidencia }}
                                        </div>
                                      </div>
                                      <div
                                        class="mt-2 border-t border-gray-200 pt-2"
                                      >
                                        <strong>Dados Adicionais:</strong>
                                        <div>
                                          <strong
                                            >Optante pelo Simples
                                            Nacional:</strong
                                          >
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.OptanteSimplesNacional == 1 ? 'Sim' : 'Não' }}
                                        </div>
                                        <div>
                                          <strong>Incentivo Fiscal:</strong>
                                          {{ nota?.Nfse?.InfNfse?.DeclaracaoPrestacaoServico?.InfDeclaracaoPrestacaoServico?.IncentivoFiscal == 1 ? 'Sim' : 'Não' }}
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </td>
                              </tr>
                            </template>
                          </tbody>
                        </table>
                      </div>
                    </div>

              </template>
              <template v-else>
                <div
                  id="alert-border-1"
                  class="flex items-center p-4 mb-4 text-blue-800 border-t-4 border-blue-300 bg-blue-50 dark:text-blue-400 dark:bg-gray-800 dark:border-blue-800"
                  role="alert"
                >
                  <svg
                    class="shrink-0 w-4 h-4"
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z"
                    />
                  </svg>
                  <div class="ms-3 text-sm font-medium">
                    {{ code + ': ' + message }}
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
