<?php

declare(strict_types=1);

namespace Pheature\Community\Laravel\Exception;

use InvalidArgumentException;
use Pheature\Core\Toggle\Exception\FeatureNotFoundException;

final class EloquentFeatureNotFound extends InvalidArgumentException implements FeatureNotFoundException
{
    public static function withId(string $featureId): self
    {
        return new self(
            sprintf(
                'There is not feature with id %s in database.',
                $featureId
            )
        );
    }
}
