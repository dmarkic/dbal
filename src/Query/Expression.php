<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use Stringable;

abstract class Expression implements Stringable
{

    /** @return array<mixed> */
    abstract public function toArray(): array;
}
