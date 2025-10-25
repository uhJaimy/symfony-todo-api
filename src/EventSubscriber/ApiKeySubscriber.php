<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiKeySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly string $apiKey,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        if ('OPTIONS' === $request->getMethod()) {
            return;
        }

        $providedApiKey = $request->headers->get('X-API-KEY', '');

        if ('' === $providedApiKey || !hash_equals($this->apiKey, $providedApiKey)) {
            $response = new JsonResponse(
                ['error' => 'Invalid or missing API key.'],
                JsonResponse::HTTP_UNAUTHORIZED,
                ['WWW-Authenticate' => 'API key required']
            );
            $event->setResponse($response);
        }
    }
}
