<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Order\Line;
use App\Entity\Order\Order;
use App\Entity\Order\Sav;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;

class SavType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("line", EntityType::class, [
                "label" => "Produit concerné :",
                "class" => Line::class,
                "choice_label" => fn (Line $line) => sprintf(
                    "%s - %s - %s",
                    $line->getOrder()->getCreatedAt()->format("d/m/Y"),
                    $line->getProduct()->getName(),
                    $line->getProduct()->getBrand()->getName()
                ),
                "query_builder" => fn (EntityRepository $repository) => $repository->createQueryBuilder("l")
                    ->where("l.order = :order")
                    ->setParameter("order", $options["order"])
            ])
            ->add("attachments", DropzoneType::class)
            ->add("description", TextareaType::class, [
                "label" => "Descriptif précis de la panne :"
            ])
            ->add("comment", TextareaType::class, [
                "required" => false,
                "label" => "Commentaires :"
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired("order");
        $resolver->setAllowedTypes("order", Order::class);
        $resolver->setDefault("data_class", Sav::class);
    }
}
