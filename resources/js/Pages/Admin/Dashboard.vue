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
import AppLayout from '@/Layouts/AppLayoutAdmin.vue';
import Welcome from '@/Components/Welcome.vue';

const props = defineProps({
    diskusage: String
}); 
</script>

<template>
    <AppLayout title="Geral">
        <div class="w-full">
            <div class="w-full mx-auto">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <Welcome :disckusage="diskusage ?? 0" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
