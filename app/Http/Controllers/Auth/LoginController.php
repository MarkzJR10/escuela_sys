<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->stateless()
            ->redirect();
    }

    /**
     * Obtain the user information from Google and log them in.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'No se pudo autenticar con Google: ' . $e->getMessage());
        }

        // Buscar el usuario por email
        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Guardar o actualizar google_id
            $user->update([
                'google_id' => $googleUser->getId(),
            ]);

            Auth::login($user);

            return $this->redirectUser($user);
        }

        // Si el usuario no está registrado, redirigir con error
        return redirect()->route('login')->withErrors(['email' => 'Esta cuenta institucional no está registrada en el sistema.']);
    }

    /**
     * Procesar la autenticación de Google Identity Services enviada mediante POST.
     */
    public function handleGooglePost(Request $request)
    {
        $idToken = $request->input('credential');

        if (!$idToken) {
            return redirect()->route('login')->with('error', 'Token de autenticación no recibido.');
        }

        try {
            $response = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if ($response->failed()) {
                return redirect()->route('login')->with('error', 'Token de Google no válido.');
            }

            $payload = $response->json();
            $email = $payload['email'] ?? null;

            if (!$email) {
                return redirect()->route('login')->with('error', 'No se obtuvo el correo institucional.');
            }

            $user = User::where('email', $email)->first();

            if ($user) {
                if (isset($payload['sub'])) {
                    $user->update(['google_id' => $payload['sub']]);
                }

                Auth::login($user);

                return $this->redirectUser($user);
            }

            return redirect()->route('login')->withErrors(['email' => 'Esta cuenta institucional no está registrada en el sistema.']);
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Error al procesar la autenticación: ' . $e->getMessage());
        }
    }

    /**
     * Manejar la redirección tras la autenticación tradicional (formulario).
     */
    protected function authenticated(Request $request, $user)
    {
        return $this->redirectUser($user);
    }

    /**
     * Helper para definir a dónde redirigir al usuario según sus roles.
     */
    protected function redirectUser($user)
    {
        if ($user->hasRole('padre')) {
            session()->forget('url.intended');
            return redirect()->route('portal_padre.dashboard');
        }

        return redirect()->intended($this->redirectTo);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
