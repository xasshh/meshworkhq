<?php

use App\Models\User;

test('public pages carry a unique meta description', function () {
    $descriptions = [];

    foreach (['home', 'how-it-works', 'for-talent', 'directory'] as $route) {
        $html = $this->get(route($route))->assertOk()->getContent();

        preg_match('/<meta name="description" content="([^"]+)"/', $html, $m);

        expect($m[1] ?? null)->not->toBeNull("route [{$route}] has no meta description")
            ->and(strlen($m[1]))->toBeGreaterThan(70)
            ->and(strlen($m[1]))->toBeLessThan(200);

        $descriptions[] = $m[1];
    }

    // Duplicate descriptions across pages are worse than none at all.
    expect($descriptions)->toHaveCount(count(array_unique($descriptions)));
});

test('public pages are indexable and carry a canonical', function (string $route) {
    $html = $this->get(route($route))->assertOk()->getContent();

    expect($html)->toContain('<link rel="canonical"')
        ->and($html)->toContain('name="robots" content="index, follow')
        ->and($html)->not->toContain('noindex');
})->with(['home', 'how-it-works', 'for-talent', 'directory']);

test('the authenticated app is kept out of the index', function (string $route) {
    $professional = User::factory()->professional()->create();

    $html = $this->actingAs($professional)->get(route($route))->assertOk()->getContent();

    expect($html)->toContain('content="noindex, nofollow"');
})->with([
    'professional.dashboard',
    'professional.alerts',
    'professional.wallet',
    'professional.messages',
]);

test('auth screens are kept out of the index', function (string $route) {
    $html = $this->get(route($route))->assertOk()->getContent();

    expect($html)->toContain('content="noindex, nofollow"');
})->with(['login', 'register', 'professional.login', 'client.register']);

test('every public page emits organisation and website structured data', function (string $route) {
    $html = $this->get(route($route))->assertOk()->getContent();

    expect($html)->toContain('"@type":"Organization"')
        ->and($html)->toContain('"@type":"WebSite"');
})->with(['home', 'how-it-works', 'directory']);

test('the faq pages emit valid faq structured data', function (string $route) {
    $html = $this->get(route($route))->assertOk()->getContent();

    expect($html)->toContain('"@type":"FAQPage"');

    preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);

    $faq = collect($m[1])
        ->map(fn ($json) => json_decode($json, true))
        ->first(fn ($data) => ($data['@type'] ?? null) === 'FAQPage');

    expect($faq)->not->toBeNull()
        ->and($faq['mainEntity'])->not->toBeEmpty();

    foreach ($faq['mainEntity'] as $question) {
        expect($question['@type'])->toBe('Question')
            ->and($question['name'])->not->toBeEmpty()
            ->and($question['acceptedAnswer']['text'])->not->toBeEmpty();

        // The answer must be present in the page a person sees, or the rich
        // result is a lie and gets dropped.
        expect($html)->toContain(e($question['name']));
    }
})->with(['how-it-works', 'for-talent']);

test('robots.txt points at an absolute sitemap and blocks the app', function () {
    $body = $this->get('/robots.txt')->assertOk()->getContent();

    expect($body)->toContain('Sitemap: '.route('sitemap'))
        ->and($body)->toContain('Disallow: /client/')
        ->and($body)->toContain('Disallow: /webhooks/')
        ->and($body)->toContain('Disallow: /payment/');
});

test('the sitemap lists public pages and complete profiles only', function () {
    $complete = User::factory()->professional()->create([
        'professional_title' => 'Brand designer',
        'bio' => 'I build brand systems for Nigerian companies.',
    ]);

    $thin = User::factory()->professional()->create([
        'professional_title' => 'Designer',
        'bio' => null,
    ]);

    $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

    expect($xml)->toContain(route('home'))
        ->and($xml)->toContain(route('how-it-works'))
        ->and($xml)->toContain(route('for-talent'))
        ->and($xml)->toContain(route('directory'))
        ->and($xml)->toContain(route('professionals.show', ['id' => $complete->id]))
        ->and($xml)->not->toContain(route('professionals.show', ['id' => $thin->id]));

    // Nothing private may ever appear in a sitemap.
    expect($xml)->not->toContain('/dashboard')
        ->and($xml)->not->toContain('/wallet');
});

test('a professional profile carries person structured data and its own description', function () {
    $professional = User::factory()->professional()->create([
        'name' => 'Emeka Nwosu',
        'professional_title' => 'Brand designer',
        'bio' => 'I build brand systems for Nigerian companies that have outgrown their first logo.',
        'skill_tags' => ['Brand Identity'],
    ]);

    $html = $this->get(route('professionals.show', ['id' => $professional->id]))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('"@type":"Person"')
        ->and($html)->toContain('"@type":"BreadcrumbList"')
        ->and($html)->toContain('Emeka Nwosu')
        ->and($html)->toContain('I build brand systems');
});

test('a profile with no bio is not put in front of searchers', function () {
    $professional = User::factory()->professional()->create([
        'professional_title' => 'Designer',
        'bio' => null,
    ]);

    $html = $this->get(route('professionals.show', ['id' => $professional->id]))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('content="noindex, nofollow"');
});

test('social card tags are present for sharing', function () {
    $html = $this->get(route('home'))->assertOk()->getContent();

    expect($html)->toContain('property="og:title"')
        ->and($html)->toContain('property="og:description"')
        ->and($html)->toContain('property="og:image"')
        ->and($html)->toContain('name="twitter:card"');
});
