<?php

namespace Blrf\Dbal\Query;

enum ConditionType: string
{
    case AND = 'AND';
    case OR = 'OR';
}
