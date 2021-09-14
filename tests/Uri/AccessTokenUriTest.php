<?php declare(strict_types=1);
namespace ImboClient\Uri;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass ImboClient\Uri\AccessTokenUri
 */
class AccessTokenUriTest extends TestCase
{
    /**
     * @return array<int,array{expected:string,base:string,privateKey:string}>
     */
    public function getUris(): array
    {
        return [
            [
                'expected'   => 'http://imbo?accessToken=0b74021a482388ff0a39e8aa24588329313008fc6f3baa1c434c2f02c7e52aeb',
                'base'       => 'http://imbo',
                'privateKey' => 'key',
            ],
            [
                'expected'   => 'http://imbo/users/user/images/image?t%5B%5D=foo&t%5B%5D=bar&accessToken=44d4f757f8a4ae6119c392789a277e0ac4aa6f61ff393f9a6beaadab43fde4c0',
                'base'       => 'http://imbo/users/user/images/image?t[]=foo&t[]=bar',
                'privateKey' => 'super secret private key',
            ],
        ];
    }

    /**
     * @dataProvider getUris
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testAppendsAccessTokenQueryParameter(string $expected, string $base, string $privateKey): void
    {
        $this->assertSame(
            $expected,
            (string) new AccessTokenUri($base, $privateKey),
            'Incorrect string generated',
        );
    }
}
