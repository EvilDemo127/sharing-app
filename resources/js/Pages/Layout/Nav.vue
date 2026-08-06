<template>
    <!-- Mobile top bar (shown only below lg breakpoint) -->
    <div
        class="mobile-topbar d-lg-none d-flex align-items-center justify-content-between bg-white border-bottom px-3 py-2"
    >
        <button
            class="btn border border-light-subtle p-2 shadow-none rounded-3"
            type="button"
            :aria-expanded="mobileMenuOpen"
            aria-label="Toggle navigation"
            @click="toggleMobileMenu"
        >
            <i class="fas fa-bars fs-5 text-dark"></i>
        </button>
        <span class="fw-bold text-primary">Menu</span>
        <div style="width: 38px"></div>
    </div>

    <!-- Overlay shown behind the sidebar on mobile when it's open -->
    <div
        v-if="mobileMenuOpen"
        class="side-nav-overlay d-lg-none"
        @click="toggleMobileMenu"
    ></div>

    <!-- Side Nav -->
    <aside
        class="side-nav bg-white border-end d-flex flex-column"
        :class="{ 'side-nav-open': mobileMenuOpen }"
    >
        <!-- Brand / close button (mobile) -->
        <div
            class="px-3 py-3 border-bottom border-light-subtle d-flex align-items-center justify-content-between"
        >
            <Link :href="route('home')" class="fw-bold text-primary fs-5 text-decoration-none">
                Brand
            </Link>
            <button
                type="button"
                class="btn-close d-lg-none"
                aria-label="Close navigation"
                @click="toggleMobileMenu"
            ></button>
        </div>

        <!-- Floating Search Input -->
        <div class="px-3 py-3">
            <form @submit.prevent="LandC.searching">
                <div
                    class="input-group shadow-sm rounded-pill overflow-hidden bg-white border border-light"
                >
                    <span
                        class="input-group-text bg-white border-0 ps-3 pe-1 text-muted"
                    >
                        <i class="fas fa-search"></i>
                    </span>
                    <input
                        v-model="LandC.search.value"
                        name="search"
                        type="search"
                        id="form1"
                        class="form-control border-0 py-2 shadow-none text-dark ps-1"
                        placeholder="Search questions, topics or tags..."
                        style="font-size: 0.9rem"
                    />
                </div>
            </form>
        </div>

        <!-- Nav Links (vertical) -->
        <ul class="nav flex-column px-2 gap-1 flex-grow-1 overflow-auto">
            <li class="nav-item">
                <Link
                    class="side-link active-link"
                    :href="route('home')"
                >
                    <i class="fas fa-list-ul"></i>
                    <span>All Questions</span>
                </Link>
            </li>
            <li class="nav-item">
                <a class="side-link" href="#">
                    <i class="fas fa-check-circle"></i>
                    <span>Answered</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="side-link" href="#">
                    <i class="fas fa-circle-notch"></i>
                    <span>Unanswered</span>
                </a>
            </li>

            <li class="nav-item mt-2 pt-2 border-top border-light-subtle"></li>
            
            <!-- Messages row -->
            <li class="nav-item">
                <Link
                    :href="route('message')"
                    class="side-link justify-content-between"
                >
                    <span class="d-flex align-items-center gap-3">
                        <i class="fas fa-comment"></i>
                        <span>Messages</span>
                    </span>
                    <span
                        v-if="unreadMessage > 0"
                        class="badge rounded-pill bg-danger font-monospace"
                        style="font-size: 0.65rem"
                    >
                        {{ unreadMessage }}
                    </span>
                </Link>
            </li>
            
            <!-- Notifications row (with flyout dropdown) -->
            <li class="nav-item  position-relative" ref="notifRef">
                <a
                    class="side-link justify-content-between"
                    href="#"
                    role="button"
                    @click.prevent="toggleNotif"
                >
                    <span class="d-flex align-items-center gap-3">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </span>
                    <span v-if="notiCount >0"
                        class="badge rounded-pill bg-danger font-monospace"
                        style="font-size: 0.65rem"
                    >
                        {{ notiCount }}
                    </span>
                </a>
                <ul
                    :class="{ show: notifOpen }"
                    class="dropdown-menu side-flyout border border-light-subtle shadow-sm p-1 rounded-2"
                    style="min-width: 230px"
                >
                    <li>
                        <h6
                            class="dropdown-header fw-bold text-dark px-3 py-2 fs-6"
                        >
                            Notifications
                        </h6>
                    </li>
                    <li><hr class="dropdown-divider my-1 opacity-75" /></li>
                    
                    <li v-for="noti in notification" :key="noti.id" class="d-flex flex-colum">
                        <Link
                            class="dropdown-item rounded-1 py-2 px-3 text-secondary"
                            :href="noti.data?.url ?? '#'"
                            @click.prevent="makeRead(noti)"
                            >{{ noti.data?.message ?? 'New notification' }}</Link
                        >
                    </li>
                </ul>
            </li>

            
            
        </ul>

        <!-- Bottom section: avatar -->
        <div class="border-top border-light-subtle px-2 py-3">
            <!-- Avatar Dropdown -->
            <div class="dropdown position-relative" ref="avatarRef">
                <div
                    @click="toggleAvatarMenu"
                    class="d-flex align-items-center gap-2 rounded-3 p-2 hover-icon"
                    role="button"
                    style="cursor: pointer"
                >
                    <img
                        :src="
                            user.image
                                ? `https://lh3.googleusercontent.com/d/${user.image}`
                                : '/images/default-avatar.png'
                        "
                        class="rounded-circle border flex-shrink-0"
                        style="width: 32px; height: 32px; object-fit: cover"
                        alt="User Avatar"
                    />
                    <span class="fw-semibold text-dark text-truncate small" style="max-width: 120px">{{
                        user && user.name ? user.name : "User Name"
                    }}</span>
                    <i class="fas fa-chevron-up ms-auto small text-muted"></i>
                </div>

                <!-- Premium Multi-tier Dropdown Card (opens upward, sits at bottom of sidebar) -->
                <ul
                    :class="{ show: dropDownOpen }"
                    class="dropdown-menu dropup-menu border border-light-subtle shadow-sm mb-2 p-1 rounded-2"
                    style="min-width: 220px"
                >
                    <li>
                        <div
                            class="px-3 py-2 mb-1 border-bottom border-light"
                        >
                            <span
                                class="fw-bold text-dark text-truncate d-block fs-6"
                                style="max-width: 180px"
                                >{{
                                    user && user.name
                                        ? user.name
                                        : "User Name"
                                }}</span
                            >
                            <span
                                class="text-muted small text-truncate d-block"
                                style="max-width: 180px"
                                >{{
                                    user && user.email
                                        ? user.email
                                        : "user@email.com"
                                }}</span
                            >
                        </div>
                    </li>
                    <li>
                        <Link
                            class="dropdown-item rounded-1 py-2 px-3 d-flex align-items-center gap-2 text-secondary"
                            :href="route('profile.edit')"
                        >
                            <i
                                class="fas fa-user-cog text-muted"
                                style="width: 18px"
                            ></i>
                            Account Settings
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="route('question.own')"
                            class="dropdown-item rounded-1 py-2 px-3 d-flex align-items-center gap-2 text-secondary"
                        >
                            <i
                                class="fas fa-question-circle text-muted"
                                style="width: 18px"
                            ></i>
                            My Questions
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="route('question.save')"
                            class="dropdown-item rounded-1 py-2 px-3 d-flex align-items-center gap-2 text-secondary"
                        >
                            <i
                                class="fas fa-bookmark text-muted"
                                style="width: 18px"
                            ></i>
                            Saved Lists
                        </Link>
                    </li>
                    <li><hr class="dropdown-divider my-1 opacity-75" /></li>
                    <li>
                        <a
                            class="dropdown-item rounded-1 py-2 px-3 d-flex align-items-center gap-2 text-danger fw-bold"
                            href="/logout"
                        >
                            <i
                                class="fas fa-sign-out-alt"
                                style="width: 18px"
                            ></i>
                            Sign Out
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </aside>

    <!--
        NOTE for parent layout:
        The sidebar is fixed and 260px wide on lg+ screens (see .side-nav below).
        Wrap your page content in an element with `margin-left: 260px` (or a
        `.content-with-sidenav` class) on lg+ screens so content doesn't sit
        underneath the sidebar. On mobile the sidebar is off-canvas, so no
        margin is needed there.
    -->
</template>

<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { LikeAndCom } from "../Comp/LikeAndCom";
import { computed, ref, onMounted, onUnmounted } from "vue";
import Echo from "laravel-echo";
import axios from "axios";

const authId = usePage().props.user.id;
const notification= ref([]);
const notiCount=ref(0);

defineOptions({
    name: "Nav",
});

const unreadMessage = computed(() => usePage().props.unreadMessage);

// Reactive States
const notifOpen = ref(false);
const dropDownOpen = ref(false);
const mobileMenuOpen = ref(false);

// Template refs used for outside-click detection
const notifRef = ref(null);
const avatarRef = ref(null);

const toggleNotif = () => {
    notifOpen.value = !notifOpen.value;
    if (notifOpen.value) dropDownOpen.value = false;
};

const toggleAvatarMenu = () => {
    dropDownOpen.value = !dropDownOpen.value;
    if (dropDownOpen.value) notifOpen.value = false;
};

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
};

// Close dropdowns when clicking anywhere outside of them
const handleClickOutside = (event) => {
    if (notifOpen.value && notifRef.value && !notifRef.value.contains(event.target)) {
        notifOpen.value = false;
    }
    if (dropDownOpen.value && avatarRef.value && !avatarRef.value.contains(event.target)) {
        dropDownOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener("click", handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener("click", handleClickOutside);
});

const user = usePage().props.user;
const LandC = LikeAndCom();

onMounted(async()=>{
    window.Echo.private(`chat.${authId}`)
    .listen(".MessageSent", () => {
        unreadMessage.value++;
    })
    .listen(".MessageRead", () => {
        unreadMessage.value--;
    });

    

// noti
    await loadNoti()
    await realTimeNoti()

})

//get real time noti
function realTimeNoti(){
    window.Echo
    .private(`App.Models.User.${authId}`)
    .notification((noti)=>{
        notification.value.unshift(noti)
        notiCount.value++
        console.log(notiCount.value);
        
    });
}
    //get all noti
 function loadNoti(){
     axios.get(route('real_time_noti'))
    .then(res=>{
       notification.value = res.data.noti
       notiCount.value=notification.value.filter(n=> !n.read_at).length

    })
    .catch(err=>console.log(err)
    )
}
    
const makeRead=(noti)=>{
    if(noti.read_at) return
    noti.read_at=new Date().toISOString()
   

    axios.post(route('read_noti',noti.id))
    .then(res=>{
        if(res.data.success && notiCount.value > 0)
    {
notiCount.value--
    }
    })
    .catch(err=>{
        noti.read_at=null
        notiCount.value++
        console.log(err)
    }
    )
    
}

</script>

<style scoped>
/* ---------- Side Nav layout ---------- */
.side-nav {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 260px;
    z-index: 1040;
    transition: transform 0.25s ease-in-out;
}

.mobile-topbar {
    position: sticky;
    top: 0;
    z-index: 1030;
}

.side-nav-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1035;
}

/* Off-canvas on mobile, always visible on lg+ */
@media (max-width: 991.98px) {
    .side-nav {
        transform: translateX(-100%);
    }
    .side-nav.side-nav-open {
        transform: translateX(0);
    }
}

/* ---------- Nav links ---------- */
.side-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 0.9rem;
    border-radius: 8px;
    color: #495057;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.15s ease-in-out;
}
.side-link i {
    width: 18px;
    text-align: center;
}
.side-link:hover {
    background-color: rgba(18, 102, 241, 0.06);
    color: #1266f1;
}
.side-link.active-link {
    background-color: rgba(18, 102, 241, 0.08);
    color: #1266f1;
    font-weight: 700;
}

/* ---------- Dropdown fixes ---------- */
.hover-icon {
    transition: background-color 0.2s;
}
.hover-icon:hover {
    background-color: rgba(0, 0, 0, 0.05);
}
.dropdown-item {
    transition: all 0.15s ease;
}
.dropdown-item:hover {
    background-color: rgba(18, 102, 241, 0.06);
    color: #1266f1;
}

/* Both dropdown menus are hidden by default and only shown via the `show`
   class bound in the template (v-bind :class="{ show: ... }") - this is
   what was missing/broken for the notifications dropdown before. */
.dropdown-menu {
    display: none;
    position: absolute;
}
.dropdown-menu.show {
    display: block;
    animation: fadeIn 0.15s ease-out;
}

/* Notification dropdown opens below+right of the bell */
.dropdown-menu {
    top: 100%;
    left: 0;
}

/* Avatar dropdown opens upward since the trigger sits at the bottom of the sidebar */
.dropup-menu {
    bottom: 100%;
    top: auto;
    left: 0;
}

/* Notifications row now lives inside the nav list, so its dropdown flies
   out to the right instead of opening below the trigger */
/* .side-flyout {
    top: 0;
    left: 100%;
    margin-left: 8px;
} */
@media (max-width: 991.98px) {
    /* On the narrower off-canvas sidebar there's no room to the right,
       so fall back to opening below the row */
    .side-flyout {
        left: 0;
        top: 100%;
        margin-left: 0;
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>