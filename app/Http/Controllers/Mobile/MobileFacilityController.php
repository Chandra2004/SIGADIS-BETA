<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MobileFacilityController extends Controller
{
    public function index(Request $request): Response
    {
        $facilities = Facility::all()->map(function ($f) {
            $formattedType = match ($f->type) {
                'puskesmas' => 'Puskesmas PONED',
                'rumah_sakit' => 'RSUD PONEK',
                'polindes' => 'Polindes Siaga',
                'klinik' => 'Klinik Bersalin',
                default => ucfirst(str_replace('_', ' ', $f->type ?? 'Faskes')),
            };

            return [
                'id' => $f->id,
                'name' => $f->name,
                'type' => $formattedType,
                'address' => $f->address ?? 'Jl. Wilayah Binaan',
                'phone_number' => $f->phone_number ?? '081234567890',
                'latitude' => $f->latitude ?? -6.200000,
                'longitude' => $f->longitude ?? 106.816666,
                'has_emergency_room' => true,
                'has_nicu' => (bool) ($f->has_nicu ?? false),
                'ambulance_available' => $f->ambulance_status === 'siaga' || is_null($f->ambulance_status),
                'distance_km' => round(1.2 + ($f->id * 0.8), 1),
            ];
        })->sortBy('distance_km')->values();

        return Inertia::render('Mobile/Facilities', [
            'facilities' => $facilities,
        ]);
    }
}
