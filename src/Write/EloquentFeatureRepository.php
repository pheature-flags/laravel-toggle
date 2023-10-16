<?php

declare(strict_types=1);

namespace Pheature\Community\Laravel\Write;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Pheature\Core\Toggle\Write\Feature;
use Pheature\Core\Toggle\Write\FeatureId;
use Pheature\Core\Toggle\Write\FeatureRepository;
use DateTimeImmutable;

use function json_encode;

/**
 * @psalm-import-type WriteStrategy from \Pheature\Core\Toggle\Write\Strategy
 * @psalm-import-type EloquentFeature from \Pheature\Community\Laravel\Read\EloquentFeatureFactory
 */
final class EloquentFeatureRepository implements FeatureRepository
{
    private const TABLE = 'pheature_toggles';

    public function save(Feature $feature): void
    {
        $now = new DateTimeImmutable();

        if (null === $this->findFeature($feature->id())) {
            DB::table('pheature_toggles')
                ->insert([
                    'feature_id' => $feature->id(),
                    'name' => $feature->id(),
                    'enabled' => (int)$feature->isEnabled(),
                    'strategies' => json_encode($feature->strategies(), JSON_THROW_ON_ERROR),
                    'created_at' => $now->format('Y-m-d H:i:s'),
                ]);

            return;
        }
        DB::table('pheature_toggles')
            ->where('feature_id', $feature->id())
            ->update([
                'enabled' => $feature->isEnabled(),
                'strategies' => json_encode($feature->strategies(), JSON_THROW_ON_ERROR),
                'updated_at' => $now->format('Y-m-d H:i:s'),
            ]);
    }

    public function get(FeatureId $featureId): Feature
    {
        $featureData = $this->findFeature($featureId->value());
        if (null === $featureData) {
            throw new InvalidArgumentException(sprintf('Not feature found for given id %s', $featureId->value()));
        }

        return EloquentFeatureFactory::createFromEloquentRepresentation($featureData);
    }

    public function remove(Feature $feature): void
    {
        $sql = <<<SQL
            DELETE FROM pheature_toggles WHERE feature_id = :feature_id
        SQL;

        DB::delete(
            $sql,
            [
                'feature_id' => $feature->id()
            ]
        );
    }

    /**
     * @return EloquentFeature|null
     */
    private function findFeature(string $id): ?array
    {
        $table = self::TABLE;
        $sql = <<<SQL
            SELECT * FROM $table WHERE feature_id = :feature_id
        SQL;

        /** @var object[]|false $result */
        $result = DB::select($sql, ['feature_id' => $id]);

        /** @var EloquentFeature|null $feature */
        $feature = !empty($result[0]) ? (array)$result[0] : null;

        return $feature;
    }
}
