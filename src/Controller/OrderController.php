<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order\Order;
use App\Entity\Order\Sav;
use App\Entity\User\User;
use App\Form\Order\SavType;
use App\Repository\Order\OrderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @IsGranted("ROLE_SHOP")
 * @Route("/commandes")
 */
class OrderController extends AbstractController
{
    /**
     * @param OrderRepository<Order> $orderRepository
     * @Route("/", name="order_index")
     */
    public function index(OrderRepository $orderRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render("ui/order/index.html.twig", [
            "orders" => $orderRepository->findBy(["user" => $user], ["createdAt" => "desc"])
        ]);
    }

    /**
     * @Route("/upload", name="order_upload")
     */
    public function upload(
        Request $request,
        SluggerInterface $slugger,
        string $publicDir,
        string $uploadDir
    ): JsonResponse {
        /** @var UploadedFile $file */
        $file = $request->files->get("file");
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
        $file->move(sprintf("%s/%s", $publicDir, $uploadDir), $newFilename);
        return $this->json([
            "file" => sprintf("%s/%s", $uploadDir, $newFilename),
            "name" => $originalFilename
        ]);
    }

    /**
     * @Route("/{id}/detail", name="order_detail")
     */
    public function detail(Order $order): Response
    {
        return $this->render("ui/order/detail.html.twig", [
            "order" => $order
        ]);
    }

    /**
     * @Route("/{id}/declencher-sav", name="order_trigger_sav")
     */
    public function triggerSAV(Order $order, Request $request, MailerInterface $mailer, string $publicDir): Response
    {
        $sav = new Sav();

        $form = $this->createForm(SavType::class, $sav, ["order" => $order])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $email = (new TemplatedEmail())
                ->from(new Address("contact@keyprivilege.fr", "Key Privilege"))
                ->to(new Address("sav@keyprivilege.fr", "Key Privilege"))
                ->replyTo(new Address($user->getEmail(), $user->getFullName()))
                ->htmlTemplate("emails/sav.html.twig")
                ->context(["sav" => $sav, "user" => $user]);

            foreach ($sav->attachments as $attachment) {
                $email->attachFromPath(sprintf("%s/%s", $publicDir, $attachment));
            }

            $mailer->send($email);

            $this->addFlash(
                "success",
                "Votre demande de SAV a bien été envoyée. Nous vous répondrons dans les plus brefs délais."
            );
            return $this->redirectToRoute("order_index");
        }

        return $this->render("ui/order/trigger_sav.html.twig", [
            "form" => $form->createView(),
            "order" => $order
        ]);
    }
}
