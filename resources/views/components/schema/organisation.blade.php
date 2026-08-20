@php
    $org = config('seo.organisation');

    $organisation = array_filter([
        '@type' => 'Organization',
        '@id' => url('/').'#organisation',
        'name' => $org['name'],
        'legalName' => $org['legal_name'],
        'description' => $org['description'],
        'url' => url('/'),
        'logo' => asset('images/meshwork-lockup.png'),
        'email' => $org['email'],
        'foundingDate' => $org['founding_year'],
        'address' => [
            '@type' => 'PostalAddress',
            'addressCountry' => $org['country'],
        ],
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Nigeria',
        ],
        'sameAs' => $org['same_as'] ?: null,
    ]);

    $website = [
        '@type' => 'WebSite',
        '@id' => url('/').'#website',
        'name' => $org['name'],
        'url' => url('/'),
        'publisher' => ['@id' => url('/').'#organisation'],
        'inLanguage' => 'en-NG',
        // Lets a search engine offer a directory search box straight in results.
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => route('directory').'?search={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];

    $graph = [
        '@context' => 'https://schema.org',
        '@graph' => [$organisation, $website],
    ];
@endphp

<script type="application/ld+json">{!! json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
