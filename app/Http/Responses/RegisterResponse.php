<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Symfony\Component\HttpFoundation\Response;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        $user = $request->user();

        // Both dashboards sit behind the `verified` middleware, so sending an
        // unverified account there just bounces. Take them straight to the
        // notice that tells them to check their email.
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        $route = match (true) {
            $user?->isProfessional() => route('professional.dashboard'),
            default => route('client.dashboard'),
        };

        return redirect()->intended($route);
    }
}
