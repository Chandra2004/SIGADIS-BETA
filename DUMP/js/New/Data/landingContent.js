export const navItems = [
    { label: 'Beranda', href: '/' },
    { label: 'Tentang', href: '/tentang' },
    { label: 'Fitur', href: '/fitur' },
    { label: 'Cara Kerja', href: '/cara-kerja' },
    { label: 'FAQ', href: '/faq' },
];

export const problems = [
    {
        title: 'Sulit mendapatkan bantuan dengan cepat',
        description:
            'Informasi kondisi dan lokasi belum tersampaikan secara efektif ketika terjadi keadaan darurat.',
    },
    {
        title: 'Pemantauan kesehatan belum terintegrasi',
        description:
            'Data dan riwayat kesehatan ibu dapat tersebar dan sulit dipantau secara berkelanjutan.',
    },
    {
        title: 'Komunikasi ibu dan tenaga pendamping terbatas',
        description:
            'Ibu membutuhkan akses komunikasi dan pendampingan yang lebih terhubung.',
    },
    {
        title: 'Informasi lokasi tidak selalu tersedia',
        description:
            'Dalam keadaan darurat, mengetahui lokasi ibu dapat membantu mempercepat respons.',
    },
];

// Ikon garis 24x24 buatan sendiri (currentColor) — tetap on-brand & bebas lisensi.
export const featureIcons = {
    '01': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.4 8.7 8 11 4.6-2.3 8-6 8-11V5l-8-3Z"/><path d="M12 8v5"/><path d="M12 16.5h.01"/></svg>',
    '02': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.6-9.5-9.1C.8 8.4 2.4 5 5.8 5c1.9 0 3.3 1 4.2 2.4C11 6 12.4 5 14.3 5c1.6 0 2.9.8 3.6 2M18 8v6M15 11h6"/></svg>',
    '03': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12a7 7 0 0 1 14 0v3a2 2 0 0 1-2 2h-1v-6h3M5 15h1v-6H3v3a2 2 0 0 0 2 2Z"/><path d="M8 19a4 4 0 0 0 4 2"/></svg>',
    '04': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h9l3 3v15H6Z"/><path d="M15 3v3h3M9 12h6M9 16h6M9 8h2"/></svg>',
    '05': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 5-2 6-2 8h16c0-2-2-3-2-8Z"/><path d="M10 21a2 2 0 0 0 4 0"/></svg>',
    '06': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.4"/><path d="M5 20c0-3.9 3.1-6 7-6s7 2.1 7 6"/></svg>',
};

export const features = [
    { number: '01', title: 'Emergency Button', description: 'Kirim permintaan bantuan darurat dengan cepat melalui satu tombol.' },
    { number: '02', title: 'Location Sharing', description: 'Bagikan lokasi ibu untuk membantu tenaga pendamping mengetahui posisi saat keadaan darurat.' },
    { number: '03', title: 'Pendampingan Bidan', description: 'Terhubung dengan bidan atau kader untuk mendapatkan pendampingan kesehatan.' },
    { number: '04', title: 'Riwayat Kesehatan', description: 'Simpan dan akses informasi serta riwayat kesehatan ibu secara terstruktur.' },
    { number: '05', title: 'Notifikasi', description: 'Dapatkan informasi dan pemberitahuan penting terkait pendampingan kesehatan.' },
    { number: '06', title: 'Profil Ibu', description: 'Kelola data diri, informasi kehamilan, kontak suami, bidan, dan kontak darurat.' },
];

export const emergencyFlow = [
    { label: 'DARURAT', active: true },
    { label: 'Ambil lokasi' },
    { label: 'Kirim laporan' },
    { label: 'Bidan / Kader' },
    { label: 'Respons bantuan' },
];

export const emergencyInfoCards = [
    { label: 'Identitas', value: 'Identitas pengguna' },
    { label: 'Waktu', value: 'Waktu kejadian' },
    { label: 'Lokasi', value: 'Lokasi' },
    { label: 'Status', value: 'Status laporan' },
];

// Tiap step sengaja dipetakan 1:1 ke pose maskot asli di aplikasi mobile, supaya
// landing page memperkenalkan karakter yang sama persis dengan yang ditemui di produk.
export const steps = [
    { number: '01', title: 'Daftar', description: 'Ibu membuat akun melalui website atau aplikasi.', mascotKey: 'menyambut' },
    { number: '02', title: 'Lengkapi Profil', description: 'Masukkan informasi penting seperti data diri dan kontak pendamping/darurat.', mascotKey: 'menjelaskan' },
    { number: '03', title: 'Gunakan SIGADIS', description: 'Pantau informasi kesehatan dan tetap terhubung dengan bidan/kader melalui aplikasi.', mascotKey: 'mendengarkan' },
    { number: '04', title: 'Aktifkan Bantuan Saat Darurat', description: 'Tekan tombol darurat dan sistem mengirimkan informasi yang diperlukan kepada pihak terkait.', mascotKey: 'siagaPenuh' },
];

export const roles = [
    { name: 'Ibu', description: 'Mendapatkan akses pendampingan dan bantuan ketika membutuhkan.', mascotKey: 'legaGembira' },
    {
        name: 'Bidan / Kader',
        description: 'Membantu memantau dan merespons kondisi ibu yang berada dalam pendampingan.',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3v6a4 4 0 0 0 8 0V3M10 9v3a5 5 0 0 0 5 5h0a5 5 0 0 0 5-5"/><circle cx="19" cy="6" r="2"/></svg>',
    },
    {
        name: 'Admin',
        description: 'Mengelola pengguna, data, dan operasional sistem.',
        icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.4 8.7 8 11 4.6-2.3 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>',
    },
];

export const advantages = [
    { title: 'Terintegrasi', description: 'Informasi ibu, pendampingan, dan laporan darurat berada dalam satu sistem.' },
    { title: 'Berbasis Lokasi', description: 'Informasi lokasi dapat digunakan dalam proses respons keadaan darurat.' },
    { title: 'Terhubung', description: 'Ibu dapat terhubung dengan bidan/kader yang mendampingi.' },
    { title: 'Responsif', description: 'Mempermudah penyampaian informasi ketika kondisi membutuhkan perhatian segera.' },
];

export const faqs = [
    { question: 'Apa itu SIGADIS?', answer: 'SIGADIS adalah Sistem Gawat Darurat Ibu-Selamat yang dirancang untuk mendukung pendampingan dan respons kesehatan ibu.' },
    { question: 'Siapa yang dapat menggunakan SIGADIS?', answer: 'Ibu/pasien, bidan/kader, dan admin sesuai dengan hak akses masing-masing.' },
    { question: 'Apakah SIGADIS dapat digunakan melalui website?', answer: 'Website digunakan untuk informasi, registrasi, dan akses publik. Fitur utama SIGADIS tersedia melalui aplikasi mobile.' },
    { question: 'Bagaimana cara menggunakan fitur darurat?', answer: 'Pengguna dapat menggunakan tombol darurat pada aplikasi untuk mengirimkan laporan sesuai mekanisme yang tersedia.' },
    { question: 'Apakah SIGADIS membutuhkan lokasi?', answer: 'Fitur tertentu, khususnya mekanisme bantuan darurat, dapat menggunakan informasi lokasi pengguna.' },
    { question: 'Bagaimana cara mendapatkan aplikasi SIGADIS?', answer: 'Pengguna dapat mengunduh aplikasi melalui tombol download yang tersedia pada website.' },
];

export const APK_URL = 'https://dev-sigadis-beta.rf.gd/application/SIGADIS.apk';
