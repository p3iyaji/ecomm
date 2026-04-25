<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { onMounted, ref, watch } from 'vue';
import { loadStripe } from '@stripe/stripe-js';
import type { Stripe } from '@stripe/stripe-js';
import ShopLayout from '@/layouts/ShopLayout.vue';

const page = usePage();

const props = defineProps<{
    cart: { id: number; items: { name: string; quantity: number; total: number }[] };
    clientSecret: string;
    stripeKey: string;
    summary: {
        subtotal: number;
        tax: number;
        shipping_cost: number;
        total: number;
        shipping_method: string;
    };
}>();

const processing = ref(false);
const stripeError = ref<string | null>(null);
const billingSameAsShipping = ref(true);

const form = useForm({
    payment_intent_id: '',
    shipping_method: props.summary.shipping_method,
    shipping_address: {
        address_line1: '',
        address_line2: '',
        city: '',
        state: '',
        postal_code: '',
        country: 'US',
        phone: '',
    },
    billing_address: {
        address_line1: '',
        address_line2: '',
        city: '',
        state: '',
        postal_code: '',
        country: 'US',
    },
});

let stripe: Stripe | null = null;
let elements: ReturnType<Stripe['elements']> | null = null;

async function mountPaymentElement(clientSecret: string) {
    const el = document.getElementById('payment-element');
    if (!el) {
        return;
    }
    el.innerHTML = '';
    if (!stripe) {
        stripe = await loadStripe(props.stripeKey);
    }
    if (!stripe) {
        stripeError.value = 'Unable to load Stripe.';
        return;
    }
    elements = stripe.elements({ clientSecret });
    elements.create('payment').mount('#payment-element');
}

onMounted(() => {
    mountPaymentElement(props.clientSecret);
});

watch(
    () => props.clientSecret,
    (secret) => {
        if (secret) {
            mountPaymentElement(secret);
        }
    },
);

const refreshCheckout = () => {
    router.get('/checkout', { shipping_method: form.shipping_method }, {
        preserveScroll: true,
        preserveState: true,
        only: ['clientSecret', 'summary', 'errors'],
    });
};

const submit = async () => {
    if (!stripe || !elements) {
        return;
    }
    processing.value = true;
    stripeError.value = null;

    if (billingSameAsShipping.value) {
        form.billing_address = {
            address_line1: form.shipping_address.address_line1,
            address_line2: form.shipping_address.address_line2 ?? '',
            city: form.shipping_address.city,
            state: form.shipping_address.state,
            postal_code: form.shipping_address.postal_code,
            country: form.shipping_address.country,
        };
    }

    const { error, paymentIntent } = await stripe.confirmPayment({
        elements,
        redirect: 'if_required',
    });

    if (error) {
        stripeError.value = error.message ?? 'Payment failed';
        processing.value = false;
        return;
    }

    if (paymentIntent?.status === 'succeeded') {
        form.payment_intent_id = paymentIntent.id;
        form.post('/checkout', {
            onFinish: () => {
                processing.value = false;
            },
        });
    } else {
        stripeError.value = 'Payment was not completed.';
        processing.value = false;
    }
};
</script>

<template>
    <Head title="Checkout" />
    <ShopLayout>
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-stone-900">Checkout</h1>
            <p class="mt-1 text-stone-600">Shipping, payment, and confirmation.</p>

            <div
                v-if="page.props.errors?.error"
                class="mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-800"
            >
                {{ page.props.errors.error }}
            </div>
            <div v-if="stripeError" class="mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">
                {{ stripeError }}
            </div>

            <div class="mt-8 grid gap-10 lg:grid-cols-5">
                <div class="space-y-8 lg:col-span-3">
                    <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-stone-900">Shipping address</h2>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="block sm:col-span-2">
                                <span class="text-sm font-medium text-stone-700">Address line 1</span>
                                <input
                                    v-model="form.shipping_address.address_line1"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"
                                    autocomplete="address-line1"
                                />
                            </label>
                            <label class="block sm:col-span-2">
                                <span class="text-sm font-medium text-stone-700">Address line 2 (optional)</span>
                                <input
                                    v-model="form.shipping_address.address_line2"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"
                                />
                            </label>
                            <label class="block">
                                <span class="text-sm font-medium text-stone-700">City</span>
                                <input
                                    v-model="form.shipping_address.city"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"
                                />
                            </label>
                            <label class="block">
                                <span class="text-sm font-medium text-stone-700">State / Region</span>
                                <input
                                    v-model="form.shipping_address.state"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"
                                />
                            </label>
                            <label class="block">
                                <span class="text-sm font-medium text-stone-700">Postal code</span>
                                <input
                                    v-model="form.shipping_address.postal_code"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"
                                />
                            </label>
                            <label class="block">
                                <span class="text-sm font-medium text-stone-700">Country</span>
                                <input
                                    v-model="form.shipping_address.country"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"
                                />
                            </label>
                            <label class="block sm:col-span-2">
                                <span class="text-sm font-medium text-stone-700">Phone</span>
                                <input
                                    v-model="form.shipping_address.phone"
                                    type="tel"
                                    class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-stone-900">Shipping method</h2>
                        <div class="mt-4 space-y-3">
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-stone-200 p-3 hover:bg-stone-50">
                                <input v-model="form.shipping_method" type="radio" value="standard" />
                                <span class="text-sm font-medium text-stone-800">Standard — $5.00 (5–7 days)</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-stone-200 p-3 hover:bg-stone-50">
                                <input v-model="form.shipping_method" type="radio" value="express" />
                                <span class="text-sm font-medium text-stone-800">Express — $15.00 (2–3 days)</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-stone-200 p-3 hover:bg-stone-50">
                                <input v-model="form.shipping_method" type="radio" value="next_day" />
                                <span class="text-sm font-medium text-stone-800">Next day — $25.00</span>
                            </label>
                        </div>
                        <button
                            type="button"
                            class="mt-4 w-full rounded-lg border border-amber-200 bg-amber-50 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100"
                            @click="refreshCheckout"
                        >
                            Update totals &amp; payment form
                        </button>
                    </section>

                    <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="text-lg font-semibold text-stone-900">Billing address</h2>
                            <label class="flex items-center gap-2 text-sm text-stone-600">
                                <input v-model="billingSameAsShipping" type="checkbox" class="rounded border-stone-300" />
                                Same as shipping
                            </label>
                        </div>
                        <div v-if="!billingSameAsShipping" class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="block sm:col-span-2">
                                <span class="text-sm font-medium text-stone-700">Address line 1</span>
                                <input
                                    v-model="form.billing_address.address_line1"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"
                                />
                            </label>
                            <label class="block">
                                <span class="text-sm font-medium text-stone-700">City</span>
                                <input
                                    v-model="form.billing_address.city"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"
                                />
                            </label>
                            <label class="block">
                                <span class="text-sm font-medium text-stone-700">Postal code</span>
                                <input
                                    v-model="form.billing_address.postal_code"
                                    type="text"
                                    class="mt-1 w-full rounded-md border-stone-300 text-sm shadow-sm"
                                />
                            </label>
                        </div>
                    </section>

                    <section class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-stone-900">Payment</h2>
                        <p class="mt-1 text-sm text-stone-500">Cards and wallets via Stripe.</p>
                        <div id="payment-element" class="mt-4" />
                    </section>
                </div>

                <aside class="lg:col-span-2">
                    <div class="sticky top-24 rounded-xl border border-stone-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-stone-900">Order summary</h2>
                        <ul class="mt-4 divide-y divide-stone-100 text-sm">
                            <li v-for="item in cart.items" :key="item.name + item.quantity" class="flex justify-between py-2">
                                <span class="text-stone-600">{{ item.name }} × {{ item.quantity }}</span>
                                <span class="font-medium text-stone-900">${{ Number(item.total).toFixed(2) }}</span>
                            </li>
                        </ul>
                        <dl class="mt-4 space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-stone-600">Subtotal</dt>
                                <dd class="font-medium">${{ Number(summary.subtotal).toFixed(2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-stone-600">Estimated tax</dt>
                                <dd class="font-medium">${{ Number(summary.tax).toFixed(2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-stone-600">Shipping</dt>
                                <dd class="font-medium">${{ Number(summary.shipping_cost).toFixed(2) }}</dd>
                            </div>
                            <div class="flex justify-between border-t border-stone-200 pt-2 text-base font-semibold">
                                <dt>Total</dt>
                                <dd>${{ Number(summary.total).toFixed(2) }}</dd>
                            </div>
                        </dl>
                        <button
                            type="button"
                            class="mt-6 w-full rounded-full bg-amber-600 py-3 text-sm font-semibold text-white shadow hover:bg-amber-700 disabled:opacity-60"
                            :disabled="processing"
                            @click="submit"
                        >
                            {{ processing ? 'Processing…' : 'Pay now' }}
                        </button>
                    </div>
                </aside>
            </div>
        </div>
    </ShopLayout>
</template>
