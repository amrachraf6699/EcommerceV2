<?php

namespace Tests\Unit;

use App\Rules\SafeSlug;
use PHPUnit\Framework\TestCase;

class SafeSlugTest extends TestCase
{
    public function test_it_accepts_url_safe_slugs(): void
    {
        foreach (['shoes', 'air-max-270', 'product-123'] as $slug) {
            $this->assertTrue(SafeSlug::isValid($slug));
        }
    }

    public function test_it_rejects_route_breaking_or_ambiguous_characters(): void
    {
        foreach (['category/item', 'hello world', 'hello_world', 'hello.world', '-leading', 'trailing-', 'double--dash', 'UPPERCASE'] as $slug) {
            $this->assertFalse(SafeSlug::isValid($slug));
        }
    }
}
