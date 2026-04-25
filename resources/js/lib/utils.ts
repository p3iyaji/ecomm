import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function formatCurrency(amount: number, currency: string) {
    const n = Number(amount);
    const safe = Number.isFinite(n) ? n : 0;
    const code = String(currency ?? 'USD').toUpperCase();
    try {
        return new Intl.NumberFormat(undefined, { style: 'currency', currency: code }).format(safe);
    } catch {
        return `${code} ${safe.toFixed(2)}`;
    }
}
