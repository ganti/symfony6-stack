<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimezoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use function App\Controller\Admin\t;

class UserCrudController extends AbstractCrudController
{
    public function __construct(AdminContextProvider $adminContextProvider, Security $security, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->adminContextProvider = $adminContextProvider;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }


    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural(t('admin.crud.user.titles.index_page'))
            ->setPageTitle('index', '%entity_label_plural%')
            //->setPageTitle('edit', 'Edit %entity_label_singular%: %email    %')
            ->setPageTitle('edit', fn (User $user) => sprintf('Edit %s', $user->getUsername()))
            ->setSearchFields(['username', 'email'])
        ;
    }

    /*
     * Checks if logged in user ist the same as in the CRUD request
     */
    private function getIsLoggedInUserEditingUserCrud(): bool
    {
        $requestUserId = $this->adminContextProvider->getContext()->getRequest()->query->get('entityId');
        $loggedInUserId = $this->security->getUser()->getId();
        return ($requestUserId == $loggedInUserId and $requestUserId != null);
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            if ($this->isGranted('ROLE_ADMIN')) {
                yield IntegerField::new('id');
                yield TextField::new('username', t('admin.crud.user.label.username'));
                yield TextField::new('email', t('admin.crud.user.label.email'));
                yield ChoiceField::new('roles', t('admin.crud.user.label.user_roles'))
                    ->setChoices(array_combine($this->getUserRolesField(), $this->getUserRolesField()))
                    ->renderAsBadges();
                yield BooleanField::new('isActive', t('admin.crud.generic.is_active'))->renderAsSwitch(false);
                yield DateTimeField::new('createdAt', t('admin.crud.generic.created_at'));
            }
        } else {
            if ($this->getIsLoggedInUserEditingUserCrud() or $this->isGranted('ROLE_ADMIN')) {
                yield FormField::addPanel(t('admin.crud.user.titles.account_information'))
                    ->setIcon('far fa-address-card')
                    ->setCssClass('col-sm-12');

                yield TextField::new('username', t('admin.crud.user.label.username'))->setColumns('col-6');
                yield TextField::new('email', t('admin.crud.user.label.email'))->setColumns('col-6');

                yield TextField::new('firstname', t('admin.crud.user.label.firstname'))->setColumns('col-6');
                yield TextField::new('lastname', t('admin.crud.user.label.lastname'))->setColumns('col-6');


                yield FormField::addPanel(t('admin.crud.user.titles.change_password'))
                    ->setIcon('fa fa-solid fa-key')
                    ->setCssClass('col-3');
                yield Field::new('plainPassword', t('admin.crud.user.label.new_password'))
                                            ->onlyOnForms()
                                            ->setFormType(RepeatedType::class)
                                            ->setFormTypeOption('empty_data', '')
                                            ->setFormTypeOptions([
                                                'type' => PasswordType::class,
                                                'first_options' => ['label' => t('admin.crud.user.label.new_password')],
                                                'second_options' => ['label' => t('admin.crud.user.label.new_password_repeat')],
                                            ]);
            }

            yield FormField::addPanel(t('admin.crud.user.titles.user_settings'))
                ->setIcon('fa fa-solid fa-screwdriver-wrench')
                ->setCssClass('col');
            yield TimezoneField::new('timezone', t('admin.crud.user.label.time_zone'))
                ->setColumns('col-6');
            yield CountryField::new('country', t('admin.crud.user.label.country'))
                ->setColumns('col-6');
            yield ChoiceField::new('date_format', t('admin.crud.user.label.date_format'))
                ->setColumns('col-6')
                ->setChoices([
                    '2022-06-23 (yyyy-MM-dd)' => 'yyyy-MM-dd',
                    '6/23/2022 (M/d/yyyy)' => 'M/d/yyyy',
                    '23.06.2022 (dd.MM.yyyy)' => 'dd.MM.yyyy',
                    '23/06/2022 (dd/MM/yyyy)' => 'dd/MM/yyyy',
                ]);

            yield ChoiceField::new('time_format', t('admin.crud.user.label.time_format'))
                ->setColumns('col-6')
                ->setChoices([
                    '15:23:42 (HH:mm:ss)' => 'HH:mm:ss',
                    '15:23 (HH:mm)' => 'HH:mm',
                    '3:23:42 PM (h:mm:ss a) '=> 'hh:mm:ss a',
                    '3:23 PM (h:mm a) '=> 'hh:mm:ss a',
                ]);

            if ($this->isGranted('ROLE_ADMIN')) {
                yield FormField::addPanel(t('admin.crud.user.titles.admin_settings'))
                ->setIcon('fas fa-users-cog')
                ->setCssClass('');
                yield ChoiceField::new('roles', t('admin.crud.user.label.user_roles'))
                                            ->allowMultipleChoices()
                                            ->autocomplete()
                                            ->setChoices($this->getUserRolesField());

                yield BooleanField::new('is_active', t('admin.crud.generic.is_active'));
                yield BooleanField::new('is_verified', t('admin.crud.user.label.mail_verified'))->setFormTypeOption('disabled', 'disabled');

                yield TextField::new('pid', 'PID')->setFormTypeOption('disabled', 'disabled');

                yield DateTimeField::new('createdAt', t('admin.crud.generic.created_at'))
                    ->setColumns('col-4')
                    ->setFormTypeOption('disabled', 'disabled');
                yield DateTimeField::new('updatedAt', t('admin.crud.generic.updated_at'))
                    ->setColumns('col-4')
                    ->setFormTypeOption('disabled', 'disabled');
            }

            yield FormField::addPanel('Logs')->setIcon('fas fa-log');


            yield AssociationField::new('logs');
            //yield CollectionField::new('logs')->setTemplatePath('bundles/EasyAdminBundle/fields/array_readonly.html.twig');
        }
    }


    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {

        // set new password with encoder interface
        if (method_exists($entityInstance, 'setPassword')) {
            $passwords = $this->adminContextProvider->getContext()->getRequest()->request->all()['User']['plainPassword'];

            if (isset($passwords['first']) and empty(trim($passwords['first'])) == false) {
                if (trim($passwords['first']) == trim($passwords['second'])) {
                    $plainPassword = trim($passwords['first']);
                } else {
                    $this->addFlash('warning', t('admin.crud.user.messages.passwort_not_match'));
                }
                if (!empty($plainPassword)) {
                    $encodedPassword = $this->passwordHasher->hashPassword(
                        $this->getUser(),
                        $plainPassword
                    );
                    $entityInstance->setPassword($encodedPassword);
                } else {
                    $entityInstance->eraseCredentials();
                }
            } else {
                $entityInstance->eraseCredentials();
            }
        }

        //UserRoles
        $roles = $this->adminContextProvider->getContext()->getRequest()->request->all()['User']['roles'];
        $rolesToSave = $this->entityManager->getRepository(UserRole::class)->getAllRolesToSave($roles);
        $entityInstance->setRoles($rolesToSave);

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('new')
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
        ->add('username', t('admin.crud.user.label.username'))
        ->add('email', t('admin.crud.user.label.email'))
        ->add('roles', t('admin.crud.user.label.user_roles'))
        ->add('isActive', t('admin.crud.generic.is_active'));
    }

    private function getUserRolesField(): array
    {
        $return = [];
        $roles = $this->entityManager->getRepository(UserRole::class)->findAllActive();
        foreach ($roles as $r) {
            $return[$r->getName()] = $r->getRole();
        }
        return $return;
    }
}
