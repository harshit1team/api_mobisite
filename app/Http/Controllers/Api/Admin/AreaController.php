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
     * Geocode a city/area name using Nominatim.
     */
    private function geocode(string $query): array
    {
        try {
            $q = $query;
            if (!preg_match('/(christchurch|canterbury|new zealand|nz)/i', $query)) {
                $q = $query . ', Christchurch, New Zealand';
            }

            $response = Http::withHeaders([
                'User-Agent' => 'MobilRiccartonAdmin/1.0',
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $q,
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 1,
            ]);

            if ($response->successful() && count($response->json()) > 0) {
                $result = $response->json()[0];
                return [
                    'lat' => $result['lat'] ?? null,
                    'lon' => $result['lon'] ?? null,
                    'display_name' => $result['display_name'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            // Geocoding failure is non-fatal
        }

        return ['lat' => null, 'lon' => null, 'display_name' => null];
    }
}
