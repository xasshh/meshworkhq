<?php

/**
 * Horizontal overflow on a phone.
 *
 * Each of these guards a fix for a measured bug: the dashboard pages scrolled
 * sideways on a 390px screen, which drags the header and content half off the
 * edge. None of it is visible on a desktop browser, and each fix is a single
 * easily reverted line, so the reason is recorded here rather than left to be
 * rediscovered on someone's phone.
 */
function appCss(): string
{
    return file_get_contents(resource_path('css/app.css'));
}

it('lets a long client email wrap instead of widening the panel', function () {
    $css = appCss();

    preg_match('/\.seal-value\s*\{(.*?)\}/s', $css, $value);
    preg_match('/\.seal-row\s*\{(.*?)\}/s', $css, $row);

    // An email is one unbreakable token in a monospace face. Left alone it sets
    // the min-content width of the whole panel and pushes the page sideways.
    expect($value[1] ?? '')->toContain('overflow-wrap: anywhere')
        ->and($value[1] ?? '')->toContain('min-width: 0')
        ->and($row[1] ?? '')->toContain('flex-wrap: wrap');
});

it('contains the absolutely positioned label inside the pitches table scroller', function () {
    $markup = file_get_contents(resource_path('views/pages/professional/⚡pitches.blade.php'));

    preg_match('/<div class="([^"]*overflow-x-auto[^"]*)"/', $markup, $wrapper);

    // The actions column holds an sr-only span. Absolutely positioned elements
    // are only clipped by an ancestor that establishes a containing block, so
    // without `relative` it escapes this scroller and extends the page by the
    // full width of the table.
    expect($wrapper[1] ?? '')->toContain('relative');
});

it('lets the conversation header badges shrink on a narrow screen', function () {
    $markup = file_get_contents(resource_path('views/pages/shared/⚡conversation.blade.php'));

    // These badges share one flex row with the counterpart's name. shrink-0
    // there means the row keeps its full width and overflows the screen.
    expect($markup)->not->toContain('items-center gap-2 shrink-0');
});

it('lets a pasted token wrap instead of widening the message thread', function () {
    $markup = file_get_contents(resource_path('views/pages/shared/⚡conversation.blade.php'));

    preg_match('/<p class="([^"]*)">\{\{ \$message->body \}\}<\/p>/', $markup, $body);

    // Measured on a 375px viewport with a 120 character token and no spaces:
    // break-words (overflow-wrap: break-word) left 802px of horizontal scroll,
    // because it only breaks a word that would overflow its line box and does
    // not feed into how wide the bubble asks to be. overflow-wrap: anywhere
    // does count toward intrinsic sizing, and brought it to zero.
    expect($body[1] ?? '')->toContain('wrap-anywhere')
        ->and($body[1] ?? '')->not->toContain('break-words');
});
