<template>
    <li :key="promotion.id" class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white shadow">
        <div class="flex w-full items-center justify-between space-x-6 p-6">
            <div class="flex-1 truncate">
                <div class="flex items-center space-x-3">
                    <h3 class="truncate text-sm font-medium text-gray-900">{{ promotion.name }}</h3>
                    <span class="inline-block flex-shrink-0 rounded-full  px-2 py-0.5 text-xs font-medium" :class="[ promotion.active ? 'text-green-800 bg-green-100' : 'text-red-800 bg-red-100']">{{ promotion.active ? 'Active': 'Inactive' }}</span>
                </div>
                <p class="mt-1 truncate text-sm text-gray-500">{{ promotion.subtitle }}</p>
            </div>
            <img class="h-10 w-10 flex-shrink-0 rounded-full bg-gray-300" :src="image" alt="" />
        </div>
        <div>
            <div class="-mt-px flex divide-x divide-gray-200">
                <div class="flex w-0 flex-1">

                </div>
                <div class="-ml-px flex w-0 flex-1">

                </div>
            </div>
        </div>
    </li>
</template>

<script>
import { EnvelopeIcon, PhoneIcon } from '@heroicons/vue/20/solid'
export default {
    name: "PromotionItem",
    props: ['promotion'],
    components: {
        EnvelopeIcon,
        PhoneIcon
    },
    data() {
        return {}
    },
    computed: {
        image() {
            if (this.promotion.image === null) {
                return `https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80`
            } else {
                return this.promotion.image.path
            }
        }
    },
    methods: {}
}
</script>

<style scoped>

</style>
