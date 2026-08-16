<?php

namespace Database\Seeders;

use App\Models\AlertRecipient;
use App\Models\EmergencyAlert;
use App\Models\HealthcareWorker;
use App\Models\KaderAreaAssignment;
use App\Models\Pregnancy;
use App\Models\PregnantUser;
use App\Models\RiskAssessment;
use App\Models\ScreeningSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MaternalDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tenaga Kesehatan di berbagai wilayah (Pending, Verified, Rejected)
        $workers = [
            [
                'phone_number' => '081234500010',
                'full_name' => 'Bidan Nurhaliza, S.Tr.Keb',
                'role' => 'bidan',
                'str_number' => 'STR-2026-0810',
                'status' => 'verified',
                'region_code' => '33.08.05.2001',
                'is_available' => true,
            ],
            [
                'phone_number' => '081234500011',
                'full_name' => 'Kader Fatimah Zahra',
                'role' => 'kader',
                'appointment_letter_ref' => 'SK-DESA-SR-012',
                'status' => 'verified',
                'region_code' => '33.08.05.2001',
                'is_available' => true,
            ],
            [
                'phone_number' => '081234500012',
                'full_name' => 'Bidan Dewi Sartika, A.Md.Keb',
                'role' => 'bidan',
                'str_number' => 'STR-2026-0812',
                'status' => 'verified',
                'region_code' => '33.08.05.2002',
                'is_available' => true,
            ],
            [
                'phone_number' => '081234500013',
                'full_name' => 'Kader Sri Wahyuni',
                'role' => 'kader',
                'appointment_letter_ref' => 'SK-DESA-SA-044',
                'status' => 'verified',
                'region_code' => '33.08.05.2002',
                'is_available' => true,
            ],
            [
                'phone_number' => '081234500014',
                'full_name' => 'Bidan Lailasari, S.Keb',
                'role' => 'bidan',
                'str_number' => 'STR-2026-0814',
                'status' => 'verified',
                'region_code' => '33.08.05.2003',
                'is_available' => true,
            ],
            [
                'phone_number' => '081234500015',
                'full_name' => 'Bidan Dian Permata, S.Tr.Keb',
                'role' => 'bidan',
                'str_number' => 'STR-2026-0815',
                'status' => 'pending',
                'region_code' => '33.08.05.2004',
                'is_available' => false,
            ],
            [
                'phone_number' => '081234500016',
                'full_name' => 'Kader Anisa Rahma',
                'role' => 'kader',
                'appointment_letter_ref' => 'SK-DESA-KD-088',
                'status' => 'pending',
                'region_code' => '33.08.05.2009',
                'is_available' => false,
            ],
        ];

        foreach ($workers as $w) {
            $createdWorker = HealthcareWorker::updateOrCreate(
                ['phone_number' => $w['phone_number']],
                [
                    'full_name' => $w['full_name'],
                    'password_hash' => Hash::make('password'),
                    'role' => $w['role'],
                    'str_number' => $w['str_number'] ?? null,
                    'appointment_letter_ref' => $w['appointment_letter_ref'] ?? null,
                    'status' => $w['status'],
                    'verified_at' => $w['status'] === 'verified' ? now() : null,
                    'region_code' => $w['region_code'],
                    'is_available' => $w['is_available'],
                ]
            );

            if ($w['role'] === 'kader' && $w['status'] === 'verified') {
                KaderAreaAssignment::updateOrCreate(
                    ['kader_id' => $createdWorker->id, 'region_code' => $w['region_code']],
                    ['kader_priority' => 'primary']
                );
            }
        }

        // 2. Data Ibu Hamil & Kehamilan Nyata
        $mothers = [
            [
                'phone_number' => '081288800001',
                'full_name' => 'Ibu Siti Khadijah',
                'mother_name' => 'Siti Khadijah',
                'region_code' => '33.08.05.2001',
                'address' => 'Jl. Merdeka No. 12, RT 02/RW 01, Desa Sungai Raya',
                'weeks' => 14,
                'status' => 'hamil',
                'risk_level' => 'rendah',
                'due_date' => now()->addMonths(6)->toDateString(),
            ],
            [
                'phone_number' => '081288800002',
                'full_name' => 'Ibu Maria Ulfah',
                'mother_name' => 'Maria Ulfah',
                'region_code' => '33.08.05.2001',
                'address' => 'Gg. Kamboja No. 5, Desa Sungai Raya',
                'weeks' => 28,
                'status' => 'hamil',
                'risk_level' => 'sedang',
                'due_date' => now()->addMonths(3)->toDateString(),
            ],
            [
                'phone_number' => '081288800003',
                'full_name' => 'Ibu Rina Anggraini',
                'mother_name' => 'Rina Anggraini',
                'region_code' => '33.08.05.2002',
                'address' => 'Jl. Raya Ambangah No. 45, Desa Sungai Ambangah',
                'weeks' => 34,
                'status' => 'hamil',
                'risk_level' => 'tinggi',
                'due_date' => now()->addWeeks(6)->toDateString(),
            ],
            [
                'phone_number' => '081288800004',
                'full_name' => 'Ibu Dian Safitri',
                'mother_name' => 'Dian Safitri',
                'region_code' => '33.08.05.2003',
                'address' => 'Komplek Arang Limbung Asri Blok C-2',
                'weeks' => 20,
                'status' => 'hamil',
                'risk_level' => 'rendah',
                'due_date' => now()->addMonths(5)->toDateString(),
            ],
            [
                'phone_number' => '081288800005',
                'full_name' => 'Ibu Endang Lestari',
                'mother_name' => 'Endang Lestari',
                'region_code' => '33.08.05.2004',
                'address' => 'Jl. Teluk Kapuas Dalam No. 18',
                'weeks' => 38,
                'status' => 'hamil',
                'risk_level' => 'tinggi',
                'due_date' => now()->addWeeks(2)->toDateString(),
            ],
            [
                'phone_number' => '081288800006',
                'full_name' => 'Ibu Yuliana Kusuma',
                'mother_name' => 'Yuliana Kusuma',
                'region_code' => '33.08.05.2009',
                'address' => 'Dsn. Kuala Dua Indah RT 03/RW 02',
                'weeks' => 41,
                'status' => 'nifas',
                'risk_level' => 'rendah',
                'due_date' => now()->subDays(5)->toDateString(),
            ],
            [
                'phone_number' => '081288800007',
                'full_name' => 'Ibu Kartika Putri',
                'mother_name' => 'Kartika Putri',
                'region_code' => '33.08.05.2009',
                'address' => 'Jl. Poros Kuala Dua No. 88',
                'weeks' => 18,
                'status' => 'hamil',
                'risk_level' => 'sedang',
                'due_date' => now()->addMonths(5)->toDateString(),
            ],
        ];

        $midwife1 = HealthcareWorker::where('phone_number', '081234500001')->first();

        foreach ($mothers as $idx => $m) {
            $user = PregnantUser::updateOrCreate(
                ['phone_number' => $m['phone_number']],
                [
                    'full_name' => $m['full_name'],
                    'password_hash' => Hash::make('password'),
                    'otp_verified_at' => now()->subDays(20),
                ]
            );

            $pregnancy = Pregnancy::updateOrCreate(
                ['pregnant_user_id' => $user->id],
                [
                    'mother_name' => $m['mother_name'],
                    'estimated_due_date' => $m['due_date'],
                    'gestational_age_weeks_at_registration' => $m['weeks'],
                    'region_code' => $m['region_code'],
                    'address' => $m['address'],
                    'emergency_contact_name' => 'Suami '.$m['mother_name'],
                    'emergency_contact_phone' => '08190000'.sprintf('%04d', $idx + 1),
                    'status' => $m['status'],
                    'nifas_started_at' => $m['status'] === 'nifas' ? now()->subDays(5) : null,
                ]
            );

            // Buat Riwayat Sesi Skrining & Penilaian Risiko
            $session = ScreeningSession::create([
                'pregnancy_id' => $pregnancy->id,
                'session_type' => $m['status'] === 'nifas' ? 'nifas' : 'periodic',
                'started_at' => now()->subDays(rand(1, 10)),
                'completed_at' => now()->subDays(rand(1, 10)),
                'is_complete' => true,
            ]);

            $assessment = RiskAssessment::create([
                'pregnancy_id' => $pregnancy->id,
                'screening_session_id' => $session->id,
                'risk_level' => $m['risk_level'],
                'triggered_rule_codes' => $m['risk_level'] === 'tinggi' ? ['R_RED_FLAG_BLEEDING'] : ($m['risk_level'] === 'sedang' ? ['R_HEADACHE_MODERATE'] : []),
                'recommendation_text' => 'Pemantauan rutin dan konsultasi faskes terdekat.',
                'assessed_at' => now()->subDays(rand(1, 10)),
            ]);

            // Jika Kasus Darurat Aktif
            if ($m['risk_level'] === 'tinggi' && $idx === 2) {
                $alert = EmergencyAlert::create([
                    'pregnancy_id' => $pregnancy->id,
                    'risk_assessment_id' => $assessment->id,
                    'trigger_type' => 'manual_button',
                    'status' => 'being_handled',
                    'triggered_at' => now()->subMinutes(12),
                ]);

                if ($midwife1) {
                    AlertRecipient::create([
                        'emergency_alert_id' => $alert->id,
                        'healthcare_worker_id' => $midwife1->id,
                        'recipient_role_at_time' => 'bidan_utama',
                        'delivery_status' => 'sent',
                        'sent_at' => now()->subMinutes(11),
                        'acknowledged_at' => now()->subMinutes(9),
                    ]);
                }
            } elseif ($m['risk_level'] === 'tinggi' && $idx === 4) {
                // Kasus darurat yang telah berhasil ditangani (resolved)
                $handledAlert = EmergencyAlert::create([
                    'pregnancy_id' => $pregnancy->id,
                    'risk_assessment_id' => $assessment->id,
                    'trigger_type' => 'auto_risk_high',
                    'status' => 'resolved',
                    'triggered_at' => now()->subHours(5),
                    'handled_at' => now()->subHours(4)->subMinutes(50),
                    'handled_by_id' => $midwife1?->id,
                ]);

                if ($midwife1) {
                    AlertRecipient::create([
                        'emergency_alert_id' => $handledAlert->id,
                        'healthcare_worker_id' => $midwife1->id,
                        'recipient_role_at_time' => 'bidan_utama',
                        'delivery_status' => 'sent',
                        'sent_at' => now()->subHours(5),
                        'acknowledged_at' => now()->subHours(5)->addMinutes(3)->addSeconds(20), // Waktu respons 3m 20d
                    ]);
                }
            }
        }
    }
}
