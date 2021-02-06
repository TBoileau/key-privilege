<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\Rules;
use App\Entity\User;
use App\Repository\RulesRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestListener
{
    public TokenStorageInterface $tokenStorage;

    /**
     * @var RulesRepository<Rules>
     */
    private RulesRepository $rulesRepository;

    private UrlGeneratorInterface $urlGenerator;

    /**
     * @param RulesRepository<Rules> $rulesRepository
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        RulesRepository $rulesRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->rulesRepository = $rulesRepository;
        $this->urlGenerator = $urlGenerator;
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$this->tokenStorage->getToken()?->getUser() instanceof User) {
            return;
        }

        if ($event->getRequest()->attributes->get("_route") === "security_rules") {
            return;
        }

        /** @var User $user */
        $user = $this->tokenStorage->getToken()->getUser();

        $rules = $this->rulesRepository->getLastPublishedRules();

        if ($user->hasAcceptedRules($rules)) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->urlGenerator->generate("security_rules")));
    }
}
