<?php declare(strict_types=1);
namespace ImboClient\Middleware;

use ArrayObject;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use ImboClient\Exception\RuntimeException;
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
        $assertions = function (RequestInterface $request, array $_options): PromiseInterface {
            $this->assertNotEmpty($request->getHeaderLine('x-imbo-authenticate-signature'));
            $this->assertNotEmpty($request->getHeaderLine('x-imbo-authenticate-timestamp'));
            return $this->createMock(PromiseInterface::class);
        };

        $middleware = new Authenticate('public', 'private');
        $middleware($assertions)(new Request('GET', 'http://localhost'), ['require_imbo_signature' => true]);
    }

    /**
     * @covers ::addAuthenticationHeaders
     * @covers ::__invoke
     */
    public function testSignaturesAreUnique(): void
    {
        $numSignaturesToGenerate = 100;
        $signatures = new ArrayObject();

        $assertions = function (RequestInterface $request, array $_options) use ($signatures): PromiseInterface {
            $signatures->append($request->getHeaderLine('x-imbo-authenticate-signature'));
            return $this->createMock(PromiseInterface::class);
        };

        for ($i = 0; $i < $numSignaturesToGenerate; $i++) {
            $middleware = new Authenticate('public', uniqid('', true));
            $middleware($assertions)(new Request('GET', 'http://localhost/'), ['require_imbo_signature' => true]);
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

    /**
     * @return array<string,array{options:array<string,bool>}>
     */
    public function getOptions(): array
    {
        return [
            'missing option' => [
                'options' => [],
            ],
            'option is false' => [
                'options' => [
                    'require_imbo_signature' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider getOptions
     * @covers ::__invoke
     */
    public function testDoesNotAddSignatureWhenOptionIsNotSet(array $options): void
    {
        $assertions = function (RequestInterface $request, array $_options): PromiseInterface {
            $this->assertEmpty($request->getHeaderLine('x-imbo-authenticate-signature'));
            $this->assertEmpty($request->getHeaderLine('x-imbo-authenticate-timestamp'));
            return $this->createMock(PromiseInterface::class);
        };

        $middleware = new Authenticate('public', 'private');
        $middleware($assertions)(new Request('GET', 'http://localhost'), $options);
    }

    /**
     * @covers ::__invoke
     */
    public function testThrowsExceptionWhenHandlerResultIsIncorrect(): void
    {
        $handler = function (RequestInterface $_request, array $_options): RequestInterface {
            return $this->createMock(RequestInterface::class);
        };

        $middleware = new Authenticate('public', 'private');

        $this->expectException(RuntimeException::class);
        $middleware($handler)(new Request('GET', 'http://localhost'), []);
    }
}
