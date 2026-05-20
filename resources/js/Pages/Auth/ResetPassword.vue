<script setup>
import GuestLayout from '@/Layouts/LayoutPublico.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PasswordStrength from '@/Components/PasswordStrength.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
import { ref } from 'vue';
import { evaluarPassword } from '@/Composables/useValidaciones';
import { Eye, EyeOff } from 'lucide-vue-next';

const { t } = useI18n();

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const erroresLocales = ref({});
const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const submit = () => {
    const e = {};
    const pw = evaluarPassword(form.password);
    if (!pw.valida) e.password = 'La contraseña debe tener 8+ caracteres, mayúsculas, minúsculas, números y símbolos.';
    if (form.password !== form.password_confirmation) e.password_confirmation = 'Las contraseñas no coinciden.';
    erroresLocales.value = e;
    if (Object.keys(e).length) return;

    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.reset_title')" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" :value="t('auth.email_label')" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" :value="t('auth.reset_new_password')" />

                <div class="relative mt-1">
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pr-10"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        minlength="8"
                        maxlength="128"
                    />
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <EyeOff v-if="showPassword" class="h-4 w-4" />
                        <Eye v-else class="h-4 w-4" />
                    </button>
                </div>

                <InputError class="mt-2" :message="erroresLocales.password || form.errors.password" />
                <PasswordStrength :password="form.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    :value="t('auth.reset_confirm_password')"
                />

                <div class="relative mt-1">
                    <TextInput
                        id="password_confirmation"
                        :type="showPasswordConfirm ? 'text' : 'password'"
                        class="block w-full pr-10"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        minlength="8"
                        maxlength="128"
                    />
                    <button type="button" @click="showPasswordConfirm = !showPasswordConfirm"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <EyeOff v-if="showPasswordConfirm" class="h-4 w-4" />
                        <Eye v-else class="h-4 w-4" />
                    </button>
                </div>

                <InputError
                    class="mt-2"
                    :message="erroresLocales.password_confirmation || form.errors.password_confirmation"
                />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ t('auth.reset_btn') }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
