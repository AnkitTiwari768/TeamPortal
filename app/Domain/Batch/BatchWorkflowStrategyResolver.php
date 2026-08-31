<?php 

declare(strict_types=1);

namespace App\Domain\Batch;

class BatchWorkflowStrategyResolver
{
    public function resolve(): BatchWorkflowStrategy
    {
        if (hasRole('ondc-admin')) {
            return app(OndcWorkflowStrategy::class);
        }

        if (hasRole('nsic')) {
            return app(NsicWorkflowStrategy::class);
        }

        if (hasRole('nsic-finance')) {
            return app(NsicFinanceWorkflowStrategy::class);
        }

        if (hasRole('ca')) {
            return app(CaWorkflowStrategy::class);
        }

        throw new \Exception("No workflow strategy found");
    }
}