<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Entity\User\User;
use App\Entity\Rules;
use App\Repository\RulesRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    private EntityManagerInterface $entityManager;

    /**
     * @param RulesRepository<Rules> $rulesRepository
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        RulesRepository $rulesRepository,
        UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $entityManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->rulesRepository = $rulesRepository;
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
    }

    public function onRequest(RequestEvent $event): void
    {
        $this->entityManager->getFilters()->enable("softdeleteable");

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
