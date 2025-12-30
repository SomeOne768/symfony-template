<?php

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Step\Then;
use Behat\Step\When;

class FeatureContext implements Context, SnippetAcceptingContext
{
    #[When('a demo scenario sends a request to :arg1')]
    public function aDemoScenarioSendsARequestTo($arg1): void
    {
        // TODO: implémenter la requête vers le kernel Symfony
        throw new PendingException();
    }

    #[Then('the response should be received')]
    public function theResponseShouldBeReceived(): void
    {
        // TODO: vérifier la réponse
        throw new PendingException();
    }
}
