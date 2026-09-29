<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AreaController extends Controller
{
    /**
     * List all areas.
     */
    public function index(): JsonResponse
    {
        $areas = Area::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'areas' => $areas,
        ]);
    }

    /**
     * Store a new area — geocodes the city name via Nominatim.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:areas,name'],
        ]);

        // Geocode using OpenStreetMap Nominatim (free, no key needed)
        $geo = $this->geocode($validated['name']);

        $area = Area::create([
            'name' => $validated['name'],
            'latitude' => $geo['lat'] ?? null,
            'longitude' => $geo['lon'] ?? null,
            'display_name' => $geo['display_name'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Area created successfully.',
            'area' => $area,
        ], 201);
    }

    /**
     * Update an area name — re-geocodes if name changed.
     */
    public function update(Request $request, Area $area): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:areas,name,' . $area->id],
        ]);

        $geo = $this->geocode($validated['name']);

        $area->update([
            'name' => $validated['name'],
            'latitude' => $geo['lat'] ?? null,
            'longitude' => $geo['lon'] ?? null,
            'display_name' => $geo['display_name'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Area updated successfully.',
            'area' => $area->fresh(),
        ]);
    }

    /**
     * Delete an area.
     */
    public function destroy(Area $area): JsonResponse
    {
        $area->delete();

        return response()->json([
            'success' => true,
            'message' => 'Area deleted successfully.',
        ]);
    }

    /**
     * Geocode a city/area name using Nominatim with timeout and local fast-lookup.
     */
    private function geocode(string $query): array
    {
        $cleanName = strtolower(trim($query));

        // Fast-path known local suburbs around Christchurch to prevent external API delays
        $knownSuburbs = [
            'addington' => ['lat' => -43.5429, 'lon' => 172.6126, 'display_name' => 'Addington, Christchurch, New Zealand'],
            'riccarton' => ['lat' => -43.5306, 'lon' => 172.6036, 'display_name' => 'Riccarton, Christchurch, New Zealand'],
            'fendalton' => ['lat' => -43.5152, 'lon' => 172.5975, 'display_name' => 'Fendalton, Christchurch, New Zealand'],
            'christchurch central' => ['lat' => -43.5321, 'lon' => 172.6362, 'display_name' => 'Christchurch Central, Christchurch, New Zealand'],
            'ilam' => ['lat' => -43.5262, 'lon' => 172.5768, 'display_name' => 'Ilam, Christchurch, New Zealand'],
            'merivale' => ['lat' => -43.5135, 'lon' => 172.6241, 'display_name' => 'Merivale, Christchurch, New Zealand'],
            'upper riccarton' => ['lat' => -43.5312, 'lon' => 172.5802, 'display_name' => 'Upper Riccarton, Christchurch, New Zealand'],
            'burnside' => ['lat' => -43.4988, 'lon' => 172.5769, 'display_name' => 'Burnside, Christchurch, New Zealand'],
            'spreydon' => ['lat' => -43.5516, 'lon' => 172.6186, 'display_name' => 'Spreydon, Christchurch, New Zealand'],
            'sydenham' => ['lat' => -43.5471, 'lon' => 172.6375, 'display_name' => 'Sydenham, Christchurch, New Zealand'],
            'papanui' => ['lat' => -43.4947, 'lon' => 172.6105, 'display_name' => 'Papanui, Christchurch, New Zealand'],
            'hornby' => ['lat' => -43.5435, 'lon' => 172.5298, 'display_name' => 'Hornby, Christchurch, New Zealand'],
            'cashmere' => ['lat' => -43.5786, 'lon' => 172.6288, 'display_name' => 'Cashmere, Christchurch, New Zealand'],
            'wigram' => ['lat' => -43.5521, 'lon' => 172.5525, 'display_name' => 'Wigram, Christchurch, New Zealand'],
            'halswell' => ['lat' => -43.5852, 'lon' => 172.5647, 'display_name' => 'Halswell, Christchurch, New Zealand'],
            'christchurch' => ['lat' => -43.5321, 'lon' => 172.6362, 'display_name' => 'Christchurch, Canterbury, New Zealand'],
        ];

        if (isset($knownSuburbs[$cleanName])) {
            return $knownSuburbs[$cleanName];
        }

        try {
            $q = $query;
            if (!preg_match('/(christchurch|canterbury|new zealand|nz)/i', $query)) {
                $q = $query . ', Christchurch, New Zealand';
            }

            $response = Http::timeout(5)->withHeaders([
                'User-Agent' => 'MobilRiccartonAdmin/1.0 (management@cmenergy.co.nz)',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $q,
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 1,
            ]);

            if ($response->successful() && is_array($response->json()) && count($response->json()) > 0) {
                $result = $response->json()[0];
                return [
                    'lat' => isset($result['lat']) ? (float) $result['lat'] : null,
                    'lon' => isset($result['lon']) ? (float) $result['lon'] : null,
                    'display_name' => $result['display_name'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            // Geocoding failure is non-fatal
        }

        return ['lat' => null, 'lon' => null, 'display_name' => null];
    }
}
