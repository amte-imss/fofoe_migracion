<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class AbstractSubscriber
{
    const UNKNOWN_ACTION = 'unknown_action';

    protected ?Request $request;

    public function __construct(
        protected readonly LoggerInterface $logger,
        RequestStack $requestStack,
    ) {
        $this->request = $requestStack->getCurrentRequest();
    }

    protected function logDB(string $action = self::UNKNOWN_ACTION, array $context = [], string $type = 'info'): void
    {
        match ($type) {
            'error' => $this->logger->error($action, $context),
            default => $this->logger->info($action, $context),
        };
    }
}
