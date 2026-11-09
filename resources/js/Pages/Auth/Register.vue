<template>
    <CoffeeLayout>
        <Head title="Daftar" />

        <div class="flex items-center justify-center min-h-[90vh] px-4 py-12">
            <div
                class="w-full max-w-md bg-white p-8 md:p-12 rounded-[40px] border border-[#FBF3E8] shadow-sm"
            >
                <div
                    class="w-16 h-16 bg-[#FDF8F3] rounded-2xl flex items-center justify-center mx-auto mb-8"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-8 w-8 text-[#C49B72]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"
                        />
                    </svg>
                </div>

                <div class="text-center mb-10">
                    <h2 class="text-3xl font-serif font-bold text-[#3B2B24]">
                        Buat Akun
                    </h2>
                    <p class="text-sm text-[#8A6F57] mt-2">
                        Daftar untuk mulai memesan kopi
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label
                            class="text-[10px] font-bold text-[#8A6F57] uppercase tracking-widest ml-1"
                            >Nama Lengkap</label
                        >
                        <input
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            class="w-full mt-2 px-5 py-4 bg-[#FDF8F3] border-none rounded-2xl focus:ring-2 focus:ring-[#C49B72] text-sm"
                            placeholder="Contoh: Hansen Daniel"
                        />
                        <div
                            v-if="form.errors.name"
                            class="text-red-500 text-xs mt-1 ml-1"
                        >
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="text-[10px] font-bold text-[#8A6F57] uppercase tracking-widest ml-1"
                            >Alamat Email</label
                        >
                        <input
                            type="email"
                            v-model="form.email"
                            required
                            class="w-full mt-2 px-5 py-4 bg-[#FDF8F3] border-none rounded-2xl focus:ring-2 focus:ring-[#C49B72] text-sm"
                            placeholder="hansen@student.untan.ac.id"
                        />
                        <div
                            v-if="form.errors.email"
                            class="text-red-500 text-xs mt-1 ml-1"
                        >
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label
                                class="text-[10px] font-bold text-[#8A6F57] uppercase tracking-widest ml-1"
                                >Password</label
                            >
                            <input
                                type="password"
                                v-model="form.password"
                                required
                                class="w-full mt-2 px-5 py-4 bg-[#FDF8F3] border-none rounded-2xl focus:ring-2 focus:ring-[#C49B72] text-sm"
                                placeholder="Min. 8 karakter"
                            />
                        </div>
                        <div>
                            <label
                                class="text-[10px] font-bold text-[#8A6F57] uppercase tracking-widest ml-1"
                                >Konfirmasi Password</label
                            >
                            <input
                                type="password"
                                v-model="form.password_confirmation"
                                required
                                class="w-full mt-2 px-5 py-4 bg-[#FDF8F3] border-none rounded-2xl focus:ring-2 focus:ring-[#C49B72] text-sm"
                                placeholder="Ulangi password"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-[#3B2B24] text-white py-5 rounded-2xl text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-[#C49B72] transition-all shadow-lg shadow-[#3B2B24]/10 mt-4"
                    >
                        Daftar Akun
                    </button>

                    <p class="text-center text-xs text-[#8A6F57] mt-8">
                        Sudah punya akun?
                        <Link
                            href="/login"
                            class="text-[#C49B72] font-bold hover:underline"
                            >Masuk sekarang</Link
                        >
                    </p>
                </form>
            </div>
        </div>
    </CoffeeLayout>
</template>

<script setup>
import CoffeeLayout from "@/Layouts/CoffeeLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>
