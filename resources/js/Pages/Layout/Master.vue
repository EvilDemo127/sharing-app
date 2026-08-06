<template>
    <!-- Fixed Side Navigation -->
    <Nav />

    <!-- Content area: offset to the right of the fixed side nav on lg+ screens -->
    <div class="content-with-sidenav">
        <main class="container-fluid py-4 px-3 px-lg-4">
            <div class="row g-4">
                <section class="col-12 col-md-8">
                    <slot />
                </section>
                <!-- Right Panel: Responsive Sidebar (12 columns on Mobile, 4 on Desktop) -->
                <aside class="col-12 col-md-4">
                    <SideBar />
                </aside>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { toast } from 'vue3-toastify';
import Nav from './Nav.vue';
import SideBar from './SideBar.vue';

defineOptions({
    name: 'Master',
});

// Global Notification Alert Trigger
const notify = () => {
    toast("Wow so easy!", {
        autoClose: 1000,
        position: toast.POSITION.BOTTOM_RIGHT,
    });
};
</script>

<style scoped>
/* Nav.vue's side-nav is fixed & 260px wide on lg+ screens (position: fixed,
   left: 0). Push the content area over so it doesn't sit underneath it.
   Below lg the side-nav is off-canvas, so no offset is needed there. */
.content-with-sidenav {
    min-height: 100vh;
}

@media (min-width: 992px) {
    .content-with-sidenav {
        margin-left: 260px;
    }
}
</style>