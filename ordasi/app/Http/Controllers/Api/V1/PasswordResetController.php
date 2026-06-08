<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /** Pide el reset: genera un token y manda el link (por mail; en dev, al log). */
    public function forgot(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', $data['email'])->first();

        // Respuesta uniforme (no revelar si el email existe).
        if ($user) {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($token), 'created_at' => Carbon::now()]
            );

            $front = rtrim(config('services.storefront.url', env('STOREFRONT_URL', 'http://localhost:5174')), '/');
            $link = "{$front}/reset?token={$token}&email=" . urlencode($user->email);
            Log::info("[password reset] {$user->email}: {$link}");
            // TODO: con SMTP configurado, enviar el link por mail aquí.

            $payload = ['message' => 'Si el email existe, te enviamos un enlace para restablecer la contraseña.'];
            if (config('app.debug')) {
                $payload['debug_link'] = $link; // solo en desarrollo
            }
            return response()->json($payload);
        }

        return response()->json(['message' => 'Si el email existe, te enviamos un enlace para restablecer la contraseña.']);
    }

    /** Restablece con el token. */
    public function reset(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'token'    => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $data['email'])->first();
        if (! $record || ! Hash::check($data['token'], $record->token)) {
            return response()->json(['message' => 'El enlace es inválido.'], 422);
        }
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            return response()->json(['message' => 'El enlace expiró. Pedí uno nuevo.'], 422);
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->update(['password' => $data['password']]); // se hashea por el cast
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();

        return response()->json(['message' => 'Contraseña actualizada. Ya podés ingresar.']);
    }
}
