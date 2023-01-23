<?php declare(strict_types=1);
namespace ImboClient\Response;

use ArrayAccess;
use ImboClient\Exception\RuntimeException;
use Psr\Http\Message\ResponseInterface;

/**
 * @template-implements ArrayAccess<string, mixed>
 */
abstract class ApiResponse implements ArrayAccess
{
    private ?ResponseInterface $response = null;

    /**
     * @return array<string,callable>
     */
    abstract protected function getArrayOffsets(): array;

    /**
     * @return static
     */
    public function withResponse(ResponseInterface $response): self
    {
        $clone = clone $this;
        $clone->response = $response;
        return $clone;
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return in_array($offset, array_keys($this->getArrayOffsets()));
    }

    /**
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        $offsets = $this->getArrayOffsets();
        return array_key_exists($offset, $offsets) ? $offsets[$offset]() : null;
    }

    public function offsetSet($offset, $value): void
    {
        throw new RuntimeException('Not supported');
    }

    public function offsetUnset($offset): void
    {
        throw new RuntimeException('Not supported');
    }
}
