<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
</script>

<template>
    <div class="guest-layout">
        <slot />
    </div>
</template>

<style lang="scss" scoped>
@use 'variables' as *;

.guest-layout {
    min-height: 100vh;
    background-color: $color-gray-100;
}
</style>