<template>
    <Layout :user="user">
        <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <PromotionItem :promotion="promotion" v-for="promotion in promotions" />
        </ul>
    </Layout>
</template>

<script>
import PromotionItem from '@/Components/PromotionItem.vue';
import Layout from '@/Pages/Layout.vue';
export default {
    name: "Index",
    props: ['promotions', 'user'],
    components: {
        PromotionItem,
        Layout
    },
    data() {
        return {}
    },
    methods: {}
}
</script>

<style scoped>

</style>
