<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Hook\AfterScenario;
use Behat\Hook\BeforeScenario;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementHtmlException;
use Behat\Mink\Session;
use Behat\Mink\WebAssert;
use PHPUnit\Framework\Assert;
use Symfony\Component\Clock\Test\ClockSensitiveTrait;

use function trim;

final readonly class FeatureContext implements Context
{
    public function __construct(private Session $session)
    {
    }


    /**
     * @Then /^the "([^"]*)" attribute of the "(?P<element>[^"]*)" element should contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function theAttributeOfTheElementShouldContain(string $attribute, string $selector, string $value): void
    {
        $webassert = new WebAssert($this->session);
        $element = $webassert->elementAttributeExists('css', $selector, $attribute);
        $actual = (string) $element->getAttribute($attribute);

        // decode html entities in value
        $value = \htmlspecialchars_decode($value);
        // trim spaces and \n
        $actual = \preg_replace('/\s{2,}|\n+/', '', \htmlspecialchars_decode($actual)) ?? '';

        $regex = '/'.\preg_quote($value, '/').'/ui';

        if (true === (bool) \preg_match($regex, $actual)) {
            return;
        }

        throw new ElementHtmlException(\sprintf('The text "%s" was not found in the attribute "%s" of the %s. Found "%s" instead.', $value, $attribute, $selector, $actual), $this->session->getDriver(), $element);
    }

    /**
     * @Then /^the "([^"]*)" attribute of the "(?P<element>[^"]*)" element should be empty$/
     */
    public function theAttributeOfTheElementShouldBeEmpty(string $attribute, string $selector): void
    {
        $assert = new WebAssert($this->session);

        $page = $this->session->getPage();
        $node = $page->find('css', $selector);

        if ($node instanceof NodeElement && '' !== (string) $node->getAttribute($attribute)) {
            throw new \RuntimeException(\sprintf('The "%s" attribute of the "%s" element was not empty.', $attribute, $selector));
        }
    }

    /**
     * @Then /^the "([^"]*)" attribute of one of the "(?P<selector>[^"]*)" elements should contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function theAttributeOfOneOfTheElementsShouldContain(string $attribute, string $selector, string $value): void
    {
        $page = $this->session->getPage();
        $nodes = $page->findAll('css', $selector);
        $regex = '/'.\preg_quote($value, '/').'/ui';
        $foundList = '';
        if ([] === $nodes) {
            throw new \RuntimeException(\sprintf('No element "%s" was found in the HTML.', $selector));
        }

        foreach ($nodes as $node) {
            if ((bool) \preg_match($regex, (string) $node->getAttribute($attribute))) {
                return;
            }
            $foundList .= $node->getAttribute($attribute)."\n";
        }

        throw new \RuntimeException(\sprintf("The text \"%s\" was not found in the attribute \"%s\" of \"%s\", found: \n %s \n instead.", $value, $attribute, $selector, $foundList));
    }

    /**
     * @Then /^the "([^"]*)" attribute of every "(?P<selector>[^"]*)" elements should contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function theAttributeOfEveryElementsShouldContain(string $attribute, string $selector, string $value): void
    {
        $page = $this->session->getPage();
        $nodes = $page->findAll('css', $selector);
        $regex = '/'.\preg_quote($value, '/').'/ui';

        foreach ($nodes as $index => $node) {
            if (!(bool) \preg_match($regex, (string) $node->getAttribute($attribute))) {
                throw new \RuntimeException(\sprintf('The text "%s" was not found in the attribute "%s" of any "%s" element.', $value, $attribute, $selector));
            }
        }
    }

    /**
     * Checks, that at least one element with specified CSS contains specified HTML
     * Example: Then one of the "ul > li" elements should contain "style=\"color:black;\""
     * Example: And one of the "ul > li" elements should contain "style=\"color:black;\"".
     *
     * @Then /^one of the "(?P<element>[^"]*)" elements should contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function assertElementsContain(string $element, string $value): void
    {
        $page = $this->session->getPage();
        $nodes = $page->findAll('css', $element);
        $html = \str_replace('\\"', '"', $value);
        $regex = '/'.\preg_quote($html, '/').'/ui';

        foreach ($nodes as $node) {
            if ((bool) \preg_match($regex, $node->getHtml())) {
                return;
            }
        }

        throw new \RuntimeException(\sprintf('The string "%s" was not found in the HTML of "%s".', $html, $element));
    }

    /**
     * Checks, that all elements with specified CSS contains specified HTML.
     *
     * @Then /^all of the "(?P<element>[^"]*)" elements should contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function assertAllElementsContain(string $element, string $value): void
    {
        $page = $this->session->getPage();
        $nodes = $page->findAll('css', $element);
        $html = \str_replace('\\"', '"', $value);
        $regex = '/'.\preg_quote($html, '/').'/ui';

        if (0 === \count($nodes)) {
            throw new \RuntimeException(\sprintf('No element "%s" was found in the HTML.', $element));
        }

        foreach ($nodes as $node) {
            if (!(bool) \preg_match($regex, $node->getHtml())) {
                throw new \RuntimeException(\sprintf('The string "%s" was not found in the HTML of "%s".', $html, $element));
            }
        }
    }

    /**
     * Checks, that none of the elements with specified CSS contains specified HTML.
     *
     * @Then /^none of the "(?P<element>[^"]*)" elements should contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function assertNoneElementsContain(string $element, string $value): void
    {
        $page = $this->session->getPage();
        $nodes = $page->findAll('css', $element);
        if (0 === \count($nodes)) {
            throw new \RuntimeException(\sprintf('No element "%s" was found in the HTML.', $element));
        }

        $html = \str_replace('\\"', '"', $value);
        $regex = '/'.\preg_quote($html, '/').'/ui';

        foreach ($nodes as $node) {
            if (0 < \preg_match($regex, $node->getHtml())) {
                throw new \RuntimeException(\sprintf('The string "%s" has been found in the HTML of "%s".', $html, $element));
            }
        }
    }

    /**
     * @Then /^I should see "([^"]*)" in the attribute "([^"]*)" of the element "([^"]*)"$/
     */
    public function theElementShouldHaveTheAttribute(string $value, string $attribute, string $locator): void
    {
        $page = $this->session->getPage();
        $node = $page->find('css', $locator);

        if (!$node instanceof NodeElement) {
            throw new \RuntimeException(\sprintf('The element "%s" was not found.', $locator));
        }

        $actual = $node->getAttribute($attribute);

        if ($actual !== $value) {
            throw new \RuntimeException(\sprintf('The element "%s" has the attribute "%s" with the value "%s", but should be "%s".', $locator, $attribute, $actual, $value));
        }
    }

    /**
     * @Then /^I should see the input element named "([^"]*)" in the form "([^"]*)"$/
     */
    public function iShouldSeeTheInputElementNamedInTheForm(string $name, string $formLocator): void
    {
        $inputLocator = \sprintf('%s input[name="%s"]', $formLocator, $name);
        $page = $this->session->getPage();
        $node = $page->find('css', $inputLocator);

        Assert::assertInstanceOf(
            NodeElement::class,
            $node,
            \sprintf('The element "%s" was not found.', $inputLocator),
        );
    }

    /**
     * @Then /^I should not see "([^"]*)" in the attribute "([^"]*)" of the element "([^"]*)"$/
     */
    public function theElementShouldNotHaveTheAttribute(string $value, string $attribute, string $locator): void
    {
        $page = $this->session->getPage();
        $node = $page->find('css', $locator);

        if (!$node instanceof NodeElement) {
            throw new \RuntimeException(\sprintf('The element "%s" was not found.', $locator));
        }

        $actual = $node->getAttribute($attribute);

        if ($actual === $value) {
            throw new \RuntimeException(\sprintf('The element "%s" has the attribute "%s" with the value "%s", but it should not be the case.', $locator, $attribute, $value));
        }
    }

    /**
     * Checks element contains digits this is useful when checking things like number of results or buckets (facets..)
     * and avoid depending on the dataset.
     *
     * @Then /^the "(?P<element>[^"]*)" element should contain digits/
     */
    public function theElementShouldContainDigits(string $element): void
    {
        $page = $this->session->getPage();
        $node = $page->find('css', $element);

        if (!$node instanceof NodeElement) {
            throw new \RuntimeException(\sprintf('The element "%s" was not found.', $element));
        }
        $content = $node->getHtml();
        if ((bool) \preg_match('/^\d+$/', $content)) {
            return;
        }

        throw new \RuntimeException(\sprintf('"%s" doesn\'t contain digits.', $element));
    }

    /**
     * @Given I set the clock to :date
     */
    public function setDate(string $date): void
    {
        self::mockTime($date);
    }

    /**
     * @Then /^The property "([^"]*)" should contain "([^"]*)" in XML response$/
     *
     * @throws \Exception
     */
    public function thePropertyShouldContainInXmlResponse(string $property, string $value): void
    {
        $page = $this->session->getPage();
        $xml = new \SimpleXMLElement($page->getContent());

        $res = $xml->xpath($property);
        if (null === $res || false === $res) {
            throw new \RuntimeException(\sprintf('The response object does not have "%s" property.', $property));
        }

        /** @var \SimpleXMLElement $xmlValue */
        $xmlValue = $res[0];
        Assert::assertSame($value, (string) $xmlValue);
    }

    /**
     * @Then /^The "([^"]*)" namespace should point to "([^"]*)" in XML response$/
     *
     * @throws \Exception
     */
    public function theNamespaceShouldPointToInXmlResponse(string $namespace, string $value): void
    {
        $page = $this->session->getPage();
        $xml = new \SimpleXMLElement($page->getContent());
        $ns = $xml->getNamespaces(true);
        Assert::assertArrayHasKey($namespace, $ns);
        Assert::assertSame($value, $ns[$namespace]);
    }

    /**
     * @Then /^the Json property "([^"]*)" should not exist$/
     */
    public function JsonPropertyShouldNotExist(string $property): void
    {
        $page = $this->session->getPage();
        $json = \json_decode($page->getContent(), true);

        if (!\is_array($json)) {
            throw new \RuntimeException('invalid Json');
        }

        Assert::assertFalse(isset($json[$property]));
    }

    /**
     * @Then /^one of the properties "([^"]*)" should contain "([^"]*)" in XML response$/
     *
     * @throws \Exception
     */
    public function oneOfThePropertiesShouldContainInXmlResponse(string $property, string $value): void
    {
        $page = $this->session->getPage();
        $xml = new \SimpleXMLElement($page->getContent());

        $properties = $xml->xpath($property);
        if (null === $properties || false === $properties) {
            throw new \RuntimeException(\sprintf('The response object does not have "%s" property.', $property));
        }

        foreach ($properties as $p) {
            if (!$p instanceof \SimpleXMLElement) {
                continue;
            }
            if ($value === (string) $p) {
                return;
            }
        }
        throw new \RuntimeException(\sprintf('The properties "%s" does not contain "%s" value.', $property, $value));
    }

    /**
     * @Then /^I should see "([^"]*)" in the Json property "([^"]*)"$/
     */
    public function JsonPropertyShouldEquals(string $value, string $property): void
    {
        $page = $this->session->getPage();
        $json = \json_decode($page->getContent(), true);

        if (!\is_array($json)) {
            throw new \RuntimeException('invalid Json');
        }

        if (!isset($json[$property])) {
            throw new \RuntimeException(\sprintf('the property %s was not found', $property));
        }

        Assert::assertSame($value, $json[$property]);
    }

    /**
     * @Then /^I should see "([^"]*)" in one of the nested Json properties "([^"]*)"$/
     */
    public function NestedJsonPropertyShouldEquals(string $value, string $property): void
    {
        $page = $this->session->getPage();
        $json = \json_decode($page->getContent(), true);

        if (!\is_array($json)) {
            throw new \RuntimeException('invalid Json');
        }

        $result = [];
        \array_walk_recursive($json, static function ($v, $k) use ($property, &$result): void {
            if ($k === $property) {
                $result[] = $v;
            }
        });

        Assert::assertContains($value, $result);
    }

    /**
     * @Then /^The header "([^"]*)" contains "([^"]*)" in the response header$/
     */
    public function checkHeaderExistWithValue(string $name, string $value): void
    {
        $actualValue = $this->session->getResponseHeader($name);
        Assert::assertNotEmpty($actualValue);
        Assert::assertSame($value, $actualValue);
    }

    /**
     * @Then /^The path "([^"]*)" should contain "(.*)" in JSON response$/
     *
     * @throws \Exception
     */
    public function thePathShouldContainInJsonResponse(string $path, string $value): void
    {
        $page = $this->session->getPage();
        /** @var array<string, string> $json */
        $json = \json_decode($page->getContent(), true);
        $actualValue = self::walkJsonResponse($json, $path);

        Assert::assertNotEmpty($actualValue);
        Assert::assertSame($actualValue, $value);
    }

    /**
     * @Then /^The path "([^"]*)" should contain ([^"]*) elements? in JSON response$/
     */
    public function thePathShouldContainNElementInJsonResponse(string $path, int $value): void
    {
        $page = $this->session->getPage();
        /** @var array<string, string> $json */
        $json = \json_decode($page->getContent(), true);
        $actualValue = self::walkJsonResponse($json, $path);
        Assert::assertIsArray($actualValue);
        Assert::assertCount($value, $actualValue);
    }

    /**
     * @Given /^the response content should start with "([^"]*)"$/
     *
     * @throws \Exception
     */
    public function theResponseContentShouldStartWith(string $value): void
    {
        $page = $this->session->getPage();
        $bibtex = $page->getContent();

        if (!\is_string($bibtex)) {
            throw new \Exception('invalid bibtex');
        }

        if ('' !== $value) {
            Assert::assertStringStartsWith($value, $bibtex);
        }
    }

    /**
     * @Given /^each node of the response should respect the bibtex's rules$/
     *
     * @throws \Exception
     */
    public function eachNodeOfTheResponseShouldRespectTheBibtexSRules(): void
    {
        $page = $this->session->getPage();
        $bibtex = $page->getContent();

        if (!\is_string($bibtex)) {
            throw new \Exception('invalid bibtex');
        }

        $bibtex = \explode(\PHP_EOL, $bibtex);
        for ($i = 1; $i < \count($bibtex) - 1; ++$i) {
            $pattern = "/^\s*[A-Z_]+(\s*=\s*)(([A-Z][a-z]{2})|(\{[^{}]*\})|(\{\{[^{}]*\}\})),$/";
            $test = $bibtex[$i];
            Assert::assertMatchesRegularExpression($pattern, $test);
        }
    }

    /**
     * @Given /^I should see "([^"]*)" in the "([^"]*)" field in my bibtex object$/
     *
     * @throws \Exception
     */
    public function iShouldSeeInTheFieldInMyBibtexObject(string $value, string $field): void
    {
        $page = $this->session->getPage();
        $bibtex = $page->getContent();

        if (!\is_string($bibtex)) {
            throw new \Exception('invalid bibtex');
        }

        $escape = ['/', '(', ')', '{', '}', '\''];
        $replace = ['\/', '\(', '\)', '\{', '\}', '\\\''];
        $value = \str_replace($escape, $replace, $value);
        $searchPattern = '/'.$field.' = '.$value.'/';
        Assert::assertMatchesRegularExpression($searchPattern, $bibtex);
    }

    /**
     * @Given /^the property "([^"]*)" should be present "([^"]*)" time\(s\)$/
     *
     * @throws \Exception
     */
    public function thePropertyShouldBePresentTimeS(string $property, int $value): void
    {
        $page = $this->session->getPage();
        $xml = new \SimpleXMLElement($page->getContent());

        $xmlValue = $xml->xpath($property);
        Assert::assertSame($value, \count((array) $xmlValue));
    }

    /**
     * @Given /^the property "([^"]*)" should not be present$/
     *
     * @throws \Exception
     */
    public function thePropertyShouldNotBePresent(string $property): void
    {
        $page = $this->session->getPage();
        $xml = new \SimpleXMLElement($page->getContent());

        $xmlValue = $xml->xpath($property);
        Assert::assertSame(0, \count((array) $xmlValue));
    }

    /**
     * @Transform :list
     *
     * @return string[]
     */
    public function commaSeparatedListToArray(string $string): array
    {
        return \array_map(static fn ($item) => \strtolower(\trim($item)), \explode(',', $string));
    }

    /**
     * @Then the headers of the csv content using :delimiter as delimiter should be :list
     *
     * @param string[] $list
     */
    public function csvHeaderShouldBe(string $delimiter, array $list): void
    {
        $csv = $this->session->getPage()->getContent();
        $headers = \explode("\n", $csv)[0];
        $parsed = \array_map(static fn ($item) => \strtolower(\trim($item)), \array_filter(\str_getcsv($headers, $delimiter), static fn ($item) => \is_string($item)));
        $headers = \implode(',', $parsed);
        foreach ($list as $value) {
            Assert::assertTrue(\in_array($value, $parsed, true), "$value not found in list: $headers");
        }
    }

    /**
     * @Then /^one of the line in the csv content using "([^"]*)" as delimiter should contain "([^"]*)"$/
     */
    public function csvLineShouldContain(string $delimiter, string $needle): void
    {
        $csv = $this->session->getPage()->getContent();
        $lines = \explode("\n", $csv);
        $ok = false;
        $needle = \trim($needle);
        foreach ($lines as $line) {
            $parsed = \array_map(static fn ($item) => \trim($item, "\" \t"), \array_filter(\str_getcsv($line, $delimiter), static fn ($item) => \is_string($item)));
            $ok = $ok || \in_array($needle, $parsed, true);
        }

        Assert::assertTrue($ok, "$needle not found in csv file :\n $csv");
    }

    /**
     * @Given /^the csv should have (\d+) lines$/
     */
    public function theCsvShouldHaveLines(int $numberOfLines): void
    {
        $csv = $this->session->getPage()->getContent();
        $lines = \explode("\n", $csv);
        Assert::assertCount($numberOfLines, $lines);
    }

    /**
     * @Given /^I should not see an element matching "([^"]*)"$/
     */
    public function iShouldNotSeeAnElementMatching(string $locator): void
    {
        $page = $this->session->getPage();
        $node = $page->find('css', $locator);

        if (null !== $node) {
            throw new \RuntimeException(\sprintf('The element "%s" was found (and should not be).', $locator));
        }
    }

    /**
     * Le path contient le chemin vers l'attribut voulu avec un '.' comme séparateur entre les noeuds.
     *
     * @param array<string, string> $json
     */
    private static function walkJsonResponse(array $json, string $path): mixed
    {
        $nodes = \explode('.', $path);
        foreach ($nodes as $node) {
            // Si $node contient un indice de tableau, on récupère la partie JSON associé
            if ((bool) \preg_match('/^(.*)\[(\d+)]$/', $node, $match)) {
                /** @var string[] $match
                 */
                $json = $json[$match[1]][$match[2]];
            } else {
                $json = $json[$node];
            }
        }

        return $json;
    }
}
