<?php declare(strict_types=1);

namespace ImboClient\Middleware;

use ArrayObject;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use ImboClient\Exception\RuntimeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

use function count;
use function sprintf;

#[CoversClass(Authenticate::class)]
class AuthenticateTest extends TestCase
{
    public function testCanAddHeaders(): void
    {
        $assertions = function (RequestInterface $request, array $options): PromiseInterface {
            $this->assertNotEmpty($request->getHeaderLine('x-imbo-publickey'));
            $this->assertNotEmpty($request->getHeaderLine('x-imbo-authenticate-signature'));
            $this->assertNotEmpty($request->getHeaderLine('x-imbo-authenticate-timestamp'));

            return $this->createStub(PromiseInterface::class);
        };

        $middleware = new Authenticate('public', 'private');
        $middleware($assertions)(new Request('GET', 'http://localhost'), ['require_imbo_signature' => true]);
    }

    public function testSignaturesAreUnique(): void
    {
        $numSignaturesToGenerate = 100;
        $signatures = new ArrayObject();

        $assertions = function (RequestInterface $request, array $options) use ($signatures): PromiseInterface {
            $signatures->append($request->getHeaderLine('x-imbo-authenticate-signature'));

            return $this->createStub(PromiseInterface::class);
        };

        for ($i = 0; $i < $numSignaturesToGenerate; ++$i) {
            $middleware = new Authenticate('public', uniqid('', true));
            $middleware($assertions)(new Request('GET', 'http://localhost/'), ['require_imbo_signature' => true]);
        }

        $this->assertCount(
            $numSignaturesToGenerate,
            $signatures,
            sprintf('Expected %d signatures', $numSignaturesToGenerate),
        );
        $this->assertCount(
            count(array_unique($signatures->getArrayCopy())),
            $signatures,
            'Did not expect duplicate signatures',
        );
    }

    #[DataProvider('getOptions')]
    public function testDoesNotAddSignatureWhenOptionIsNotSet(array $options): void
    {
        $assertions = function (RequestInterface $request, array $options): PromiseInterface {
            $this->assertEmpty($request->getHeaderLine('x-imbo-publickey'));
            $this->assertEmpty($request->getHeaderLine('x-imbo-authenticate-signature'));
            $this->assertEmpty($request->getHeaderLine('x-imbo-authenticate-timestamp'));

            return $this->createStub(PromiseInterface::class);
        };

        $middleware = new Authenticate('public', 'private');
        $middleware($assertions)(new Request('GET', 'http://localhost'), $options);
    }

    public function testThrowsExceptionWhenHandlerResultIsIncorrect(): void
    {
        $handler = function (RequestInterface $request, array $options): RequestInterface {
            return $this->createStub(RequestInterface::class);
        };

        $middleware = new Authenticate('public', 'private');

        $this->expectException(RuntimeException::class);
        $middleware($handler)(new Request('GET', 'http://localhost'), []);
    }

    /**
     * @return array<string,array{options:array<string,bool>}>
     */
    public static function getOptions(): array
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
}
