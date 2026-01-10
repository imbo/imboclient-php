<?php declare(strict_types=1);

namespace ImboClient\Url;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AccessTokenUrl::class)]
class AccessTokenUrlTest extends TestCase
{
    #[DataProvider('getUrls')]
    public function testAppendsAccessTokenQueryParameter(string $expected, string $base, string $privateKey): void
    {
        $this->assertSame(
            $expected,
            (string) new AccessTokenUrl($base, $privateKey),
            'Incorrect string generated',
        );
    }

    /**
     * @return array<int,array{expected:string,base:string,privateKey:string}>
     */
    public static function getUrls(): array
    {
        return [
            [
                'expected' => 'http://imbo?accessToken=0b74021a482388ff0a39e8aa24588329313008fc6f3baa1c434c2f02c7e52aeb',
                'base' => 'http://imbo',
                'privateKey' => 'key',
            ],
            [
                'expected' => 'http://imbo/users/user/images/image?t%5B%5D=foo&t%5B%5D=bar&accessToken=44d4f757f8a4ae6119c392789a277e0ac4aa6f61ff393f9a6beaadab43fde4c0',
                'base' => 'http://imbo/users/user/images/image?t[]=foo&t[]=bar',
                'privateKey' => 'super secret private key',
            ],
        ];
    }
}
