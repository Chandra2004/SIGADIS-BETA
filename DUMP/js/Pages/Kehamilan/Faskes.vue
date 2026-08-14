<script setup>
import { Head, router } from '@inertiajs/vue3';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue';
import AccountDrawer from '@/Components/Shared/AccountDrawer.vue';
import BottomTabBar from '@/Components/Shared/BottomTabBar.vue';
import Icon from '@/Components/Shared/Icon.vue';
import LogoutConfirmDialog from '@/Components/Shared/LogoutConfirmDialog.vue';
import TopAppBar from '@/Components/Shared/TopAppBar.vue';

const props = defineProps({
    facilities: { type: Array, required: true },
    midwife: { type: Object, default: null },
});

const showAccountMenu = ref(false);
const showLogoutConfirm = ref(false);

function logout() {
    router.post(route('auth.pregnant.logout'));
}

const typeLabel = {
    puskesmas: 'Puskesmas',
    pustu: 'Pustu',
    polindes: 'Polindes',
    rumah_sakit: 'Rumah Sakit',
    klinik: 'Klinik',
};

const search = ref('');

const filteredFacilities = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) return props.facilities;
    return props.facilities.filter((f) => f.name.toLowerCase().includes(query) || f.address.toLowerCase().includes(query));
});

const facilitiesWithLocation = computed(() => props.facilities.filter((f) => f.latitude && f.longitude));

function waLink(phone) {
    return `https://wa.me/62${phone.replace(/^0/, '')}`;
}

function directionsLink(facility) {
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(`${facility.name} ${facility.address}`)}`;
}

// Leaflet + OpenStreetMap -- gratis, tanpa API key, cukup buat pin lokasi faskes.
const mapEl = ref(null);
let map = null;
let markers = [];
let myLocationMarker = null;
const locating = ref(false);
const locateError = ref('');

function renderMarkers() {
    markers.forEach((m) => m.remove());
    markers = facilitiesWithLocation.value.map((f) => L.marker([f.latitude, f.longitude])
        .addTo(map)
        .bindPopup(`<strong>${f.name}</strong><br>${typeLabel[f.type] ?? f.type}<br>${f.address}`));
}

onMounted(async () => {
    await nextTick();
    if (!mapEl.value) return;

    const center = facilitiesWithLocation.value[0]
        ? [facilitiesWithLocation.value[0].latitude, facilitiesWithLocation.value[0].longitude]
        : [-2.5489, 118.0149]; // fallback: tengah Indonesia kalau belum ada faskes berkoordinat

    map = L.map(mapEl.value).setView(center, facilitiesWithLocation.value[0] ? 13 : 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    renderMarkers();

    if (facilitiesWithLocation.value.length > 1) {
        map.fitBounds(L.latLngBounds(facilitiesWithLocation.value.map((f) => [f.latitude, f.longitude])), { padding: [24, 24] });
    }
});

onBeforeUnmount(() => map?.remove());

// Lokasi Ibu diminta manual lewat tombol, bukan otomatis saat halaman dibuka -- konsisten dengan pola izin GPS di tombol SOS.
function locateMe() {
    if (!navigator.geolocation) {
        locateError.value = 'Perangkat tidak mendukung deteksi lokasi.';
        return;
    }

    locating.value = true;
    locateError.value = '';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            const { latitude, longitude } = position.coords;
            myLocationMarker?.remove();
            myLocationMarker = L.circleMarker([latitude, longitude], {
                radius: 8, color: '#2c4a6e', fillColor: '#f3aec0', fillOpacity: 1,
            }).addTo(map).bindPopup('Lokasi Ibu').openPopup();
            map.setView([latitude, longitude], 14);
            locating.value = false;
        },
        () => {
            locateError.value = 'Gagal mengambil lokasi. Pastikan izin lokasi diaktifkan.';
            locating.value = false;
        },
    );
}
</script>

<template>
    <Head title="Info Faskes Terdekat" />

    <div class="min-h-screen bg-brand-pink-50 pb-24">
        <TopAppBar title="Faskes" @menu="showAccountMenu = !showAccountMenu" />
        <AccountDrawer :show="showAccountMenu" @close="showAccountMenu = false" @logout="showAccountMenu = false; showLogoutConfirm = true" />
        <LogoutConfirmDialog
            :show="showLogoutConfirm"
            message="Ibu perlu memasukkan nomor HP lagi untuk masuk kembali."
            @confirm="logout"
            @cancel="showLogoutConfirm = false"
        />

        <div class="mx-auto w-full max-w-md px-6 py-6">
            <input
                v-model="search"
                type="text"
                placeholder="Cari Puskesmas / Rumah Sakit terdekat..."
                class="input input-bordered mb-4 w-full bg-white"
            />

            <div class="relative mb-4">
                <div ref="mapEl" class="h-56 w-full overflow-hidden rounded-xl border border-brand-navy-100" />
                <button
                    type="button"
                    class="btn btn-sm absolute top-3 right-3 z-[1000] gap-1 border-none bg-white shadow"
                    :disabled="locating"
                    @click="locateMe"
                >
                    <Icon name="location" size="h-4 w-4" class="text-brand-navy-900" />
                    {{ locating ? 'Mencari...' : 'Lokasi Saya' }}
                </button>
            </div>
            <p v-if="locateError" class="mb-4 text-xs text-[--color-error-form]">{{ locateError }}</p>

            <div v-if="midwife" class="mb-4 flex items-center justify-between rounded-lg bg-brand-navy-900 p-3 text-sm text-white">
                <div>
                    <p class="text-xs text-white/70 uppercase">Bidan Pendamping Ibu</p>
                    <p class="font-semibold">{{ midwife.full_name }}</p>
                </div>
                <a :href="waLink(midwife.phone_number)" target="_blank" rel="noopener" class="btn btn-sm gap-1 border-none bg-[--color-brand-pink-500] text-white">
                    <Icon name="phone" size="h-4 w-4" /> WA
                </a>
            </div>

            <h2 class="mb-3 text-sm font-bold text-brand-navy-900">Faskes Terdekat</h2>

            <div v-if="filteredFacilities.length === 0" class="text-center">
                <img src="/assets/images/mascot/pose-18-menunggu-santai.png" alt="" class="mx-auto mb-3 h-24 w-24 object-contain" />
                <p class="text-sm text-brand-navy-700">Tidak ada faskes yang cocok.</p>
            </div>

            <div v-for="f in filteredFacilities" :key="f.id" class="mb-3 rounded-lg border border-brand-navy-100 bg-white p-4">
                <p class="font-bold text-brand-navy-900">{{ f.name }}</p>
                <p class="mb-2 text-sm text-brand-navy-700">{{ typeLabel[f.type] ?? f.type }} &middot; {{ f.address }}</p>

                <div class="mb-3 flex flex-wrap gap-1">
                    <span v-if="f.has_icu" class="rounded-full bg-risk-low-bg px-2 py-0.5 text-xs text-risk-low">ICU Tersedia</span>
                    <span v-if="f.has_nicu" class="rounded-full bg-risk-low-bg px-2 py-0.5 text-xs text-risk-low">
                        NICU {{ f.nicu_bed_count ? `(${f.nicu_bed_count} Bed)` : 'Tersedia' }}
                    </span>
                    <span v-if="f.ambulance_status" class="flex items-center gap-1 rounded-full bg-brand-navy-100 px-2 py-0.5 text-xs text-brand-navy-900">
                        <Icon name="truck" size="h-3.5 w-3.5" />
                        {{ f.ambulance_status === 'siaga' ? 'Ambulans Siaga' : f.ambulance_status === 'dalam_perjalanan' ? 'Ambulans Dalam Perjalanan' : 'Ambulans Tidak Tersedia' }}
                    </span>
                </div>

                <div class="flex gap-2">
                    <a v-if="f.phone_number" :href="`tel:${f.phone_number}`" class="btn btn-sm flex-1 border-none bg-brand-navy-900 text-white">
                        Telepon
                    </a>
                    <a :href="directionsLink(f)" target="_blank" rel="noopener" class="btn btn-sm btn-outline flex-1 border-brand-navy-100 text-brand-navy-700">
                        Petunjuk Arah
                    </a>
                </div>
            </div>
        </div>

        <BottomTabBar active="faskes" />
    </div>
</template>
