<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/LayoutPublico.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Recaptcha from '@/Components/Recaptcha.vue';
import { ref } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const { t } = useI18n();
const RECAPTCHA_SITE_KEY = usePage().props.recaptchaSiteKey;
const recaptchaRef = ref(null);

const form = useForm({
    email: '',
    password: '',
    remember: false,
    turnstile_token: '',
});

const onVerify  = (token) => { form.turnstile_token = token; };
const onExpire  = ()      => { form.turnstile_token = ''; };
const onError   = ()      => { form.turnstile_token = ''; };

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
        onError: () => {
            recaptchaRef.value?.reset();
            form.turnstile_token = '';
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.login')" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

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
                <InputLabel for="password" :value="t('auth.password')" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-tierra-700 dark:text-tierra-300">{{ t('auth.remember_me') }}</span>
                </label>
            </div>

            <!-- reCAPTCHA -->
            <div class="mt-4">
                <Recaptcha
                    ref="recaptchaRef"
                    :sitekey="RECAPTCHA_SITE_KEY"
                    @verify="onVerify"
                    @expire="onExpire"
                    @error="onError"
                />
                <InputError class="mt-1" :message="form.errors.turnstile_token" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-tierra-600 dark:text-tierra-400 underline hover:text-tierra-900 dark:hover:text-tierra-200 focus:outline-none focus:ring-2 focus:ring-tierra-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                >
                    {{ t('auth.forgot') }}
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing || !form.turnstile_token }"
                    :disabled="form.processing || !form.turnstile_token"
                >
                    {{ t('auth.login_btn') }}
                </PrimaryButton>
            </div>

            <div class="mt-6 rounded-lg border border-tierra-200 dark:border-tierra-800 bg-tierra-50 dark:bg-tierra-900/10 p-4 text-center">
                <p class="text-sm text-tierra-700 dark:text-tierra-300">{{ t('auth.no_account') }}</p>
                <Link
                    :href="route('register')"
                    class="mt-2 inline-flex items-center justify-center rounded-md border border-primary-500 dark:border-primary-600 px-4 py-2 text-sm font-medium text-primary-600 dark:text-primary-400 transition hover:bg-primary-50 dark:hover:bg-primary-900/20"
                >
                    {{ t('auth.create_account_link') }}
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
