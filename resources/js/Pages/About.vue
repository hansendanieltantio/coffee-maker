<template>
    <CoffeeLayout>
        <Head title="Tentang Peneliti - Coffee Maker WFQ" />

        <div class="max-w-5xl mx-auto py-16 px-4">
            <div class="text-center mb-20">
                <span
                    class="inline-block px-4 py-1.5 mb-6 text-[10px] font-bold tracking-[0.3em] text-[#C49B72] uppercase bg-[#C49B72]/10 rounded-full"
                >
                    Informasi Peneliti & Proyek
                </span>
                <h1 class="text-5xl font-serif font-bold text-[#3B2B24] mb-6">
                    Profil Riset
                </h1>
                <div class="w-20 h-1 bg-[#C49B72] mx-auto rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
                <div class="lg:col-span-1">
                    <div
                        class="bg-white p-8 rounded-[40px] border border-[#FBF3E8] shadow-sm sticky top-8"
                    >
                        <div
                            class="w-20 h-20 bg-[#3B2B24] rounded-3xl flex items-center justify-center text-[#C49B72] text-3xl font-bold mb-6 mx-auto shadow-lg"
                        >
                            H
                        </div>
                        <div
                            class="text-center border-b border-[#FBF3E8] pb-6 mb-6"
                        >
                            <h2 class="text-xl font-bold text-[#3B2B24]">
                                Hansen Daniel Tantio
                            </h2>
                            <p class="text-[#C49B72] font-mono text-sm mt-1">
                                H1051201047
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <p
                                    class="text-[10px] font-bold text-[#8A6F57] uppercase tracking-widest"
                                >
                                    Jurusan
                                </p>
                                <p class="text-xs text-[#3B2B24] font-semibold">
                                    Rekayasa Sistem Komputer
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-bold text-[#8A6F57] uppercase tracking-widest"
                                >
                                    Fakultas
                                </p>
                                <p class="text-xs text-[#3B2B24] font-semibold">
                                    Matematika dan Ilmu Pengetahuan Alam
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-bold text-[#8A6F57] uppercase tracking-widest"
                                >
                                    Institusi
                                </p>
                                <p class="text-xs text-[#3B2B24] font-semibold">
                                    Universitas Tanjungpura
                                </p>
                            </div>
                            <div>
                                <p
                                    class="text-[10px] font-bold text-[#8A6F57] uppercase tracking-widest"
                                >
                                    Lokasi
                                </p>
                                <p class="text-xs text-[#3B2B24] font-semibold">
                                    Pontianak, Kalimantan Barat
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-8">
                    <div
                        class="bg-white p-10 rounded-[40px] border border-[#FBF3E8] shadow-sm relative overflow-hidden mb-8"
                    >
                        <h2
                            class="text-2xl font-serif font-bold text-[#3B2B24] mb-4"
                        >
                            Visi Penelitian
                        </h2>
                        <p
                            class="text-[#8A6F57] text-sm leading-relaxed text-justify"
                        >
                            Penelitian ini merupakan integrasi antara sistem
                            fisik (Hardware IoT) dengan konsep manajemen
                            jaringan komputer. Melalui skripsi ini, saya
                            mengimplementasikan kontrol cerdas pada mesin
                            pembuat kopi untuk membuktikan efektivitas metode
                            penjadwalan dalam menangani antrean yang dinamis.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            class="bg-white p-8 rounded-[32px] border border-[#FBF3E8] group hover:border-[#C49B72] transition-all"
                        >
                            <div
                                class="w-12 h-12 bg-[#FDF8F3] rounded-2xl flex items-center justify-center mb-6 text-[#C49B72] group-hover:bg-[#3B2B24] transition-colors"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                    />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-[#3B2B24] mb-3">
                                Weighted Fair Queuing
                            </h3>
                            <p
                                class="text-[11px] text-[#8A6F57] leading-relaxed"
                            >
                                Metode penjadwalan yang memberikan prioritas
                                berdasarkan bobot tertentu. Dalam sistem ini,
                                setiap varian kopi memiliki 'weight' yang
                                menentukan seberapa cepat pesanan diproses oleh
                                hardware.
                            </p>
                        </div>

                        <div
                            class="bg-[#3B2B24] p-8 rounded-[32px] border border-[#3B2B24] shadow-xl shadow-black/5"
                        >
                            <div
                                class="w-12 h-12 bg-[#C49B72] rounded-2xl flex items-center justify-center mb-6 text-[#3B2B24]"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-6 w-6"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"
                                    />
                                </svg>
                            </div>
                            <h3 class="text-lg font-bold text-white mb-3">
                                Edge Computing
                            </h3>
                            <p
                                class="text-[11px] text-white/60 leading-relaxed"
                            >
                                Arsitektur yang memungkinkan pemrosesan data
                                dilakukan di 'tepi' jaringan (dekat dengan mesin
                                kopi), sehingga respon sistem menjadi lebih
                                cepat tanpa ketergantungan penuh pada server
                                cloud.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CoffeeLayout>
</template>

<script setup>
import { Head } from "@inertiajs/vue3";
import CoffeeLayout from "@/Layouts/CoffeeLayout.vue";
</script>
