<?php

namespace App\Controller\Admin;

use App\Admin\Field\RoleField;
use App\Admin\Field\RulesAgreementField;
use App\Entity\User\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setDefaultSort(['firstName' => 'ASC', 'lastName' => 'ASC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $logAs = Action::new("logAs", 'Se connecter en tant que')
            ->displayAsLink()
            ->linkToRoute("home", fn (User $user) => ["_switch_user" => $user->getEmail()]);
        $reset = Action::new("reset", 'Réinitialiser')
            ->displayAsLink()
            ->linkToRoute("admin_user_reset", fn (User $user) => ["user" => $user->getId()]);

        return $actions
            ->add(Crud::PAGE_INDEX, $logAs)
            ->add(Crud::PAGE_DETAIL, $logAs)
            ->add(Crud::PAGE_INDEX, $reset)
            ->add(Crud::PAGE_DETAIL, $reset)
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Utilisateur');
        yield BooleanField::new('suspended', 'Suspendre');
        yield RoleField::new('role', 'Rôle')
            ->hideOnForm();
        yield TextField::new('firstName', 'Prénom')
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ]);
        yield TextField::new('lastName', 'Nom')
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ]);
        yield EmailField::new('email', 'Adresse email')
            ->setFormTypeOption("constraints", [
                new NotBlank(),
                new Email()
            ]);
        yield TextField::new('plainPassword', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->setFormTypeOption("constraints", [
                new NotBlank()
            ])
            ->onlyWhenCreating();
        yield DateTimeField::new('registeredAt', 'Inscription')
            ->hideOnForm();
        yield DateTimeField::new('lastLogin', 'Dernière connexion')
            ->hideOnForm();
        yield RulesAgreementField::new('lastRulesAgreement', 'Règlement')
            ->hideOnForm();
        yield AssociationField::new("rulesAgreements", "Règlement")
            ->setTemplatePath("admin/field/user_rules_agreements.html.twig")
            ->onlyOnDetail();
    }

    /**
     * @Route("/admin/users/{id}/reset", name="admin_user_reset")
     */
    public function reset(
        User $user,
        UserPasswordEncoderInterface $customerPasswordEncoder,
        MailerInterface $mailer,
        AdminUrlGenerator $adminUrlGenerator
    ): RedirectResponse {
        $password = md5(random_bytes(8));
        $user->setPassword($customerPasswordEncoder->encodePassword($user, $password));
        $this->getDoctrine()->getManager()->flush();
        $mailer->send(
            (new TemplatedEmail())
                ->from(new Address("contact@keyprivilege.fr", "Key Privilege"))
                ->to(new Address($user->getEmail(), $user->getFullName()))
                ->htmlTemplate("emails/reset.html.twig")
                ->context(["customer" => $user, "password" => $password])
        );
        $this->addFlash(
            "success",
            sprintf(
                "Un nouveau mot de passe a été généré et envoyé à %s.",
                $user->getFullName()
            )
        );

        return $this->redirect(
            $adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::INDEX)->generateUrl()
        );
    }
}
