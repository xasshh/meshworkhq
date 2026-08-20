<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Response;

/**
 * robots.txt and sitemap.xml are served from routes rather than static files so
 * their absolute URLs follow APP_URL. A sitemap reference in robots.txt has to
 * be absolute, and a hardcoded one would point at the wrong host in every
 * environment but the one it was written in.
 */
class SeoController extends Controller
{
    public function robots(): Response
    {
        $disallow = [
            '/client/',
            '/professional/dashboard',
            '/professional/alerts',
            '/professional/pitches',
            '/professional/messages',
            '/professional/profile',
            '/professional/wallet',
            '/professional/brief',
            '/professional/conversation',
            '/dashboard',
            '/settings',
            '/login',
            '/register',
            '/forgot-password',
            '/reset-password',
            '/email/',
            '/user/',
            '/payment/',
            '/webhooks/',
        ];

        $lines = ['User-agent: *'];

        foreach ($disallow as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('sitemap');

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function sitemap(): Response
    {
        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => route('how-it-works'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('for-talent'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('directory'), 'priority' => '0.9', 'changefreq' => 'daily'],
        ];

        // Only profiles complete enough to be worth landing on. A crawler that
        // finds a page with a name and nothing else learns the site is thin.
        User::query()
            ->where('role', Role::Professional)
            ->whereNotNull('professional_title')
            ->whereNotNull('bio')
            ->select(['id', 'updated_at'])
            ->orderBy('id')
            ->chunk(500, function ($professionals) use (&$urls) {
                foreach ($professionals as $professional) {
                    $urls[] = [
                        'loc' => route('professionals.show', ['id' => $professional->id]),
                        'lastmod' => $professional->updated_at?->toAtomString(),
                        'priority' => '0.6',
                        'changefreq' => 'weekly',
                    ];
                }
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.e($url['loc'])."</loc>\n";

            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>'.e($url['lastmod'])."</lastmod>\n";
            }

            $xml .= '    <changefreq>'.$url['changefreq']."</changefreq>\n";
            $xml .= '    <priority>'.$url['priority']."</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>'."\n";

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
