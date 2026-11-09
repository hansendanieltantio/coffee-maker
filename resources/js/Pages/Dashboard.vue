<template>
    <CoffeeLayout>
        <Head title="Dashboard" />

        <!-- Header Panel -->
        <div
            class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4"
        >
            <div>
                <h1 class="text-3xl font-serif font-bold text-[#3B2B24]">
                    Dashboard
                </h1>
                <p class="text-[#8A6F57] mt-1 text-sm">
                    Selamat datang,
                    <span class="font-bold text-[#3B2B24]">{{
                        $page.props.auth.user.name
                    }}</span>
                </p>
            </div>
        </div>

        <!-- MENU PESANAN -->
        <section class="mb-10">
            <h2 class="text-2xl font-serif font-bold text-[#3B2B24] mb-6">
                Buat Pesanan Baru
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    v-for="menu in staticMenus"
                    :key="menu.id"
                    class="bg-white p-6 rounded-[32px] border border-[#FBF3E8] shadow-sm flex flex-col items-center text-center group hover:border-[#C49B72] transition-all"
                >
                    <div
                        :class="menu.color"
                        class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mb-4"
                    >
                        {{ menu.icon }}
                    </div>
                    <h3 class="font-bold text-[#3B2B24] text-sm mb-1">
                        {{ menu.name }}
                    </h3>
                    <button
                        @click="pesanKopi(menu)"
                        class="mt-4 w-full bg-[#3B2B24] text-white py-3 rounded-xl text-[10px] font-bold uppercase hover:bg-[#C49B72] transition-all"
                    >
                        Pesan
                    </button>
                </div>
            </div>
        </section>

        <!-- TABEL PESANAN (Semua pesanan untuk admin, pesanan sendiri untuk user) -->
        <div class="mb-10">
            <div
                class="bg-white p-6 md:p-8 rounded-[40px] border border-[#FBF3E8] shadow-sm"
            >
                <h2
                    class="text-xl font-bold text-[#3B2B24] mb-6 flex items-center"
                >
                    <span>
                        📊
                        {{ isAdmin ? "Seluruh Pesanan" : "Pesanan Saya" }}
                    </span>
                </h2>

                <div class="grid grid-cols-3 gap-3 mb-6">
                    <div class="rounded-2xl bg-orange-50 px-4 py-3">
                        <div
                            class="text-[10px] font-bold uppercase text-orange-600"
                        >
                            Pending
                        </div>
                        <div class="mt-1 text-2xl font-bold text-[#3B2B24]">
                            {{ queueSummary.pending }}
                        </div>
                    </div>
                    <div class="rounded-2xl bg-blue-50 px-4 py-3">
                        <div
                            class="text-[10px] font-bold uppercase text-blue-600"
                        >
                            Diproses
                        </div>
                        <div class="mt-1 text-2xl font-bold text-[#3B2B24]">
                            {{ queueSummary.proses }}
                        </div>
                    </div>
                    <div class="rounded-2xl bg-green-50 px-4 py-3">
                        <div
                            class="text-[10px] font-bold uppercase text-green-600"
                        >
                            Selesai
                        </div>
                        <div class="mt-1 text-2xl font-bold text-[#3B2B24]">
                            {{ queueSummary.success }}
                        </div>
                    </div>
                </div>

                <div
                    class="overflow-x-auto max-h-[500px] overflow-y-auto custom-scrollbar"
                >
                    <table class="w-full text-left">
                        <thead class="sticky top-0 bg-white">
                            <tr
                                class="text-[10px] font-bold text-[#8A6F57] uppercase border-b border-[#FBF3E8]"
                            >
                                <th class="py-4 px-4">
                                    {{ isAdmin ? "Pemesan & Menu" : "Menu Pesanan" }}
                                </th>
                                <th v-if="isAdmin" class="py-4 px-4">
                                    Bobot Prioritas
                                </th>
                                <th class="py-4 px-4">
                                    Estimasi Waktu Seduh
                                </th>
                                <th class="py-4 px-4">Waktu Pemesanan</th>
                                <th v-if="isAdmin" class="py-4 px-4">
                                    Mulai Diproses
                                </th>
                                <th v-if="isAdmin" class="py-4 px-4">
                                    Selesai Diproses
                                </th>
                                <th v-if="isAdmin" class="py-4 px-4">
                                    Lama Menunggu
                                </th>
                                <th v-if="isAdmin" class="py-4 px-4">
                                    Lama Proses
                                </th>
                                <th v-if="isAdmin" class="py-4 px-4">
                                    Total Waktu Pesanan
                                </th>
                                <th class="py-4 px-4 text-center">
                                    Status Pesanan
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <tr
                                v-for="order in displayedOrders"
                                :key="order.id"
                                class="border-b border-[#FDF8F3]"
                            >
                                <td class="py-5 px-4">
                                    <div
                                        v-if="isAdmin"
                                        class="font-bold text-[#3B2B24]"
                                    >
                                        {{ order.user_name }}
                                    </div>
                                    <div class="text-xs text-[#8A6F57]">
                                        {{ order.menu_name }}
                                    </div>
                                </td>
                                <td
                                    v-if="isAdmin"
                                    class="py-5 px-4 text-[#8A6F57]"
                                >
                                    {{ order.weight }}
                                </td>
                                <td class="py-5 px-4 text-[#8A6F57]">
                                    {{ formatDuration(order.service_time) }}
                                </td>
                                <td class="py-5 px-4 text-[#8A6F57]">
                                    {{ formatTime(order.created_at) }}
                                </td>
                                <td
                                    v-if="isAdmin"
                                    class="py-5 px-4 text-[#8A6F57]"
                                >
                                    {{ formatTime(order.started_at) }}
                                </td>
                                <td
                                    v-if="isAdmin"
                                    class="py-5 px-4 text-[#8A6F57]"
                                >
                                    {{ formatTime(order.completed_at) }}
                                </td>
                                <td
                                    v-if="isAdmin"
                                    class="py-5 px-4 text-[#8A6F57]"
                                >
                                    {{ formatDuration(order.waiting_time) }}
                                </td>
                                <td
                                    v-if="isAdmin"
                                    class="py-5 px-4 text-[#8A6F57]"
                                >
                                    {{ formatDuration(order.processing_time) }}
                                </td>
                                <td
                                    v-if="isAdmin"
                                    class="py-5 px-4 text-[#8A6F57]"
                                >
                                    {{ formatDuration(order.flow_time) }}
                                </td>
                                <td class="py-5 px-4 text-center">
                                    <span
                                        :class="getStatusClass(order.status)"
                                        class="px-3 py-1 rounded-full text-[10px] font-bold uppercase inline-block"
                                    >
                                        {{ order.status }}
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="displayedOrders.length === 0">
                                <td
                                    :colspan="isAdmin ? 10 : 4"
                                    class="py-10 text-center text-sm text-[#8A6F57]"
                                >
                                    Belum ada pesanan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </CoffeeLayout>
</template>

<script setup>
import CoffeeLayout from "@/Layouts/CoffeeLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, computed, watch } from "vue";
import Swal from "sweetalert2";

const props = defineProps({
    orders: Array,
    my_orders: Array,
    queue_summary: {
        type: Object,
        default: () => ({ pending: 0, proses: 0, success: 0 }),
    },
});

const queueSummary = computed(() => props.queue_summary);
const page = usePage();
const isAdmin = computed(() => page.props.auth.user.role === "admin");

// Helper Format Waktu (HH:mm)
const formatTime = (dateString) => {
    if (!dateString) return "-";
    const date = new Date(dateString);
    return date.toLocaleTimeString("id-ID", {
        hour: "2-digit",
        minute: "2-digit",
    });
};

// Sorting: Terbaru di atas
const displayedOrders = computed(() =>
    [...(isAdmin.value ? props.orders || [] : props.my_orders || [])].sort(
        (a, b) => b.id - a.id,
    ),
);

const isPolling = ref(false);
let pollingInterval = null;

const checkNeedsPolling = () => {
    const needsUpdate = [
        ...(props.orders || []),
        ...(props.my_orders || []),
    ].some((o) => o.status === "pending" || o.status === "proses");
    if (needsUpdate && !pollingInterval) startPolling();
    else if (!needsUpdate && pollingInterval) stopPolling();
};

const startPolling = () => {
    isPolling.value = true;
    pollingInterval = setInterval(() => {
        router.reload({
            only: ["orders", "my_orders", "queue_summary"],
            preserveScroll: true,
        });
    }, 3000);
};

const stopPolling = () => {
    clearInterval(pollingInterval);
    pollingInterval = null;
    isPolling.value = false;
};

watch(
    () => props.orders,
    () => checkNeedsPolling(),
    { deep: true },
);
onMounted(() => checkNeedsPolling());
onUnmounted(() => stopPolling());

const staticMenus = [
    {
        id: 4,
        name: "Kopi Hitam Murni",
        weight: 4,
        icon: "☕",
        color: "bg-gray-100",
    },
    {
        id: 3,
        name: "Kopi Hitam Dengan Gula",
        weight: 3,
        icon: "🍯",
        color: "bg-yellow-100",
    },
    {
        id: 2,
        name: "Kopi Susu Saja",
        weight: 2,
        icon: "🥛",
        color: "bg-blue-100",
    },
    {
        id: 1,
        name: "Kopi Susu Dengan Gula",
        weight: 1,
        icon: "✨",
        color: "bg-orange-100",
    },
];

const getStatusClass = (status) => {
    switch (status) {
        case "pending":
            return "bg-orange-100 text-orange-600";
        case "proses":
            return "bg-blue-600 text-white animate-pulse shadow-md";
        case "success":
            return "bg-green-100 text-green-600 border border-green-200";
        default:
            return "bg-gray-100 text-gray-600";
    }
};

const pesanKopi = (menu) => {
    router.post(
        route("order.store"),
        {
            menu_name: menu.name,
            weight: menu.weight,
        },
        {
            onStart: () =>
                Swal.fire({
                    title: "Memproses...",
                    didOpen: () => Swal.showLoading(),
                    allowOutsideClick: false,
                }),
            onSuccess: () =>
                Swal.fire({
                    title: "Berhasil!",
                    text: "Pesanan masuk antrean.",
                    icon: "success",
                    timer: 1500,
                    showConfirmButton: false,
                }),
        },
    );
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #3b2b24;
    border-radius: 10px;
}
</style>
