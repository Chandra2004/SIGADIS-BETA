/**
 * Peta 22 pose maskot SIGADIS -> nama semantik, deskripsi, dan alt text.
 * File aset diasumsikan berada di: public/assets/mascot/mascot-pose-{n}.webp
 *
 * Pakai lewat helper `mascot('menyambut')` daripada `mascot(1)` di komponen,
 * supaya jelas pose mana yang dipakai di konteks apa tanpa perlu buka daftar ini.
 */
const MASCOT_POSES = {
    menyambut: { n: 1, alt: 'Maskot SIGADIS menyambut pengguna baru', context: 'Onboarding / Registrasi' },
    menjelaskan: { n: 2, alt: 'Maskot SIGADIS menjelaskan pengisian data', context: 'Isi Data & Informed Consent' },
    mendengarkan: { n: 3, alt: 'Maskot SIGADIS mendengarkan proses skrining', context: 'Skrining Berjalan' },
    waspadaTenang: { n: 4, alt: 'Maskot SIGADIS waspada namun tenang', context: 'Jawaban Gejala Berat' },
    legaGembira: { n: 5, alt: 'Maskot SIGADIS lega dan gembira', context: 'Hasil Risiko Rendah' },
    peduliSerius: { n: 6, alt: 'Maskot SIGADIS peduli dan serius', context: 'Hasil Risiko Sedang' },
    sigapMendukung: { n: 7, alt: 'Maskot SIGADIS sigap mendukung', context: 'Hasil Risiko Tinggi' },
    melambaiRamah: { n: 8, alt: 'Maskot SIGADIS melambai ramah', context: 'Pengingat Skrining Berkala' },
    siagaPenuh: { n: 9, alt: 'Maskot SIGADIS terbang siaga penuh saat darurat', context: 'Aktivasi Darurat' },
    menemaniBerjaga: { n: 10, alt: 'Maskot SIGADIS menemani sambil berjaga', context: 'Menunggu Respons Bidan' },
    menunjukArah: { n: 11, alt: 'Maskot SIGADIS menunjuk arah rujukan', context: 'Info Rujukan Faskes' },
    merenungRamah: { n: 12, alt: 'Maskot SIGADIS merenung ramah', context: 'Melihat Riwayat' },
    menyambutKembali: { n: 13, alt: 'Maskot SIGADIS menyambut kembali', context: 'Masa Nifas' },
    merayakan: { n: 14, alt: 'Maskot SIGADIS merayakan keberhasilan', context: 'Case Closed' },
    berpikir: { n: 15, alt: 'Maskot SIGADIS sedang berpikir', context: 'Loading / Memproses' },
    bingungMembantu: { n: 16, alt: 'Maskot SIGADIS bingung tapi tetap membantu', context: 'Error Teknis' },
    manggukMaklum: { n: 17, alt: 'Maskot SIGADIS mengangguk maklum', context: 'Data Tidak Lengkap' },
    menungguSantai: { n: 18, alt: 'Maskot SIGADIS menunggu santai', context: 'Empty State' },
    memperkenalkanDiri: { n: 19, alt: 'Maskot SIGADIS memperkenalkan diri', context: 'Onboarding / Tutorial' },
    jempolCentang: { n: 20, alt: 'Maskot SIGADIS memberi jempol tanda berhasil', context: 'Konfirmasi Aksi Berhasil' },
    logoSistem: { n: 21, alt: 'Logo SIGADIS', context: 'Logo Sistem' },
    logoPlaystore: { n: 22, alt: 'Logo SIGADIS untuk Play Store', context: 'Logo Playstore' },
};

export function useMascot() {
    const src = (key) => `/assets/mascot/mascot-pose-${MASCOT_POSES[key].n}.webp`;
    const alt = (key) => MASCOT_POSES[key].alt;
    /** Pakai saat butuh {src, alt} sekaligus, misal untuk :src dan :alt di satu <img>. */
    const pose = (key) => ({ src: src(key), alt: alt(key) });

    return { src, alt, pose, MASCOT_POSES };
}
