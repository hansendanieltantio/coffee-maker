<template>
    <CoffeeLayout>
        <Head title="Beranda" />

        <div class="py-20 text-center">
            <span
                class="inline-block px-4 py-1.5 mb-6 text-[10px] font-bold tracking-[0.3em] text-[#C49B72] uppercase bg-[#C49B72]/10 rounded-full"
            >
                Weighted Fair Queuing - Edge Computing
            </span>
            <h1 class="text-6xl font-serif font-bold text-[#3B2B24] mb-6">
                Nikmati Kopi Cerdas
            </h1>
            <p class="text-[#8A6F57] max-w-2xl mx-auto mb-12">
                Sistem IoT Coffee Maker dengan implementasi algoritma
                <span class="text-[#3B2B24] font-bold"
                    >Weighted Fair Queuing (WFQ)</span
                >
                untuk manajemen antrean yang adil.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 py-12">
            <div
                v-for="menu in menus"
                :key="menu.id"
                class="bg-white p-8 rounded-[40px] border border-[#FBF3E8] shadow-sm hover:shadow-2xl transition-all duration-500 group relative"
            >
                <div class="mb-8 relative">
                    <div
                        :class="menu.color"
                        class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl shadow-lg transform group-hover:rotate-12 transition-transform duration-500"
                    >
                        {{ menu.icon }}
                    </div>
                    <div
                        class="absolute -right-2 -bottom-2 bg-white border-2 border-[#FBF3E8] text-[#3B2B24] w-10 h-10 rounded-full flex items-center justify-center font-bold text-xs shadow-sm"
                    >
                        W{{ menu.weight }}
                    </div>
                </div>

                <h3
                    class="text-xl font-bold text-[#3B2B24] mb-3 group-hover:text-[#C49B72] transition-colors leading-tight"
                >
                    {{ menu.name }}
                </h3>

                <p
                    class="text-xs text-[#8A6F57] leading-relaxed mb-8 h-12 overflow-hidden"
                >
                    {{ menu.desc }}
                </p>

                <Link
                    v-if="!$page.props.auth.user"
                    href="/login"
                    class="block text-center bg-[#FDF8F3] text-[#3B2B24] py-4 rounded-2xl text-[10px] font-bold uppercase tracking-widest hover:bg-[#3B2B24] hover:text-white transition-all"
                >
                    Masuk untuk Memesan
                </Link>
                <Link
                    v-else
                    href="/dashboard"
                    class="block text-center bg-[#3B2B24] text-white py-4 rounded-2xl text-[10px] font-bold uppercase tracking-widest hover:bg-[#C49B72] transition-all"
                >
                    Pesan Sekarang
                </Link>
            </div>
        </div>
    </CoffeeLayout>
</template>

<script setup>
import CoffeeLayout from "@/Layouts/CoffeeLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { menuList } from "@/Pages/Menu.vue";

const menus = menuList;
</script>
