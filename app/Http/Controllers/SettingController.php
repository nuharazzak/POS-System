<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Get current store settings.
     */
    public function index(): JsonResponse
    {
        $settings = Setting::current();

        return response()->json([
            'success'  => true,
            'settings' => new SettingResource($settings),
            'data'     => new SettingResource($settings),
        ]);
    }

    /**
     * Update store settings.
     */
    public function update(UpdateSettingRequest $request): JsonResponse
    {
        $settings = Setting::current();
        $settings->update($request->validated());

        return response()->json([
            'success'  => true,
            'message'  => 'Settings updated successfully',
            'settings' => new SettingResource($settings),
            'data'     => new SettingResource($settings),
        ]);
    }
}
