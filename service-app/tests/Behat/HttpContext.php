<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\MinkExtension\Context\RawMinkContext;
use PHPUnit\Framework\Assert;

final class HttpContext extends RawMinkContext
{
    /**
     * @When I add the request header :header with the value :value
     */
    public function iAddTheRequestHeaderWithValue(string $header, string $value): void
    {
        $session = $this->getSession();

        $session->setRequestHeader(name: $header, value: $value);
    }

    /**
     * @Then the content-type should be :contentType
     */
    public function theContentTypeShouldBe(string $contentType): void
    {
        $actual = $this->getSession()->getResponseHeader('Content-Type');
        Assert::assertNotNull($actual);
        Assert::assertStringContainsStringIgnoringCase($contentType, $actual);
    }
}
