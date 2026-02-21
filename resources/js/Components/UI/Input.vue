<script setup>
const model = defineModel({
    type: [String, Number],
    default: '',
});
</script>

<template>
    <input v-model="model" class="za-input" />
</template>
