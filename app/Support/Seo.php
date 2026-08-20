<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Per-page search metadata.
 *
 * Static pages declare their copy in config/seo.php, keyed by route name, so
 * the marketing pages carry real descriptions without every view repeating
 * boilerplate. Dynamic pages override at runtime, which works because Livewire
 * renders the page component before it renders the layout that reads this.
 */
final class Seo
{
    public ?string $title = null;

    public ?string $description = null;

    public ?string $canonical = null;

    public ?string $image = null;

    public bool $noindex = false;

    /** @var array<int, array<string, mixed>> */
    public array $schemas = [];

    public function set(?string $title = null, ?string $description = null): self
    {
        $this->title = $title ?? $this->title;
        $this->description = $description ?? $this->description;

        return $this;
    }

    public function image(?string $url): self
    {
        $this->image = $url;

        return $this;
    }

    public function canonical(string $url): self
    {
        $this->canonical = $url;

        return $this;
    }

    /** Keep a page out of the index: private, thin, or duplicate. */
    public function noindex(): self
    {
        $this->noindex = true;

        return $this;
    }

    /** @param  array<string, mixed>  $schema */
    public function schema(array $schema): self
    {
        $this->schemas[] = $schema;

        return $this;
    }

    public function resolvedDescription(): ?string
    {
        return $this->description
            ?? config('seo.pages.'.(Route::currentRouteName() ?? '').'.description')
            ?? config('seo.default_description');
    }

    /**
     * Canonical URL without the query string, so filtered and paginated views
     * of the directory do not read as separate pages competing with each other.
     */
    public function resolvedCanonical(): string
    {
        return $this->canonical ?? url()->current();
    }

    public function resolvedImage(): string
    {
        return $this->image ?? config('seo.default_image') ?? asset('images/meshwork-lockup.png');
    }

    /**
     * Everything that should not be indexed: the whole authenticated app, and
     * anything behind a signed or one time URL.
     */
    public function shouldNoindex(): bool
    {
        if ($this->noindex) {
            return true;
        }

        $name = Route::currentRouteName();

        if ($name === null) {
            return false;
        }

        foreach (config('seo.noindex_prefixes', []) as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
