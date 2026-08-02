<?php

namespace Tests\Feature\Public;

use App\Http\Controllers\Public\Pages\ContactPageController;
use Inertia\Response;
use ReflectionClass;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    public function test_contact_page_controller_returns_expected_inertia_component(): void
    {
        $response = (new ContactPageController)->render();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('public/Contact', $this->componentName($response));
    }

    private function componentName(Response $response): string
    {
        $reflection = new ReflectionClass($response);
        $property = $reflection->getProperty('component');
        $property->setAccessible(true);

        return $property->getValue($response);
    }
}
