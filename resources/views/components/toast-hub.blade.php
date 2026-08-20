{{--
    Premium toast notification hub.

    Receives toasts from three sources:
    1. Session flashes: success / error / warning / info
    2. Livewire:  $this->dispatch('toast', variant: 'success', message: '...')
    3. JS:        window.dispatchEvent(new CustomEvent('toast', { detail: { variant, message } }))
--}}

<div
    x-data="{
        toasts: [],
        variants: {
            success: { ring: 'ring-green-100',  badge: 'bg-live',        bar: 'bg-live',        title: 'Done' },
            error:   { ring: 'ring-red-100',    badge: 'bg-critical',    bar: 'bg-critical',    title: 'Something went wrong' },
            warning: { ring: 'ring-amber-100',  badge: 'bg-brand-deep', bar: 'bg-brand-deep', title: 'Heads up' },
            info:    { ring: 'ring-indigo-100', badge: 'bg-ink',         bar: 'bg-ink',         title: 'Notice' },
        },
        add(detail) {
            if (! detail?.message && ! detail?.text) return;
            const id = Date.now() + Math.random();
            this.toasts.push({
                id,
                variant: this.variants[detail.variant] ? detail.variant : 'info',
                title: detail.title ?? null,
                message: detail.message ?? detail.text,
                duration: detail.duration ?? 5000,
                show: false,
            });
            this.$nextTick(() => {
                const t = this.toasts.find(t => t.id === id);
                if (t) t.show = true;
            });
            setTimeout(() => this.dismiss(id), detail.duration ?? 5000);
        },
        dismiss(id) {
            const t = this.toasts.find(t => t.id === id);
            if (! t || ! t.show) return;
            t.show = false;
            setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id) }, 350);
        },
    }"
    x-init="
        @if (session('success')) add({ variant: 'success', message: @js(session('success')) }); @endif
        @if (session('error')) add({ variant: 'error', message: @js(session('error')) }); @endif
        @if (session('warning')) add({ variant: 'warning', message: @js(session('warning')) }); @endif
        @if (session('info')) add({ variant: 'info', message: @js(session('info')) }); @endif
    "
    @toast.window="add($event.detail)"
    class="fixed top-5 right-5 z-[100] flex flex-col gap-3 w-[calc(100vw-2.5rem)] max-w-sm pointer-events-none"
    aria-live="polite"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.show"
            x-transition:enter="transition cubic-bezier(0.22,1,0.36,1) duration-400"
            x-transition:enter-start="opacity-0 translate-x-10 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-10 scale-95"
            class="pointer-events-auto relative overflow-hidden bg-paper border border-line shadow-xl shadow-ink/10 ring-4"
            :class="variants[toast.variant].ring"
        >
            <div class="flex items-start gap-3 p-4">
                {{-- Icon badge --}}
                <div class="w-7 h-7 flex items-center justify-center shrink-0 mt-px"
                     :class="variants[toast.variant].badge">
                    <template x-if="toast.variant === 'success'">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <template x-if="toast.variant === 'error'">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </template>
                    <template x-if="toast.variant === 'warning'">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008z"/></svg>
                    </template>
                    <template x-if="toast.variant === 'info'">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                    </template>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0 pt-0.5">
                    <p class="text-sm font-semibold text-ink leading-tight"
                       x-text="toast.title ?? variants[toast.variant].title"></p>
                    <p class="text-[13px] text-ink-soft leading-snug mt-1" x-text="toast.message"></p>
                </div>

                {{-- Dismiss --}}
                <button type="button"
                        @click="dismiss(toast.id)"
                        class="shrink-0 w-6 h-6 flex items-center justify-center text-ink-faint hover:text-ink transition-colors duration-150">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Progress bar --}}
            <div class="absolute bottom-0 inset-x-0 h-0.5 bg-line-soft">
                <div class="h-full"
                     :class="variants[toast.variant].bar"
                     :style="`animation: toast-progress ${toast.duration}ms linear forwards`"></div>
            </div>
        </div>
    </template>
</div>
