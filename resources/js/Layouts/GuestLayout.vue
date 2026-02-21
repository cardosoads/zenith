<template>
    <div class="relative min-h-screen overflow-hidden bg-[var(--za-bg)] px-4 py-8">
        <div class="pointer-events-none absolute inset-0 opacity-20" style="background: var(--gradient-top-right)"></div>
        <div class="relative mx-auto w-full max-w-xl">
            <div class="za-card border-[var(--za-border-strong)] p-6 md:p-8">
                <div class="mb-8">
                    <p class="text-xs uppercase tracking-[0.18em] text-[var(--za-text-muted)]">Zenith Agenda</p>
                    <h1 class="font-[var(--za-font-display)] text-3xl">Autenticação</h1>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
