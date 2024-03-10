<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use Stringable;

abstract class Expression implements Stringable
{
    abstract public static function fromArray(array $data): static;

    abstract public static function fromString(string $from): static;

    abstract public function toArray(): array;
}
