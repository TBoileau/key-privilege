<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/faq-reglement", name="faq")
 */
class FaqController extends AbstractController
{
    /**
     * @param QuestionRepository<Question> $questionRepository
     */
    public function __invoke(QuestionRepository $questionRepository): Response
    {
        return $this->render("ui/faq.html.twig", [
            "questions" => $questionRepository->findAll()
        ]);
    }
}
