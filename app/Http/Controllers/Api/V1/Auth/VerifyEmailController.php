<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use function event;
use function redirect;
use function response;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function verify(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return redirect(config('app.frontend_url'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect(config('app.frontend_url') . '/?emailVerified=true');
    }

    /**
     * Send Email Verification
     *
     * @param Request $request
     * @return Application|ResponseFactory|Response
     */
    public function send(Request $request): Response|Application|ResponseFactory
    {
        $request->user()->sendEmailVerificationNotification();

        return response([
            'message' => 'Email de verificação reenviado com sucesso'
        ], 200);
    }
}

