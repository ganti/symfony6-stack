<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use App\Admin\Field\TwoFactorEnableField;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use Symfony\Component\Translation\TranslatableMessage;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimezoneField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        AdminContextProvider $adminContextProvider,
        AdminUrlGenerator $adminUrlGenerator,
        Security $security,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ContainerBagInterface $params,
        UserRepository $userRepository
    )
    {
        $this->adminContextProvider = $adminContextProvider;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->params = $params;
        $this->userRepository = $userRepository;
    }

    public function t(string $message, array $parameters = []): TranslatableMessage
    {
        return new TranslatableMessage($message, $parameters, 'admin');
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityLabelInPlural($this->t('admin.crud.user.titles.index_page'))
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
                yield TextField::new('username', $this->t('admin.crud.user.label.username'));
                yield TextField::new('email', $this->t('admin.crud.user.label.email'));
                yield TextField::new('fullname', $this->t('admin.crud.user.label.fullname'));
                yield ChoiceField::new('roles', $this->t('admin.crud.user.label.user_roles'))
                    ->setChoices(array_combine($this->getUserRolesField(), $this->getUserRolesField()))
                    ->renderAsBadges();

                yield BooleanField::new('isActive', $this->t('admin.crud.generic.is_active'))
                    ->renderAsSwitch(false);

                yield DateTimeField::new('createdAt', $this->t('admin.crud.generic.created_at'));
            }
        } else {
            if ($this->getIsLoggedInUserEditingUserCrud() or $this->isGranted('ROLE_ADMIN')) {

                /*
                 * ===== Tab: Account Information =====
                 */
                yield FormField::addTab($this->t('admin.crud.user.titles.account_information'))
                    ->setIcon('far fa-address-card');

                yield FormField::addPanel($this->t('admin.crud.user.titles.account_information'))
                    ->setIcon('far fa-address-card')
                    ->setCssClass('col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8');

                yield TextField::new('username', $this->t('admin.crud.user.label.username'))
                    ->setColumns('col-12');

                yield TextField::new('email', $this->t('admin.crud.user.label.email'))
                    ->setColumns('col-12');

                yield TextField::new('firstname', $this->t('admin.crud.user.label.firstname'))
                    ->setColumns('col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6');

                yield TextField::new('lastname', $this->t('admin.crud.user.label.lastname'))
                    ->setColumns('col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6');


                yield FormField::addPanel($this->t('admin.crud.user.titles.user_settings'))
                ->setIcon('fa fa-solid fa-screwdriver-wrench')
                ->setCssClass('col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8');


                yield LocaleField::new('locale', $this->t('admin.crud.user.label.locale'))
                    ->includeOnly($this->params->get('app')['admin_locales'])
                    ->setColumns('col-12');

                yield CountryField::new('country', $this->t('admin.crud.user.label.country'))
                        ->setColumns('col-12');

                yield TimezoneField::new('timezone', $this->t('admin.crud.user.label.time_zone'))
                    ->setColumns('col-12');


                yield ChoiceField::new('date_format', $this->t('admin.crud.user.label.date_format'))
                    ->setColumns('col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6')
                    ->setChoices([
                        '2022-06-23 (yyyy-MM-dd)' => 'yyyy-MM-dd',
                        '6/23/2022 (M/d/yyyy)' => 'M/d/yyyy',
                        '23.06.2022 (dd.MM.yyyy)' => 'dd.MM.yyyy',
                        '23/06/2022 (dd/MM/yyyy)' => 'dd/MM/yyyy',
                    ]);

                yield ChoiceField::new('time_format', $this->t('admin.crud.user.label.time_format'))
                    ->setColumns('col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6')
                    ->setChoices([
                        '15:23:42 (HH:mm:ss)' => 'HH:mm:ss',
                        '15:23 (HH:mm)' => 'HH:mm',
                        '3:23:42 PM (h:mm:ss a) '=> 'hh:mm:ss a',
                        '3:23 PM (h:mm a) '=> 'hh:mm:ss a',
                    ]);

                /*
                * ===== Tab: Account Security =====
                */
                yield FormField::addTab('admin.crud.user.titles.security')
                        ->setIcon('far fa-lock');
                yield FormField::addPanel($this->t('admin.crud.user.titles.change_password'))
                    ->setIcon('fa fa-solid fa-key')
                    ->setCssClass('col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8');
                yield Field::new('plainPassword', $this->t('admin.crud.user.label.new_password'))
                    ->onlyOnForms()
                    ->setFormType(RepeatedType::class)
                    ->setFormTypeOption('empty_data', '')
                    ->setFormTypeOptions([
                        'type' => PasswordType::class,
                        'first_options' => ['label' => $this->t('admin.crud.user.label.new_password')],
                        'second_options' => ['label' => $this->t('admin.crud.user.label.new_password_repeat')],
                    ]);


                // 2Factor
                $usrId = $this->adminContextProvider->getContext()->getRequest()->query->get('entityId');
                $twofactorEnabled = $this->userRepository->findOneBy(['id' => $usrId])->isTwoFactorEnabled();
                yield FormField::addPanel()
                    ->setCssClass('col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8')
                    ->setHelp(
                        $this->render('view/core/2fa/enable2fa_crud.html.twig', [
                            'isEnabled' => $twofactorEnabled,
                            'isLoggedInUser' => $this->getIsLoggedInUserEditingUserCrud(),
                            'enableTwoFactorURL' => '/admin?routeName=app_2fa_enable',
                            'disableTwoFactorURL' => '/admin?routeName=app_2fa_disable'
                        ])->getContent()
                    );
                if (!$this->getIsLoggedInUserEditingUserCrud() and $twofactorEnabled and $this->isGranted('ROLE_ADMIN')) {
                    yield BooleanField::new('TwoFactorEnabled', $this->t('admin.crud.user.label.TwoFactorEnabled'));
                }
            }

            /*
             * ===== Tab: Admin =====
             */
            if ($this->isGranted('ROLE_ADMIN')) {
                yield FormField::addTab('Admin')
                    ->setIcon('far fa-users-cog');

                yield FormField::addPanel($this->t('admin.crud.user.titles.admin_settings'))
                    ->setIcon('fas fa-users-cog')
                    ->setCssClass('col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6');

                yield ChoiceField::new('roles', $this->t('admin.crud.user.label.user_roles'))
                    ->setColumns('col-12')
                    ->allowMultipleChoices()
                    ->autocomplete()
                    ->setChoices($this->getUserRolesField());

                yield BooleanField::new('is_active', $this->t('admin.crud.generic.is_active'));
                yield BooleanField::new('is_verified', $this->t('admin.crud.user.label.mail_verified'))
                    ->setFormTypeOption('disabled', 'disabled');

                yield TextField::new('pid', 'PID')
                    ->setColumns('col-12')
                    ->setFormTypeOption('disabled', 'disabled');

                yield DateTimeField::new('createdAt', $this->t('admin.crud.generic.created_at'))
                ->setColumns('col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6')
                    ->setFormTypeOption('disabled', 'disabled');
                yield DateTimeField::new('updatedAt', $this->t('admin.crud.generic.updated_at'))
                    ->setColumns('col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6')
                    ->setFormTypeOption('disabled', 'disabled');
                yield DateTimeField::new('deletedAt', $this->t('admin.crud.generic.deleted_at'))
                    ->setColumns('col-12 col-sm-12 col-md-12 col-lg-6 col-xl-6')
                    ->setFormTypeOption('disabled', 'disabled');
            }


            yield FormField::addPanel('Logs')->setIcon('fas fa-log');

            //yield AssociationField::new('logs');
            //yield ArrayField::new('logs')
            //    ->setFormTypeOption('label', false)
            //    ->setFormTypeOption('allow_delete', false)
            //    ->setFormTypeOption('allow_add', false)
            //    ->setFormTypeOption('disabled', 'disabled');
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
                    $this->addFlash('warning', $this->t('admin.crud.user.messages.passwort_not_match'));
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

    /*
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('new')
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE);
    }*/

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
        ->add('username', $this->t('admin.crud.user.label.username'))
        ->add('email', $this->t('admin.crud.user.label.email'))
        ->add('roles', $this->t('admin.crud.user.label.user_roles'))
        ->add('isActive', $this->t('admin.crud.generic.is_active'));
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
