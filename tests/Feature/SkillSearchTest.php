<?php

use App\Models\User;
use Livewire\Livewire;

test('skill search shows professionals matching skill tags', function () {
    User::factory()->professional()->create([
        'name' => 'Adaeze Okoye',
        'professional_title' => 'Brand Designer',
        'skill_tags' => ['Brand Design', 'Logo Design', 'Figma'],
    ]);

    User::factory()->professional()->create([
        'name' => 'Emeka Nwosu',
        'professional_title' => 'Full-Stack Developer',
        'skill_tags' => ['Web Development', 'Laravel', 'React'],
    ]);

    $component = Livewire::test('skill-search')
        ->set('query', 'Brand');

    $component->assertSee('Adaeze Okoye')
        ->assertDontSee('Emeka Nwosu');
});

test('skill search is case insensitive', function () {
    User::factory()->professional()->create([
        'name' => 'Fatima Al-Hassan',
        'professional_title' => 'UX Designer',
        'skill_tags' => ['UI/UX Design', 'Figma', 'Prototyping'],
    ]);

    Livewire::test('skill-search')
        ->set('query', 'ux design')
        ->assertSee('Fatima Al-Hassan');
});

test('skill search returns nothing for queries shorter than 2 characters', function () {
    User::factory()->professional()->create([
        'name' => 'Test Professional',
        'skill_tags' => ['Photography'],
    ]);

    Livewire::test('skill-search')
        ->set('query', 'P')
        ->assertDontSee('Test Professional');
});

test('skill search shows professionals without a bio', function () {
    User::factory()->professional()->create([
        'name' => 'Ibrahim Sule',
        'professional_title' => 'Photographer',
        'bio' => null,
        'skill_tags' => ['Photography', 'Videography'],
    ]);

    Livewire::test('skill-search')
        ->set('query', 'Photography')
        ->assertSee('Ibrahim Sule');
});

test('skill search also matches by professional title', function () {
    User::factory()->professional()->create([
        'name' => 'Chidi Obi',
        'professional_title' => 'Motion Graphics Artist',
        'skill_tags' => ['Animation'],
    ]);

    Livewire::test('skill-search')
        ->set('query', 'motion graphics')
        ->assertSee('Chidi Obi');
});

test('the directory lists professionals without a bio', function () {
    User::factory()->professional()->create([
        'name' => 'Ngozi Eze',
        'professional_title' => 'Content Writer',
        'bio' => null,
    ]);

    $this->get(route('directory'))->assertSee('Ngozi Eze');
});

test('the directory filters by skill', function () {
    User::factory()->professional()->create([
        'name' => 'Adaeze Okoye',
        'professional_title' => 'Brand Designer',
        'skill_tags' => ['Brand Identity'],
    ]);

    User::factory()->professional()->create([
        'name' => 'Emeka Nwosu',
        'professional_title' => 'Developer',
        'skill_tags' => ['Laravel'],
    ]);

    Livewire::test('pages::directory')
        ->set('skill', 'Brand Identity')
        ->assertSee('Adaeze Okoye')
        ->assertDontSee('Emeka Nwosu');
});

test('the directory searches by name and title', function () {
    User::factory()->professional()->create([
        'name' => 'Chidi Obi',
        'professional_title' => 'Motion Graphics Artist',
    ]);

    User::factory()->professional()->create([
        'name' => 'Fatima Al-Hassan',
        'professional_title' => 'UX Designer',
    ]);

    Livewire::test('pages::directory')
        ->set('search', 'Motion')
        ->assertSee('Chidi Obi')
        ->assertDontSee('Fatima Al-Hassan');
});
