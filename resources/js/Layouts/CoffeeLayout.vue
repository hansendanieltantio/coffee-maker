<template>
    <div class="min-h-screen bg-[#FDF8F3] text-[#3B2B24] font-sans">
        <nav
            class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-[#FBF3E8]"
        >
            <div
                class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between"
            >
                <Link href="/" class="flex items-center space-x-3 group">
                    <div
                        class="bg-[#3B2B24] p-2 rounded-xl group-hover:rotate-12 transition-transform"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-[#C49B72]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"
                            />
                        </svg>
                    </div>
                    <span class="text-lg font-serif font-bold tracking-tight"
                        >Coffee <span class="text-[#C49B72]">Maker</span></span
                    >
                </Link>

                <div
                    class="hidden md:flex items-center space-x-8 text-[11px] font-bold uppercase tracking-[0.2em]"
                >
                    <Link
                        href="/"
                        class="hover:text-[#C49B72] transition-colors"
                        >Beranda</Link
                    >
                    <Link
                        href="/about"
                        class="hover:text-[#C49B72] transition-colors"
                        >Tentang Peneliti</Link
                    >
                    <Link
                        v-if="$page.props.auth.user"
                        href="/dashboard"
                        class="hover:text-[#C49B72] transition-colors"
                        >Dashboard</Link
                    >

                    <div
                        v-if="!$page.props.auth.user"
                        class="flex items-center space-x-3 ml-4"
                    >
                        <Link
                            href="/login"
                            class="px-6 py-2.5 border-2 border-[#3B2B24] rounded-xl hover:bg-[#3B2B24] hover:text-white transition-all text-[#3B2B24]"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            class="px-6 py-2.5 bg-[#3B2B24] text-white rounded-xl hover:bg-[#C49B72] border-2 border-[#3B2B24] hover:border-[#C49B72] transition-all shadow-md shadow-[#3B2B24]/10"
                        >
                            Daftar
                        </Link>
                    </div>

                    <div v-else class="flex items-center space-x-4">
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="text-red-600 font-bold border border-red-100 px-4 py-2 rounded-xl hover:bg-red-50 transition-colors"
                        >
                            Keluar
                        </Link>
                    </div>
                </div>

                <button
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                    class="md:hidden p-2 text-[#3B2B24] focus:outline-none"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-7 w-7"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            v-if="!isMobileMenuOpen"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16m-7 6h7"
                        />
                        <path
                            v-else
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>

            <transition name="fade">
                <div
                    v-show="isMobileMenuOpen"
                    class="md:hidden bg-white border-t border-[#FBF3E8] px-6 py-8 space-y-6 shadow-2xl absolute w-full left-0"
                >
                    <div class="space-y-4">
                        <Link
                            href="/"
                            @click="isMobileMenuOpen = false"
                            class="block text-sm font-bold uppercase tracking-widest text-[#3B2B24]"
                            >Beranda</Link
                        >
                        <Link
                            href="/about"
                            @click="isMobileMenuOpen = false"
                            class="block text-sm font-bold uppercase tracking-widest text-[#3B2B24]"
                            >Tentang Peneliti</Link
                        >
                        <Link
                            v-if="$page.props.auth.user"
                            href="/dashboard"
                            @click="isMobileMenuOpen = false"
                            class="block text-sm font-bold uppercase tracking-widest text-[#3B2B24]"
                            >Dashboard</Link
                        >
                    </div>

                    <hr class="border-[#FBF3E8]" />

                    <div
                        v-if="!$page.props.auth.user"
                        class="grid grid-cols-2 gap-4"
                    >
                        <Link
                            href="/login"
                            @click="isMobileMenuOpen = false"
                            class="flex items-center justify-center py-4 border-2 border-[#3B2B24] text-[#3B2B24] rounded-2xl text-[10px] font-bold uppercase tracking-widest"
                        >
                            Masuk
                        </Link>
                        <Link
                            href="/register"
                            @click="isMobileMenuOpen = false"
                            class="flex items-center justify-center py-4 bg-[#3B2B24] text-white rounded-2xl text-[10px] font-bold uppercase tracking-widest shadow-lg shadow-[#3B2B24]/10"
                        >
                            Daftar
                        </Link>
                    </div>
                    <div v-else>
                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            @click="isMobileMenuOpen = false"
                            class="block w-full text-center py-4 text-xs font-bold uppercase tracking-widest text-red-600 bg-red-50 rounded-2xl"
                        >
                            Keluar Akun
                        </Link>
                    </div>
                </div>
            </transition>
        </nav>

        <main class="max-w-7xl mx-auto px-6 py-10">
            <slot />
        </main>

        <footer class="mt-auto border-t border-[#FBF3E8] bg-white py-12 px-6">
            <div class="max-w-7xl mx-auto text-center">
                <div class="flex justify-center space-x-4 mb-6 opacity-30">
                    <div class="w-1.5 h-1.5 bg-[#3B2B24] rounded-full"></div>
                    <div class="w-1.5 h-1.5 bg-[#3B2B24] rounded-full"></div>
                    <div class="w-1.5 h-1.5 bg-[#3B2B24] rounded-full"></div>
                </div>
                <p
                    class="text-[10px] font-bold text-[#8A6F57] uppercase tracking-[0.4em]"
                >
                    &copy; 2026 Hansen Daniel Tantio • FMIPA UNTAN
                </p>
                <p class="text-[9px] text-[#C49B72] mt-2 font-medium">
                    Implementasi Algoritma Weighted Fair Queuing pada Edge
                    Computing pada Perangkat Coffee Maker
                </p>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import { ref } from "vue";

const isMobileMenuOpen = ref(false);
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.3s ease,
        transform 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
