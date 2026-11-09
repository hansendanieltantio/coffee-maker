<template>
    <CoffeeLayout>
        <Head title="Masuk" />

        <div class="flex items-center justify-center min-h-[80vh] px-4 py-12">
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
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                        />
                    </svg>
                </div>

                <div class="text-center mb-10">
                    <h2 class="text-3xl font-serif font-bold text-[#3B2B24]">
                        Selamat Datang
                    </h2>
                    <p class="text-sm text-[#8A6F57] mt-2">
                        Silakan masuk untuk memantau antrean
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label
                            class="text-[10px] font-bold text-[#8A6F57] uppercase tracking-widest ml-1"
                            >Alamat Email</label
                        >
                        <input
                            type="email"
                            v-model="form.email"
                            required
                            class="w-full mt-2 px-5 py-4 bg-[#FDF8F3] border-none rounded-2xl focus:ring-2 focus:ring-[#C49B72] text-sm text-[#3B2B24]"
                            placeholder="nama@email.com"
                        />
                        <div
                            v-if="form.errors.email"
                            class="text-red-500 text-xs mt-1 ml-1"
                        >
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div>
                        <label
                            class="text-[10px] font-bold text-[#8A6F57] uppercase tracking-widest ml-1"
                            >Password</label
                        >
                        <input
                            type="password"
                            v-model="form.password"
                            required
                            class="w-full mt-2 px-5 py-4 bg-[#FDF8F3] border-none rounded-2xl focus:ring-2 focus:ring-[#C49B72] text-sm text-[#3B2B24]"
                            placeholder="••••••••"
                        />
                    </div>

                    <!-- <div class="flex items-center justify-between px-1">
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="rounded border-[#FBF3E8] text-[#C49B72] focus:ring-[#C49B72]"
                            />
                            <span class="ml-2 text-xs text-[#8A6F57]"
                                >Ingat saya</span
                            >
                        </label>
                    </div> -->

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-[#3B2B24] text-white py-5 rounded-2xl text-[11px] font-bold uppercase tracking-[0.2em] hover:bg-[#C49B72] transition-all shadow-lg shadow-[#3B2B24]/10 disabled:opacity-50"
                    >
                        {{
                            form.processing ? "Memproses..." : "Masuk Sekarang"
                        }}
                    </button>

                    <p class="text-center text-xs text-[#8A6F57] mt-8">
                        Belum punya akun?
                        <Link
                            href="/register"
                            class="text-[#C49B72] font-bold hover:underline"
                            >Daftar di sini</Link
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
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>
