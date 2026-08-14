<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesCurrentPregnancy;
use App\Models\EmergencyAlert;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Layar "Status Darurat Aktif" (desain Figma) — ibu hamil pantau progres
 * alert yang baru dikirim: sinyal terkirim -> bidan menuju lokasi ->
 * penanganan medis. Dipetakan dari emergency_alerts.status, bukan kolom baru.
 */
class EmergencyAlertStatusController extends Controller
{
    use ResolvesCurrentPregnancy;

    public function show(): Response|RedirectResponse
    {
        $pregnancy = $this->currentPregnancy()?->load('activeMidwifeAssignment.midwife') ?? abort(404);
        // Alert terakhir apapun statusnya, bukan cuma yang open() — begitu
        // resolved, timeline masih perlu tampil "selesai", bukan hilang.
        $alert = $pregnancy->emergencyAlerts()->with('handledBy')->latest('triggered_at')->first();

        if (! $alert) {
            return redirect()->route('kehamilan.beranda');
        }

        return Inertia::render('Kehamilan/StatusDarurat', [
            'alert' => [
                'id' => $alert->id,
                'status' => $alert->status,
                'triggered_at' => $alert->triggered_at,
                'handled_by' => $alert->handledBy?->full_name,
                'handled_at' => $alert->handled_at,
                'steps' => $this->steps($alert),
            ],
            'midwifePhone' => $pregnancy->activeMidwifeAssignment?->midwife?->phone_number,
        ]);
    }

    /** @return array<int,array{key:string,label:string,done:bool,at:?string,detail:?string}> */
    protected function steps(EmergencyAlert $alert): array
    {
        return [
            [
                'key' => 'signal_sent',
                'label' => 'Sinyal SOS & GPS Terkirim',
                'done' => true,
                'at' => $alert->triggered_at,
                'detail' => null,
            ],
            [
                'key' => 'midwife_en_route',
                'label' => 'Bidan Pendamping Menuju Lokasi',
                'done' => in_array($alert->status, ['being_handled', 'resolved'], true),
                'at' => $alert->handled_at,
                'detail' => $alert->handledBy?->full_name,
            ],
            [
                'key' => 'medical_handling',
                'label' => 'Penanganan Medis Faskes',
                'done' => $alert->status === 'resolved',
                'at' => null,
                'detail' => null,
            ],
        ];
    }
}
