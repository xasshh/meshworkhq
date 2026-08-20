<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Descriptions For Static Pages
    |--------------------------------------------------------------------------
    |
    | Keyed by route name. Each one is written to be read in a search result:
    | it says what the page is and why someone would click, in roughly 150 to
    | 160 characters, and never repeats another page's description.
    |
    */

    'pages' => [

        'home' => [
            'description' => 'Meshwork HQ is an alert first talent marketplace for Nigeria. Post a brief free and the ten best matched professionals are notified within seconds.',
        ],

        'how-it-works' => [
            'description' => 'How Meshwork HQ works: briefs are matched by skill and released in three timed waves, so the right professionals hear first instead of everyone at once.',
        ],

        'for-talent' => [
            'description' => 'Get paid work sent to you. Meshwork HQ alerts the ten best matched professionals the moment a brief is posted, with no commission on what you earn.',
        ],

        'directory' => [
            'description' => 'Browse verified designers, developers, photographers and marketers on Meshwork HQ. Filter by skill, or post a brief and let the right ones come to you.',
        ],

        'client.register' => [
            'description' => 'Create a free Meshwork HQ client account, post a brief in minutes, and get matched with professionals across Nigeria. No commission on completed work.',
        ],

        'professional.register' => [
            'description' => 'Join Meshwork HQ as a professional. Tag your skills, get alerted to matching briefs before the crowd, and start with three free unlocks.',
        ],

    ],

    'default_description' => 'Meshwork HQ is an alert first talent marketplace for Nigeria, connecting clients with professionals by skill in naira, with no commission on completed work.',

    'default_image' => null,

    /*
    |--------------------------------------------------------------------------
    | Kept Out Of The Index
    |--------------------------------------------------------------------------
    |
    | Route name prefixes that must never be indexed. The authenticated app has
    | nothing to offer a search engine and everything to leak, and auth screens
    | are thin duplicate content.
    |
    */

    'noindex_prefixes' => [
        'client.',
        'professional.dashboard',
        'professional.alerts',
        'professional.pitches',
        'professional.messages',
        'professional.profile',
        'professional.wallet',
        'professional.brief',
        'professional.conversation',
        'professional.login',
        'professional.register',
        'client.login',
        'client.register',
        'dashboard',
        'login',
        'register',
        'password',
        'verification',
        'two-factor',
        'profile.',
        'appearance.',
        'security.',
        'payment.',
        'webhooks.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Organisation
    |--------------------------------------------------------------------------
    |
    | Used for the Organization and WebSite structured data emitted on every
    | public page.
    |
    */

    'organisation' => [
        'name' => 'Meshwork HQ',
        'legal_name' => 'Meshwork HQ',
        'description' => 'An alert first talent marketplace connecting Nigerian businesses with professionals, matched by skill.',
        'email' => 'hello@meshworkhq.com',
        'country' => 'NG',
        'founding_year' => '2026',
        'same_as' => [
            // Add social profiles here as they exist. Each one is a trust
            // signal that ties the brand to a verifiable presence.
        ],
    ],

];
