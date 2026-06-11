<?php

namespace App\Http\Controllers;

use App\Exceptions\AlreadyUnlockedException;
use App\Exceptions\BriefNotAvailableException;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Brief;
use App\Services\UnlockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UnlockController extends Controller
{
    public function __construct(
        private readonly UnlockService $unlockService,
    ) {}

    public function store(Request $request, string $ulid): RedirectResponse
    {
        $brief = Brief::where('ulid', $ulid)->firstOrFail();

        try {
            $unlock = $this->unlockService->unlock($request->user(), $brief);

            return redirect()
                ->route('professional.conversation', ['id' => $unlock->conversation?->id])
                ->with('success', 'Brief unlocked! You can now pitch to the client.');
        } catch (AlreadyUnlockedException) {
            return back()->with('error', 'You have already unlocked this brief.');
        } catch (InsufficientCreditsException $e) {
            return back()->with('error', "Insufficient credits. You need {$e->required} credit(s) but have {$e->available}.");
        } catch (BriefNotAvailableException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
