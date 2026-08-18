<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminFacilityController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AreaAssignmentController;
use App\Http\Controllers\Admin\PhoneOverrideController;
use App\Http\Controllers\Admin\ReportingController;
use App\Http\Controllers\Admin\ScreeningQuestionController;
use App\Http\Controllers\Admin\WorkerVerificationController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\PregnantAuthController;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Auth\StaffPasswordResetController;
use App\Http\Controllers\Mobile\MobileAuthController;
use App\Http\Controllers\Mobile\MobilePasswordResetController;
use App\Http\Controllers\Mobile\MobileDashboardController;
use App\Http\Controllers\Mobile\MobileScreeningController;
use App\Http\Controllers\Mobile\MobileHistoryController;
use App\Http\Controllers\Mobile\MobileFacilityController;
use App\Http\Controllers\Mobile\MobileSettingsController;
use App\Http\Controllers\Mobile\MobilePrivacyController;
use App\Http\Controllers\Mobile\MobilePregnancyRegistrationController;
use App\Http\Controllers\Bidan\AlertController;
use App\Http\Controllers\Bidan\AvailabilityController;
use App\Http\Controllers\Bidan\ClinicalVisitController;
use App\Http\Controllers\Bidan\DashboardController;
use App\Http\Controllers\Bidan\DeviceTokenController;
use App\Http\Controllers\Bidan\NotificationController as BidanNotificationController;
use App\Http\Controllers\Bidan\PatientController;
use App\Http\Controllers\Bidan\ProfileController as BidanProfileController;
use App\Http\Controllers\Bidan\ReferralController;
use App\Http\Controllers\EmergencyAlertController;
use App\Http\Controllers\EmergencyAlertStatusController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PhoneChangeController;
use App\Http\Controllers\PregnancyController;
use App\Http\Controllers\PrivacyController;
use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\WelcomeController;

// =========================================================================
// 1. RUTE LANDING PAGE (PLATFORM WEB)
// =========================================================================
Route::middleware(['platform:web'])->group(function () {
    Route::get('/', function (Request $request) {
        $isMobile = $request->header('X-Is-Native') === '1'
            || in_array($request->header('X-Capacitor-Platform'), ['android', 'ios'])
            || str_contains($request->userAgent() ?? '', 'Capacitor')
            || str_contains($request->userAgent() ?? '', 'wv');

        if ($isMobile) {
            if (Auth::guard('pregnant')->check() && Route::has('kehamilan.beranda')) {
                return redirect()->route('kehamilan.beranda');
            }
            return Inertia::render('Splash');
        }

        return Inertia::render('Landing/Home');
    })->name('landing.home');

    Route::get('/tentang', fn () => Inertia::render('Landing/About'))->name('landing.about');
    Route::get('/fitur', fn () => Inertia::render('Landing/Features'))->name('landing.features');
    Route::get('/cara-kerja', fn () => Inertia::render('Landing/HowItWorks'))->name('landing.how-it-works');
    Route::get('/faq', fn () => Inertia::render('Landing/Faq'))->name('landing.faq');
    Route::get('/download-apk', fn () => Inertia::render('Landing/DownloadApk'))->name('landing.download-apk');

    Route::get('/application/SIGADIS.apk', function () {
        return redirect('https://github.com/Chandra2004/SIGADIS-BETA/releases/latest/download/SIGADIS.apk');
    });
});


// =========================================================================
// 2. RUTE AUTENTIKASI WEB (LOGIN, REGISTER, LUPA PASSWORD, OTP, ADMIN)
// =========================================================================
Route::middleware(['platform:web'])->group(function () {
    // --- Login Terpadu (Unified Login: No. HP / STR / Email + Password) ---
    Route::middleware(['guest:staff'])->group(function () {
        Route::get('/login', [StaffAuthController::class, 'showLoginForm'])->name('auth.staff.login.show');
        Route::post('/login', [StaffAuthController::class, 'login'])->name('auth.staff.login');

        // --- Registrasi Bidan / Kader / Ibu Hamil ---
        Route::get('/register', [StaffAuthController::class, 'showRegisterForm'])->name('auth.staff.register.show');
        Route::post('/register', [StaffAuthController::class, 'register'])->name('auth.staff.register');

        // --- Pemulihan Kata Sandi (Lupa Password via OTP WhatsApp / Email) ---
        Route::get('/lupa-password', [StaffPasswordResetController::class, 'showRequestForm'])->name('auth.staff.password-reset.request');
        Route::post('/lupa-password', [StaffPasswordResetController::class, 'sendOtp'])->name('auth.staff.password-reset.send');
        Route::get('/lupa-password/verifikasi', [StaffPasswordResetController::class, 'showVerifyForm'])->name('auth.staff.password-reset.verify.show');
        Route::post('/lupa-password/verifikasi', [StaffPasswordResetController::class, 'verifyOtp'])->name('auth.staff.password-reset.verify');
        Route::get('/lupa-password/atur', [StaffPasswordResetController::class, 'showResetForm'])->name('auth.staff.password-reset.form');
        Route::post('/lupa-password/atur', [StaffPasswordResetController::class, 'resetPassword'])->name('auth.staff.password-reset.store');
    });

    // --- Pendaftaran & Verifikasi OTP Ibu Hamil ---
    Route::get('/daftar', [PregnantAuthController::class, 'showPhoneForm'])->name('auth.pregnant.phone.show');
    Route::post('/daftar', [PregnantAuthController::class, 'sendOtp'])->name('auth.pregnant.otp.send');
    Route::get('/daftar/verifikasi', [PregnantAuthController::class, 'showVerifyForm'])->name('auth.pregnant.verify.show');
    Route::post('/daftar/verifikasi', [PregnantAuthController::class, 'verifyOtp'])->name('auth.pregnant.otp.verify');

    // --- Status Menunggu Verifikasi & Logout Nakes ---
    Route::middleware('auth:staff')->group(function () {
        Route::get('/bidan/menunggu-verifikasi', [StaffAuthController::class, 'pending'])->name('auth.staff.pending');
        Route::post('/staff/logout', [StaffAuthController::class, 'logout'])->name('auth.staff.logout');
    });

    // --- Rute Pengisian Nama & Logout Ibu Hamil ---
    Route::middleware('auth:pregnant')->group(function () {
        Route::get('/daftar/nama', [PregnantAuthController::class, 'showNameForm'])->name('auth.pregnant.name.show');
        Route::post('/daftar/nama', [PregnantAuthController::class, 'saveName'])->name('auth.pregnant.name.save');
        Route::post('/logout', [PregnantAuthController::class, 'logout'])->name('auth.pregnant.logout');
    });

    // --- Rute Autentikasi & Verifikasi Admin ---
    Route::prefix('admin')->group(function () {
        Route::middleware('guest:admin')->group(function () {
            Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('auth.admin.login.show');
            Route::post('/login', [AdminAuthController::class, 'login'])->name('auth.admin.login');
        });

        Route::middleware('auth:admin')->group(function () {
            Route::post('/logout', [AdminAuthController::class, 'logout'])->name('auth.admin.logout');
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
            Route::get('/verifikasi', [WorkerVerificationController::class, 'index'])->name('admin.verifikasi.index');
            Route::post('/verifikasi/{worker}/setujui', [WorkerVerificationController::class, 'verify'])->name('admin.verifikasi.verify');
            Route::post('/verifikasi/{worker}/tolak', [WorkerVerificationController::class, 'reject'])->name('admin.verifikasi.reject');
            Route::post('/verifikasi/{worker}/batalkan-penolakan', [WorkerVerificationController::class, 'cancelRejection'])->name('admin.verifikasi.cancel-reject');

            // --- Point 3: Zonasi & Penugasan Wilayah Kader ---
            Route::get('/zonasi', [AreaAssignmentController::class, 'index'])->name('admin.zonasi.index');
            Route::post('/zonasi', [AreaAssignmentController::class, 'store'])->name('admin.zonasi.store');
            Route::delete('/zonasi/{areaAssignment}', [AreaAssignmentController::class, 'destroy'])->name('admin.zonasi.destroy');
            Route::post('/area-assignments', [AreaAssignmentController::class, 'store'])->name('admin.area-assignments.store');
            Route::delete('/area-assignments/{areaAssignment}', [AreaAssignmentController::class, 'destroy'])->name('admin.area-assignments.destroy');

            // --- Point 4: Manajemen Fasilitas Kesehatan & Rujukan ---
            Route::get('/fasilitas', [AdminFacilityController::class, 'index'])->name('admin.fasilitas.index');
            Route::post('/fasilitas', [AdminFacilityController::class, 'store'])->name('admin.fasilitas.store');
            Route::put('/fasilitas/{facility}', [AdminFacilityController::class, 'update'])->name('admin.fasilitas.update');
            Route::delete('/fasilitas/{facility}', [AdminFacilityController::class, 'destroy'])->name('admin.fasilitas.destroy');

            // --- Point 5: Bank Soal & Protokol Skrining ---
            Route::get('/bank-soal', [ScreeningQuestionController::class, 'index'])->name('admin.bank-soal.index');
            Route::post('/bank-soal', [ScreeningQuestionController::class, 'store'])->name('admin.bank-soal.store');
            Route::put('/bank-soal/{screeningQuestion}', [ScreeningQuestionController::class, 'update'])->name('admin.bank-soal.update');
            Route::post('/bank-soal/{screeningQuestion}/review', [ScreeningQuestionController::class, 'review'])->name('admin.bank-soal.review');
            Route::delete('/bank-soal/{screeningQuestion}', [ScreeningQuestionController::class, 'destroy'])->name('admin.bank-soal.destroy');

            // --- Point 6: Pemulihan Akun & Override Akses Ibu Hamil ---
            Route::get('/ganti-nomor', [PhoneOverrideController::class, 'index'])->name('admin.ganti-nomor.index');
            Route::post('/ganti-nomor/{pregnantUser}', [PhoneOverrideController::class, 'store'])->name('admin.ganti-nomor.store');
            Route::post('/ganti-nomor/{id}/pulihkan', [PhoneOverrideController::class, 'restore'])->name('admin.ganti-nomor.restore');

            // --- Point 7: Laporan, Metrik & Ekspor Data ---
            Route::get('/laporan', [ReportingController::class, 'index'])->name('admin.reporting.index');
            Route::get('/laporan/export', [ReportingController::class, 'export'])->name('admin.reporting.export');

            // --- Point 8: Pengaturan Sistem & Kelola Admin ---
            Route::get('/pengaturan', [AdminSettingsController::class, 'index'])->name('admin.pengaturan.index');
            Route::post('/pengaturan/admin', [AdminSettingsController::class, 'storeAdmin'])->name('admin.pengaturan.admin.store');
            Route::put('/pengaturan/profil', [AdminSettingsController::class, 'updateProfile'])->name('admin.pengaturan.profile.update');
            Route::delete('/pengaturan/admin/{adminUser}', [AdminSettingsController::class, 'destroyAdmin'])->name('admin.pengaturan.admin.destroy');
        });
    });
});


// =========================================================================
// 3. RUTE MOBILE ANDROID (SPLASH, ONBOARDING, & AUTENTIKASI IBU HAMIL)
// =========================================================================
Route::middleware(['platform:mobile'])->group(function () {
    // --- Entry Point Mobile ---
    Route::get('/splash', function () {
        if (Auth::guard('pregnant')->check() && Route::has('kehamilan.beranda')) {
            return redirect()->route('kehamilan.beranda');
        }
        return Inertia::render('Splash');
    })->name('splash');

    Route::get('/onboarding', fn () => Inertia::render('Onboarding'))->name('onboarding');

    // --- Autentikasi Mobile Khusus Ibu Hamil (Login, Register, OTP, Lupa Password) ---
    Route::middleware(['guest:pregnant'])->group(function () {
        // Login Mobile
        Route::get('/mobile/login', [MobileAuthController::class, 'showLoginForm'])->name('mobile.login.show');
        Route::post('/mobile/login', [MobileAuthController::class, 'login'])->name('mobile.login');

        // Registrasi Mobile
        Route::get('/mobile/daftar', [MobileAuthController::class, 'showRegisterForm'])->name('mobile.register.show');
        Route::post('/mobile/daftar', [MobileAuthController::class, 'sendOtp'])->name('mobile.register.send');

        // Verifikasi OTP Registrasi
        Route::get('/mobile/daftar/verifikasi', [MobileAuthController::class, 'showVerifyForm'])->name('mobile.verify.show');
        Route::post('/mobile/daftar/verifikasi', [MobileAuthController::class, 'verifyOtp'])->name('mobile.verify');

        // Pemulihan Kata Sandi Mobile (Lupa Password)
        Route::get('/mobile/lupa-password', [MobilePasswordResetController::class, 'showRequestForm'])->name('mobile.password-reset.request');
        Route::post('/mobile/lupa-password', [MobilePasswordResetController::class, 'sendOtp'])->name('mobile.password-reset.send');
        Route::get('/mobile/lupa-password/verifikasi', [MobilePasswordResetController::class, 'showVerifyForm'])->name('mobile.password-reset.verify.show');
        Route::post('/mobile/lupa-password/verifikasi', [MobilePasswordResetController::class, 'verifyOtp'])->name('mobile.password-reset.verify');
        Route::get('/mobile/lupa-password/atur', [MobilePasswordResetController::class, 'showResetForm'])->name('mobile.password-reset.form');
        Route::post('/mobile/lupa-password/atur', [MobilePasswordResetController::class, 'resetPassword'])->name('mobile.password-reset.store');
    });

    Route::post('/mobile/logout', [MobileAuthController::class, 'logout'])->name('mobile.logout');

    // --- Halaman & Fitur Khusus Ibu Hamil (Mobile App) ---
    Route::middleware(['auth:pregnant'])->group(function () {
        // Beranda / Dashboard Ibu Hamil (Adaptive Multi-State)
        Route::get('/mobile/dashboard', [MobileDashboardController::class, 'index'])->name('mobile.dashboard');
        Route::get('/beranda', [MobileDashboardController::class, 'index'])->name('kehamilan.beranda');

        // Pendaftaran Profil Kehamilan Baru
        Route::get('/mobile/registrasi-kehamilan', [MobilePregnancyRegistrationController::class, 'show'])->name('mobile.pregnancy.register.show');
        Route::post('/mobile/registrasi-kehamilan', [MobilePregnancyRegistrationController::class, 'store'])->name('mobile.pregnancy.register.store');

        // Switch Multi-Profil Kehamilan
        Route::post('/mobile/profil/{pregnancy}/switch', [MobileDashboardController::class, 'switchProfile'])->name('mobile.profile.switch');

        // Modul Skrining Gejala Interaktif (Conversational)
        Route::get('/mobile/skrining', [MobileScreeningController::class, 'index'])->name('mobile.screening.index');
        Route::post('/mobile/skrining/submit', [MobileScreeningController::class, 'submit'])->name('mobile.screening.submit');

        // Modul Riwayat Kehamilan & Skrining
        Route::get('/mobile/riwayat', [MobileHistoryController::class, 'index'])->name('mobile.history.index');

        // Modul Direktori Faskes Terdekat
        Route::get('/mobile/faskes', [MobileFacilityController::class, 'index'])->name('mobile.facilities.index');

        // Modul Pengaturan Aplikasi & Aksesibilitas
        Route::get('/mobile/pengaturan', [MobileSettingsController::class, 'index'])->name('mobile.settings.index');
        Route::post('/mobile/pengaturan', [MobileSettingsController::class, 'update'])->name('mobile.settings.update');

        // Modul Privasi & UU PDP
        Route::get('/mobile/privasi', [MobilePrivacyController::class, 'index'])->name('mobile.privacy.index');
        Route::post('/mobile/privasi/cabut', [MobilePrivacyController::class, 'revokeConsent'])->name('mobile.privacy.revoke');
        Route::post('/mobile/privasi/aktifkan', [MobilePrivacyController::class, 'reactivateConsent'])->name('mobile.privacy.reactivate');
        Route::post('/mobile/privasi/hapus', [MobilePrivacyController::class, 'requestDeletion'])->name('mobile.privacy.delete');

        // Modul Aktivasi Peringatan Darurat SOS
        Route::post('/mobile/darurat/aktivasi', [MobileDashboardController::class, 'triggerEmergency'])->name('mobile.emergency.activate');
        Route::post('/mobile/darurat/selesai', [MobileDashboardController::class, 'resolveEmergency'])->name('mobile.emergency.resolve');
    });
});




// ==========================================
// Rute Mobile Entry (Splash & Onboarding)
// ==========================================
Route::get('/splash', function () {
    if (Auth::guard('pregnant')->check()) {
        return redirect()->route('kehamilan.beranda');
    }
    return Inertia::render('Splash');
})->name('splash');
Route::get('/onboarding', fn () => Inertia::render('Onboarding'))->name('onboarding');
Route::get('/welcome', [WelcomeController::class, 'index'])->name('welcome');

// ==========================================
// Rute In-App Kehamilan, Skrining, Darurat & Faskes (Ibu Hamil Desktop)
// ==========================================
Route::middleware('auth:pregnant')->group(function () {
    Route::get('/kehamilan/registrasi', [PregnancyController::class, 'showRegistrationForm'])->name('kehamilan.registrasi.show');
    Route::get('/kehamilan/bidan-kandidat', [PregnancyController::class, 'midwifeCandidates'])->name('kehamilan.midwife-candidates');
    Route::post('/kehamilan/registrasi', [PregnancyController::class, 'store'])->name('kehamilan.registrasi.store');
    Route::get('/kehamilan/registrasi/{pregnancy}/sukses', [PregnancyController::class, 'registrationSuccess'])->name('kehamilan.registrasi.sukses');
    Route::get('/beranda', [MobileDashboardController::class, 'index'])->name('kehamilan.beranda');
    Route::get('/profil', [PregnancyController::class, 'profil'])->name('kehamilan.profil');
    Route::get('/kehamilan/transisi-nifas', [PregnancyController::class, 'nifasTransition'])->name('kehamilan.nifas.transisi');
    Route::post('/kehamilan/transisi-nifas', [PregnancyController::class, 'acknowledgeNifasTransition'])->name('kehamilan.nifas.transisi.ack');
    Route::post('/kehamilan/{pregnancy}/aktifkan', [PregnancyController::class, 'switchActive'])->name('kehamilan.switch-active');
    Route::get('/kehamilan/ganti-bidan', [PregnancyController::class, 'showChangeMidwife'])->name('kehamilan.ganti-bidan.show');
    Route::post('/kehamilan/ganti-bidan', [PregnancyController::class, 'changeMidwife'])->name('kehamilan.ganti-bidan.store');
    Route::get('/skrining/transisi', [ScreeningController::class, 'transition'])->name('skrining.transisi');
    Route::post('/skrining/mulai', [ScreeningController::class, 'start'])->name('skrining.mulai');
    Route::get('/skrining/{session}', [ScreeningController::class, 'show'])->name('skrining.show');
    Route::post('/skrining/{session}/jawab', [ScreeningController::class, 'answer'])->name('skrining.jawab');
    Route::post('/skrining/{session}/lewati', [ScreeningController::class, 'skip'])->name('skrining.lewati');
    Route::get('/skrining/{session}/kembali', [ScreeningController::class, 'back'])->name('skrining.kembali');
    Route::get('/skrining/{session}/hasil', [ScreeningController::class, 'hasil'])->name('skrining.hasil');
    Route::post('/darurat/aktivasi', [EmergencyAlertController::class, 'activate'])->name('darurat.aktivasi');
    Route::get('/darurat/status', [EmergencyAlertStatusController::class, 'show'])->name('darurat.status');
    Route::get('/faskes', [FacilityController::class, 'index'])->name('kehamilan.faskes');
    Route::get('/riwayat', [HistoryController::class, 'index'])->name('kehamilan.riwayat');
    Route::get('/privasi', [PrivacyController::class, 'index'])->name('kehamilan.privasi');
    Route::post('/privasi/cabut-consent', [PrivacyController::class, 'revokeConsent'])->name('kehamilan.privasi.revoke-consent');
    Route::post('/privasi/aktifkan-consent', [PrivacyController::class, 'reactivateConsent'])->name('kehamilan.privasi.reactivate-consent');
    Route::post('/privasi/hapus-data', [PrivacyController::class, 'requestDeletion'])->name('kehamilan.privasi.request-deletion');
    Route::post('/privasi/izin-gps', [PrivacyController::class, 'updateGpsPermission'])->name('kehamilan.privasi.gps-permission');
    Route::post('/privasi/izin-berbagi-data', [PrivacyController::class, 'updateShareDataPermission'])->name('kehamilan.privasi.share-data-permission');
    Route::get('/privasi/unduh-data', [PrivacyController::class, 'exportData'])->name('kehamilan.privasi.export-data');
    Route::get('/pengaturan', [SettingsController::class, 'show'])->name('kehamilan.pengaturan');
    Route::post('/pengaturan', [SettingsController::class, 'update'])->name('kehamilan.pengaturan.update');
    Route::post('/pengaturan/foto', [SettingsController::class, 'updatePhoto'])->name('kehamilan.pengaturan.foto.update');
    Route::delete('/pengaturan/foto', [SettingsController::class, 'destroyPhoto'])->name('kehamilan.pengaturan.foto.destroy');
    Route::get('/ganti-nomor', [PhoneChangeController::class, 'show'])->name('akun.ganti-nomor.show');
    Route::post('/ganti-nomor/kirim-lama', [PhoneChangeController::class, 'sendOldNumberOtp'])->name('akun.ganti-nomor.send-old');
    Route::post('/ganti-nomor/verifikasi-lama', [PhoneChangeController::class, 'verifyOldNumberOtp'])->name('akun.ganti-nomor.verify-old');
    Route::post('/ganti-nomor/kirim-baru', [PhoneChangeController::class, 'sendNewNumberOtp'])->name('akun.ganti-nomor.send-new');
    Route::post('/ganti-nomor/verifikasi-baru', [PhoneChangeController::class, 'verifyNewNumberOtp'])->name('akun.ganti-nomor.verify-new');
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('kehamilan.notifikasi.index');
    Route::post('/notifikasi/{notification}/baca', [NotificationController::class, 'markRead'])->name('kehamilan.notifikasi.mark-read');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'markAllRead'])->name('kehamilan.notifikasi.mark-all-read');
});

// ==========================================
// Rute Bidan / Kader (Staff Dashboard & Monitoring)
// ==========================================
Route::prefix('bidan')->group(function () {
    Route::middleware('auth:staff')->group(function () {
        Route::middleware('staff.verified')->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('bidan.dashboard');
            Route::post('/status-nonaktif', [AvailabilityController::class, 'deactivate'])->name('bidan.availability.deactivate');
            Route::post('/status-aktif', [AvailabilityController::class, 'reactivate'])->name('bidan.availability.reactivate');
            Route::post('/device-token', [DeviceTokenController::class, 'store'])->name('bidan.device-token.store');
            Route::delete('/device-token', [DeviceTokenController::class, 'destroy'])->name('bidan.device-token.destroy');

            // 1. Pasien Maternal & Kunjungan ANC
            Route::get('/pasien/{pregnancy}', [PatientController::class, 'show'])->name('bidan.patients.show');
            Route::post('/pasien/{pregnancy}/bersalin', [PatientController::class, 'markDelivered'])->name('bidan.patients.mark-delivered');
            Route::post('/pasien/{pregnancy}/batalkan-nifas', [PatientController::class, 'cancelNifas'])->name('bidan.patients.cancel-nifas');
            Route::post('/pasien/{pregnancy}/ubah-tanggal-bersalin', [PatientController::class, 'editDeliveryDate'])->name('bidan.patients.edit-delivery-date');
            Route::post('/pasien/{pregnancy}/tutup-kasus', [PatientController::class, 'closeCase'])->name('bidan.patients.close-case');
            Route::post('/pasien/{pregnancy}/kunjungan', [ClinicalVisitController::class, 'store'])->name('bidan.patients.clinical-visits.store');
            Route::get('/pasien/{pregnancy}/unduh-riwayat', [ClinicalVisitController::class, 'exportPdf'])->name('bidan.patients.export-history');

            // 2. Pusat Kasus Gawat Darurat
            Route::get('/alerts', [AlertController::class, 'index'])->name('bidan.alerts.index');
            Route::get('/alert/{alert}', [AlertController::class, 'show'])->name('bidan.alerts.show');
            Route::post('/alert/{alert}/terima', [AlertController::class, 'acknowledge'])->name('bidan.alerts.acknowledge');
            Route::post('/alert/{alert}/batalkan-penanganan', [AlertController::class, 'cancelHandling'])->name('bidan.alerts.cancel-handling');
            Route::post('/alert/{alert}/selesai', [AlertController::class, 'resolve'])->name('bidan.alerts.resolve');
            Route::get('/alert/{alert}/riwayat', [AlertController::class, 'history'])->name('bidan.alerts.history');

            // 3. Fasilitas & Rujukan PONEK
            Route::get('/fasilitas', [ReferralController::class, 'index'])->name('bidan.referrals.index');
            Route::get('/alert/{alert}/rujukan', [ReferralController::class, 'create'])->name('bidan.referrals.create');
            Route::post('/alert/{alert}/rujukan', [ReferralController::class, 'store'])->name('bidan.referrals.store');

            // 4. Manajemen Status Cuti & Kesiapan Tugas
            Route::get('/cuti', [AvailabilityController::class, 'index'])->name('bidan.availability.index');

            // 6. Profil Nakes & Pengaturan Akun
            Route::get('/profil', [BidanProfileController::class, 'index'])->name('bidan.profile.index');
            Route::post('/profil/ubah-password', [BidanProfileController::class, 'updatePassword'])->name('bidan.profile.update-password');
        });

        // 5. Pusat Notifikasi & Sesi Perangkat (Dapat diakses oleh nakes pending & verified)
        Route::get('/notifikasi', [BidanNotificationController::class, 'index'])->name('bidan.notifications.index');
        Route::post('/notifikasi/{notification}/baca', [BidanNotificationController::class, 'markRead'])->name('bidan.notifications.mark-read');
        Route::post('/notifikasi/{notification}/mark-read', [BidanNotificationController::class, 'markRead'])->name('bidan.notifikasi.mark-read');
        Route::post('/notifikasi/baca-semua', [BidanNotificationController::class, 'markAllRead'])->name('bidan.notifications.mark-all-read');
        Route::post('/notifikasi/mark-all-read', [BidanNotificationController::class, 'markAllRead'])->name('bidan.notifikasi.mark-all-read');
    });
});


