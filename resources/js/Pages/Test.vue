<template>
    <Layout>
        <Head title="Dashboard" />
        <h1 class="text-3xl font-bold underline">
            Hello
        </h1>
        <p>Hello {{ user.name }}, welcome to your first Inertia app!</p>
    </Layout>
</template>

<script>
import Layout from './Layout.vue'
import { Head } from '@inertiajs/inertia-vue3'

export default {
    components: {
        Head,
        Layout,
    },
    props: {
        user: Object,
    },
}
</script>
