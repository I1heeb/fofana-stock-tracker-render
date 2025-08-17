<?php

namespace App\Services;

use Sentry\Tracing\SamplingContext;

class SentrySampler
{
    public function __invoke(SamplingContext $context): float
    {
        $transactionData = $context->getTransactionContext()->getData();

        // Always sample error pages
        if (isset($transactionData['url']) && str_contains($transactionData['url'], '/error')) {
            return 1.0;
        }

        // Always sample slow transactions
        if (isset($transactionData['duration']) && $transactionData['duration'] > 1000) {
            return 1.0;
        }

        // Sample order operations more frequently
        if (isset($transactionData['url']) && str_contains($transactionData['url'], '/api/orders')) {
            return 0.5;
        }

        // Sample stock operations more frequently
        if (isset($transactionData['url']) && str_contains($transactionData['url'], '/api/stock')) {
            return 0.5;
        }

        // Default sampling rate for other transactions
        return 0.2;
    }
} 