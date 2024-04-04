<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use Stringable;

abstract class Expression implements Stringable
{
    /**
     * @param array<mixed> $data
     */
    abstract public static function fromArray(array $data): static;

    abstract public static function fromString(string $from): static;

    /** @return array<mixed> */
    abstract public function toArray(): array;
}
