<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves a CAC certificate to a reviewer.
 *
 * The file lives on the private disk and must stay there. It is streamed
 * through this action so the only way to read one is an authenticated request
 * from an account carrying the admin flag; nothing is ever copied to a public
 * path, and no guessable URL exposes it.
 */
class VerificationDocumentController extends Controller
{
    public function show(Request $request, User $user): StreamedResponse
    {
        $path = $user->verification_document_path;

        // Documents are deleted the moment a decision is recorded, so a missing
        // file usually means the review already happened.
        abort_if(! $path || ! Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response(
            $path,
            'verification-'.$user->id.'.'.pathinfo($path, PATHINFO_EXTENSION),
            [
                // Viewed in the browser, never cached by a proxy on the way.
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
