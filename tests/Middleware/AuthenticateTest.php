<?php declare(strict_types=1);
namespace ImboClient\Middleware;

use ArrayObject;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * @coversDefaultClass ImboClient\Middleware\Authenticate
 */
class AuthenticateTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::__invoke
     * @covers ::addAuthenticationHeaders
     */
    public function testCanAddHeaders(): void
    {
        $assertions = function (RequestInterface $request, array $_): PromiseInterface {
            $this->assertNotEmpty($request->getHeaderLine('x-imbo-authenticate-signature'));
            $this->assertNotEmpty($request->getHeaderLine('x-imbo-authenticate-timestamp'));
            return $this->createMock(PromiseInterface::class);
        };

        $middleware = new Authenticate('public', 'private');
        $middleware($assertions)(new Request('GET', 'http://localhost'), []);
    }

    /**
     * @covers ::addAuthenticationHeaders
     */
    public function testSignaturesAreUnique(): void
    {
        $numSignaturesToGenerate = 100;
        $signatures = new ArrayObject();

        $assertions = function (RequestInterface $request, array $_) use ($signatures): PromiseInterface {
            $signatures->append($request->getHeaderLine('x-imbo-authenticate-signature'));
            return $this->createMock(PromiseInterface::class);
        };

        for ($i = 0; $i < $numSignaturesToGenerate; $i++) {
            $middleware = new Authenticate('public', uniqid('', true));
            $middleware($assertions)(new Request('GET', 'http://localhost/'), []);
        }

        $this->assertCount(
            $numSignaturesToGenerate,
            $signatures,
            sprintf('Expected %d signatures', $numSignaturesToGenerate),
        );
        $this->assertSame(
            count(array_unique($signatures->getArrayCopy())),
            $signatures->count(),
            'Did not expect duplicate signatures',
        );
    }
}
