<?php

declare(strict_types=1);

namespace Pheature\Community\Laravel\Read;

use Illuminate\Support\Facades\DB;
use Pheature\Core\Toggle\Read\Feature;
use Pheature\Core\Toggle\Read\FeatureFinder;
use Pheature\Community\Laravel\Exception\EloquentFeatureNotFound;

use function array_map;
use function is_array;

/**
 * @psalm-import-type EloquentFeature from \Pheature\Community\Laravel\Read\EloquentFeatureFactory
 */
final class EloquentFeatureFinder implements FeatureFinder
{
    private EloquentFeatureFactory $featureFactory;

    public function __construct(EloquentFeatureFactory $featureFactory)
    {
        $this->featureFactory = $featureFactory;
    }

    public function get(string $featureId): Feature
    {
        $sql = <<<SQL
        SELECT * FROM pheature_toggles WHERE feature_id = :feature_id
        SQL;

        /** @var EloquentFeature|false $feature */
        $feature = DB::select($sql, ['feature_id' => $featureId]);

        if (false === is_array($feature)) {
            throw EloquentFeatureNotFound::withId($featureId);
        }

        return $this->featureFactory->create($feature);
    }

    /**
     * @return Feature[]
     * @throws \JsonException
     */
    public function all(): array
    {
        $sql = <<<SQL
        SELECT * FROM pheature_toggles ORDER BY created_at DESC
        SQL;

        /** @var object[]|false $rawFeatures */
        $rawFeatures = DB::select($sql);
        if (false === $rawFeatures) {
            return [];
        }

        $features = [];

        foreach ($rawFeatures as $rawFeature) {
            /** @var EloquentFeature $arrayFeature */
            $arrayFeature = (array)$rawFeature;
            $features[] = $this->featureFactory->create($arrayFeature);
        }

        return $features;
    }
}
