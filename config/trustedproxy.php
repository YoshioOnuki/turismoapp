<?php

$proxies = trim((string) env('TRUSTED_PROXIES', ''));

return [
    'proxies' => match ($proxies) {
        '' => null,
        '*' => '*',
        default => array_values(array_filter(array_map(trim(...), explode(',', $proxies)))),
    },
];
