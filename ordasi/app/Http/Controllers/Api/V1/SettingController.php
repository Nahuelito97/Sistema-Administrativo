<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:settings.index')->only(['index']);
        $this->middleware('can:settings.edit')->only(['update']);
    }

    /** Devuelve los settings como objeto key => value. */
    public function index()
    {
        return response()->json(Setting::pluck('value', 'key'));
    }

    /** Público: settings para el storefront. */
    public function publicIndex()
    {
        return response()->json(Setting::pluck('value', 'key'));
    }

    /** Guarda múltiples settings (key => value). */
    public function update(Request $request)
    {
        $data = $request->validate(['settings' => ['required', 'array']]);
        foreach ($data['settings'] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return response()->json(Setting::pluck('value', 'key'));
    }
}
