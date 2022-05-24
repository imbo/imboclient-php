<?php declare(strict_types=1);
namespace ImboClient\Response;

use Countable;
use ImboClient\Utils;
use Iterator;
use Psr\Http\Message\ResponseInterface;

class AccessControlRules extends ApiResponse implements Iterator, Countable
{
    private int $iteratorIndex = 0;
    /** @var array<AccessControlRule> */
    private array $rules;

    /**
     * @param array<AccessControlRule> $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    public static function fromHttpResponse(ResponseInterface $response): self
    {
        /** @var array<array{id:string,resources?:array<string>,group?:string,users:string|array<string>}> */
        $body = Utils::convertResponseToArray($response);
        $accessControlRules = array_map(
            fn (array $rule): AccessControlRule => new AccessControlRule(
                $rule['id'],
                $rule['users'],
                $rule['resources'] ?? null,
                $rule['group'] ?? null,
            ),
            $body,
        );

        return new self($accessControlRules);
    }

    public function rewind(): void
    {
        $this->iteratorIndex = 0;
    }

    public function current(): AccessControlRule
    {
        return $this->rules[$this->iteratorIndex];
    }

    public function key(): int
    {
        return $this->iteratorIndex;
    }

    public function next(): void
    {
        $this->iteratorIndex++;
    }

    public function valid(): bool
    {
        return isset($this->rules[$this->iteratorIndex]);
    }

    public function count(): int
    {
        return count($this->rules);
    }

    protected function getArrayOffsets(): array
    {
        return [
            'rules' => fn (): array => $this->rules,
        ];
    }
}
