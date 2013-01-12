<?php
/**
 * This file is part of the ImboClient package
 *
 * (c) Christer Edvartsen <cogo@starzinger.net>
 *
 * For the full copyright and license information, please view the LICENSE file that was
 * distributed with this source code.
 */

namespace ImboClient\Url;

/**
 * @package ImboClient\TestSuite
 * @author Christer Edvartsen <cogo@starzinger.net>
 */
class UrlTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ImboClient\Url\Url
     */
    private $url;

    /**
     * @var string
     */
    private $baseUrl = 'http://imbo';

    /**
     * @var string
     */
    private $publicKey = 'key';

    /**
     * @var string
     */
    private $privateKey = 'key';

    /**
     * Set up the url instance
     *
     * @covers ImboClient\Url\Url::__construct
     */
    public function setUp() {
        $this->url = new UrlImplementation($this->baseUrl, $this->publicKey, $this->privateKey);
    }

    /**
     * Tear down the url instance
     */
    public function tearDown() {
        $this->url = null;
    }

    /**
     * The url instance must return the complete url when used in a string context
     *
     * @covers ImboClient\Url\Url::__toString
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testReturnsTheCompleteUrlWhenUsedInAStringContext() {
        $asString = (string) $this->url;
        $this->assertSame($this->url->getUrl(), $asString);
        $this->assertStringStartsWith($this->baseUrl . '/resource?', $asString);
    }

    /**
     * The url instance must create an access token if one does not exist when first accessed
     *
     * @covers ImboClient\Url\Url::getAccessToken
     */
    public function testCanAutomaticallyCreateAnAccessTokenInstanceIfOneIsNotSet() {
        $this->assertInstanceOf('ImboClient\Url\AccessToken', $this->url->getAccessToken());
    }

    /**
     * The url instance must be able to set and get an access token instance
     *
     * @covers ImboClient\Url\Url::getAccessToken
     * @covers ImboClient\Url\Url::setAccessToken
     */
    public function testCanSetAndGetAnAccessTokenInstance() {
        $accessToken = $this->getMock('ImboClient\Url\AccessTokenInterface');

        $this->assertSame($this->url, $this->url->setAccessToken($accessToken));
        $this->assertSame($accessToken, $this->url->getAccessToken());
    }

    /**
     * Fetch query parameters
     *
     * @return array[]
     */
    public function getQueryParams() {
        return array(
            array(
                'key', 'value',
                'http://imbo/resource?key=value&accessToken=',
                'http://imbo/resource?key=value&amp;accessToken=',
            ),
            array(
                't[]', 'border',
                'http://imbo/resource?t[]=border&',
                'http://imbo/resource?t%5B%5D=border&amp;',
            ),
        );
    }

    /**
     * The url instance must be able to add different query parameters that is added in the query
     * string
     *
     * @dataProvider getQueryParams
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     * @covers ImboClient\Url\Url::getUrlEncoded
     * @covers ImboClient\Url\Url::getQueryString
     */
    public function testCanAddQueryParametersThatIsAddedInTheQueryString($key, $value, $expectedUrl, $expectedEscapedUrl) {
        $this->assertSame($this->url, $this->url->addQueryParam($key, $value));
        $this->assertStringStartsWith($expectedUrl, $this->url->getUrl());
        $this->assertStringStartsWith($expectedEscapedUrl, $this->url->getUrlEncoded());
    }

    /**
     * The url instance must be able to reset the added query parameters
     *
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::reset
     * @covers ImboClient\Url\Url::getUrl
     * @covers ImboClient\Url\Url::getUrlEncoded
     * @covers ImboClient\Url\Url::getQueryString
     */
    public function testAddMultipleQueryParamsAndReset() {
        $this->assertSame($this->url, $this->url->addQueryParam('t[]', 'border'));
        $this->assertSame($this->url, $this->url->addQueryParam('query', '{"foo":"bar"}'));

        $this->assertStringStartsWith(
            'http://imbo/resource?t[]=border&query=' . urlencode('{"foo":"bar"}') . '&accessToken=',
            $this->url->getUrl()
        );

        $this->assertStringStartsWith(
            'http://imbo/resource?t%5B%5D=border&amp;query=' . urlencode('{"foo":"bar"}') . '&amp;accessToken=',
            $this->url->getUrlEncoded()
        );

        $this->assertSame($this->url, $this->url->reset());
        $this->assertStringStartsWith('http://imbo/resource?accessToken=', $this->url->getUrlEncoded());
    }

    /**
     * The url instance can add query parameters using the magic __call method
     *
     * @covers ImboClient\Url\Url::__call
     * @covers ImboClient\Url\Url::addQueryParam
     * @covers ImboClient\Url\Url::getUrl
     * @covers ImboClient\Url\Url::getUrlEncoded
     * @covers ImboClient\Url\Url::getQueryString
     */
    public function testCanAddQueryParametersUsingTheMagicCallMethod() {
        $this->assertSame($this->url, $this->url->foo('bar')->bar()->baz(''));

        $this->assertStringStartsWith(
            'http://imbo/resource?foo=bar&baz=&accessToken=',
            $this->url->getUrl()
        );
        $this->assertStringStartsWith(
            'http://imbo/resource?foo=bar&amp;baz=&amp;accessToken=',
            $this->url->getUrlEncoded()
        );
    }

    /**
     * The url instance does not append an access token if the public or private key is missing
     *
     * @covers ImboClient\Url\Url::getUrl
     */
    public function testDoesNotAppendAccessTokenIfPublicOrPrivateKeyAreMissing() {
        $url = new UrlImplementation($this->baseUrl, null, null);
        $this->assertSame('http://imbo/resource', $url->getUrl());
    }
}

/**
 * URL implementation used for this test case
 */
class UrlImplementation extends Url {
    /**
     * {@inheritdoc}
     */
    protected function getResourceUrl() {
        return $this->baseUrl . '/resource';
    }
}
