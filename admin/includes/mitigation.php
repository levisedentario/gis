<?php

if (!function_exists('fetchMitigationMeasures')) {
    function fetchMitigationMeasures(mysqli $conn): array
    {
        $measures = [];
        $result = $conn->query("SELECT dominant_factor, recommended_mitigation FROM mitigation_measures ORDER BY id ASC");

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $measures[] = $row;
            }

            $result->free();
        }

        return $measures;
    }
}

if (!function_exists('mitigationTextContainsAny')) {
    function mitigationTextContainsAny(string $value, array $needles): bool
    {
        $haystack = strtolower(trim($value));

        if ($haystack === '') {
            return false;
        }

        foreach ($needles as $needle) {
            if (strpos($haystack, strtolower($needle)) !== false) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('mitigationExtractFirstNumber')) {
    function mitigationExtractFirstNumber($value): ?float
    {
        $text = trim((string) $value);

        if ($text === '' || !preg_match('/-?\d+(?:\.\d+)?/', $text, $matches)) {
            return null;
        }

        return (float) $matches[0];
    }
}

if (!function_exists('mitigationExtractMinimumNumber')) {
    function mitigationExtractMinimumNumber($value): ?float
    {
        $text = trim((string) $value);

        if ($text === '' || !preg_match_all('/-?\d+(?:\.\d+)?/', $text, $matches) || empty($matches[0])) {
            return null;
        }

        $numbers = array_map('floatval', $matches[0]);

        return min($numbers);
    }
}

if (!function_exists('mitigationFactorMatchesInputs')) {
    function mitigationFactorMatchesInputs(string $factor, array $inputs): bool
    {
        switch ($factor) {
            case 'Low Hydraulic Capacity':
                $hydraulicCapacity = mitigationExtractFirstNumber($inputs['hydraulic_capacity'] ?? '');
                return $hydraulicCapacity !== null && $hydraulicCapacity <= 1.5;

            case 'Poor Structural Condition':
                return mitigationTextContainsAny((string) ($inputs['structural_condition'] ?? ''), ['poor', 'damaged', 'bad', 'critical', 'failing', 'defective']);

            case 'Small Drainage Dimensions':
                $dimensions = mitigationExtractMinimumNumber($inputs['dimensions'] ?? '');
                return $dimensions !== null && $dimensions < 1.0;

            case 'Inappropriate Drainage Structure Type':
                $structureType = strtolower(trim((string) ($inputs['structure_type'] ?? '')));

                if ($structureType === '') {
                    return false;
                }

                return !mitigationTextContainsAny($structureType, ['culvert', 'ditch', 'drain', 'canal', 'pipe', 'box', 'line']);

            case 'Inefficient Drainage Shape':
                return mitigationTextContainsAny((string) ($inputs['shape'] ?? ''), ['irregular', 'oval', 'round', 'circular', 'inefficient']);

            case 'Old Built Span (age)':
                $builtSpan = mitigationExtractFirstNumber($inputs['built_span'] ?? '');
                return $builtSpan !== null && $builtSpan <= 0.5;

            case 'Very Low Elevation':
                $elevation = mitigationExtractFirstNumber($inputs['elevation'] ?? '');
                return $elevation !== null && $elevation <= 2.5;

            case 'Low Slope':
                $slope = mitigationExtractFirstNumber($inputs['slope'] ?? '');
                return $slope !== null && abs($slope) <= 0.2;

            case 'High Rainfall Amount':
                $rainProbability = mitigationExtractFirstNumber($inputs['precipitation_probability'] ?? '');
                return $rainProbability !== null && $rainProbability >= 70;

            case 'High Rainfall Intensity':
                $rainIntensity = mitigationExtractFirstNumber($inputs['rain_intensity'] ?? '');
                return $rainIntensity !== null && $rainIntensity >= 15;

            case 'Low Infiltration Capacity':
                $infiltrationCapacity = trim((string) ($inputs['infiltration_capacity'] ?? ''));

                if ($infiltrationCapacity === '') {
                    return false;
                }

                $infiltrationNumber = mitigationExtractFirstNumber($infiltrationCapacity);

                return mitigationTextContainsAny($infiltrationCapacity, ['low', 'poor', 'slow', 'very low']) || ($infiltrationNumber !== null && $infiltrationNumber <= 2.0);

            case 'High Building Density':
                $buildingDensity = mitigationExtractFirstNumber($inputs['building_density'] ?? '');
                return $buildingDensity !== null && $buildingDensity >= 50;

            case 'High Impervious Surface Percentage':
                $impervious = trim((string) ($inputs['impervious'] ?? ''));

                if ($impervious === '') {
                    return false;
                }

                $imperviousNumber = mitigationExtractFirstNumber($impervious);

                return mitigationTextContainsAny($impervious, ['high']) || ($imperviousNumber !== null && $imperviousNumber >= 50);

            case 'High Garbage Accumulation':
                return trim((string) ($inputs['garbage_accommodation'] ?? '')) !== '';

            case 'High Drainage Obstruction':
                $drainageObstruction = mitigationExtractFirstNumber($inputs['drainage_obstruction'] ?? '');
                return $drainageObstruction !== null && $drainageObstruction >= 2.0;

            case 'Low Vegetation Cover':
                $vegetationCover = mitigationExtractFirstNumber($inputs['vegetation_cover'] ?? '');
                return $vegetationCover !== null && $vegetationCover <= 30;

            default:
                return false;
        }
    }
}

if (!function_exists('buildMitigationRecommendationText')) {
    function buildMitigationRecommendationText(array $inputs, array $mitigationMeasures): string
    {
        $recommendations = [];

        foreach ($mitigationMeasures as $measure) {
            $factor = trim((string) ($measure['dominant_factor'] ?? ''));
            $mitigation = trim((string) ($measure['recommended_mitigation'] ?? ''));

            if ($factor === '' || $mitigation === '') {
                continue;
            }

            if (mitigationFactorMatchesInputs($factor, $inputs)) {
                $recommendations[] = $factor . ': ' . $mitigation;
            }
        }

        return implode("\n\n", array_values(array_unique($recommendations)));
    }
}
