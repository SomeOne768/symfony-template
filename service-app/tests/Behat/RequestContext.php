<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Common\Gate\GateResolverInterface;
use App\Common\Gate\GateSubscriber;
use BadMethodCallException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Session;
use FriendsOfBehat\SymfonyExtension\Driver\SymfonyDriver;
use LogicException;
use PHPUnit\Framework\Assert;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function array_key_exists;
use function array_merge;
use function is_array;
use function is_string;
use function json_decode;
use function sprintf;

final readonly class RequestContext implements Context
{
    private string $tokenName;

    public function __construct(
        private Session $session,
        private ParameterBagInterface $params,
    ) {
        $tokenName = $this->params->get('form.type_extension.csrf.field_name');
        if (!is_string($tokenName)) {
            throw new RuntimeException('Token name should be a string');
        }
        $this->tokenName = $tokenName;
    }

    /**
     * @When /^I POST request "([^"]*)" with payload$/
     */
    public function iPostRequestWithPayload(string $uri, PyStringNode $payload): void
    {
        /** @var array<string, int|string|array<string, int|string>> $postParams */
        $postParams = (array) json_decode($payload->getRaw(), true);
        /** @var SymfonyDriver $driver */
        $driver = $this->session->getDriver();

        /** @var KernelBrowser $client */
        $client = $driver->getClient();

        $client->request(
            method: Request::METHOD_POST,
            uri: $uri,
            parameters: $postParams,
        );
    }


    /**
     * @When /^I make POST XmlHttp request to "([^"]*)" with payload$/
     *
     */
    public function iMakePostXmlHttpRequest(
        string $uri,
        PyStringNode $payload,
    ): void {
        /** @var SymfonyDriver $driver */
        $driver = $this->session->getDriver();

        /** @var KernelBrowser $client */
        $client = $driver->getClient();

        $client->xmlHttpRequest(
            method: Request::METHOD_POST,
            uri: $uri,
            server: [],
            content: $payload->getRaw(),
        );
    }

    /**
     * @When /^I make POST XmlHttp request to "([^"]*)"$/
     */
    public function iMakePostXmlHttpRequestWithoutPayload(
        string $uri,
    ): void {
        /** @var SymfonyDriver $driver */
        $driver = $this->session->getDriver();

        /** @var KernelBrowser $client */
        $client = $driver->getClient();

        $client->xmlHttpRequest(
            method: Request::METHOD_POST,
            uri: $uri,
        );
    }

    /**
     * @When /^I make DELETE XmlHttp request to "([^"]*)"$/
     */
    public function iMakeDeleteXmlHttpRequestWithoutPayload(
        string $uri,
    ): void {
        /** @var SymfonyDriver $driver */
        $driver = $this->session->getDriver();

        /** @var KernelBrowser $client */
        $client = $driver->getClient();

        $client->xmlHttpRequest(
            method: Request::METHOD_DELETE,
            uri: $uri,
        );
    }


    /**
     * @Then /^I get CSRF token from form named "([^"]*)" for uri "([^"]*)"$/
     */
    public function iGetCsrfTokenFromFormNamedForUri(
        string $formName,
        string $uri,
    ): ?string {
        /** @var SymfonyDriver $driver */
        $driver = $this->session->getDriver();

        /** @var KernelBrowser $client */
        $client = $driver->getClient();

        $client->request(
            method: Request::METHOD_GET,
            uri: $uri,
        );

        $response = $client->getResponse();
        if (!$response instanceof Response) {
            throw new RuntimeException('No response received');
        }
        $content = $response->getContent();
        if (false === $content) {
            throw new RuntimeException('No content received');
        }

        $crawler = new Crawler($content);
        $inputToken = $crawler->filter(sprintf('input[type="hidden"][name="%s[%s]"]',
            $formName,
            $this->tokenName,
        ));

        return $inputToken->attr('value');
    }

    /**
     * @When /^I make XmlHttp request to "([^"]*)"$/
     */
    public function iMakeXmlHttpRequest(
        string $uri
    ): void {
        /** @var SymfonyDriver $driver */
        $driver = $this->session->getDriver();

        /** @var KernelBrowser $client */
        $client = $driver->getClient();

        $client->xmlHttpRequest(
            method: Request::METHOD_GET,
            uri: $uri,
            server: [],
        );
    }


    /**
     * @Then /^the response content should be$/
     */
    public function theResponseContentShouldBe(PyStringNode $content): void
    {
        /** @var SymfonyDriver $driver */
        $driver = $this->session->getDriver();

        /** @var KernelBrowser $client */
        $client = $driver->getClient();

        $response = $client->getResponse();
        if (!$response instanceof Response) {
            throw new RuntimeException('No response received');
        }
        $expected = $response->getContent();
        if (false === $expected) {
            throw new RuntimeException('No content received');
        }

        $actual = $content->getRaw();
        Assert::assertSame($expected, $actual);
    }

    /**
     * @Then /^the response property "([^"]*)" should equal "([^"]*)"$/
     */
    public function theResponsePropertyShouldEquals(string $property, string $aimed): void
    {
        /** @var SymfonyDriver $driver */
        $driver = $this->session->getDriver();

        /** @var KernelBrowser $client */
        $client = $driver->getClient();

        $response = $client->getResponse();
        if (!$response instanceof Response) {
            throw new RuntimeException('No response received');
        }

        $content = $response->getContent();
        if (false === $content) {
            throw new RuntimeException('No content received');
        }

        $content = json_decode($content, true);
        if (!is_array($content)) {
            throw new RuntimeException('cannot decode body');
        } elseif (!array_key_exists($property, $content)) {
            throw new RuntimeException(sprintf('%s is not defined', $property));
        }

        Assert::assertEquals($aimed, $content[$property]);
    }
}
