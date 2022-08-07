<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{


    public function __construct(AdminContextProvider $adminContextProvider, Security $security,  EntityManagerInterface $entityManager)
    {
        $this->adminContextProvider = $adminContextProvider;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }


    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User ')
            ->setEntityLabelInPlural('Users')
            ->setPageTitle('index', '%entity_label_plural%')
            //->setPageTitle('edit', 'Edit %entity_label_singular%: %email    %')
            ->setPageTitle('edit', fn (User $user) => sprintf('Edit %s', $user->getUsername()))
            ->setSearchFields(['username', 'email'])
        ;
    }

    /*
     * Checks if logged in user ist the same as in the CRUD request
     */ 
    private function getIsLoggedInUserEditingUserCrud(): bool{
        $requestUserId = $this->adminContextProvider->getContext()->getRequest()->query->get('entityId');
        $loggedInUserId = $this->security->getUser()->getId();
        return ($requestUserId == $loggedInUserId AND $requestUserId != null);
    }

    public function configureFields(string $pageName): iterable
    {

        if (Crud::PAGE_INDEX === $pageName) {
            if ($this->isGranted('ROLE_ADMIN')) {
                yield IntegerField::new('id');
                yield TextField::new('username');
                yield TextField::new('fullName');
                yield TextField::new('email');
                yield ChoiceField::new('roles')
                    ->setChoices(array_combine($this->getUserRolesField(), $this->getUserRolesField()))
                    ->renderAsBadges();
                yield BooleanField::new('isActive')->renderAsSwitch(false);
                yield DateTimeField::new('createdAt');
            }
        } else {
            
            if ($this->getIsLoggedInUserEditingUserCrud() OR $this->isGranted('ROLE_ADMIN')) {
                
                yield FormField::addPanel('Account Information')
                    ->setIcon('far fa-address-card')
                    ->setCssClass('col-sm-8');

                if ($this->isGranted('ROLE_ADMIN')) {
                    yield TextField::new('username', 'Username');
                    yield TextField::new('email', 'eMail');
                }else{
                    //User Profile
                    yield TextField::new('username', 'Username')->setFormTypeOption('disabled', 'disabled');
                    yield TextField::new('email', 'eMail')->setFormTypeOption('disabled', 'disabled');;
                }

                yield TextField::new('firstname', 'Firstname');
                yield TextField::new('lastname', 'Las   tname');


                yield FormField::addPanel('Change password')
                    ->setIcon('fa fa-key')
                    ->setCssClass('col');
                yield Field::new('plainPassword', 'New password')
                                            ->onlyOnForms()
                                            ->setFormType(RepeatedType::class)
                                            ->setFormTypeOption('empty_data', '')
                                            ->setFormTypeOptions([
                                                'type' => PasswordType::class,
                                                'first_options' => ['label' => 'New password'],
                                                'second_options' => ['label' => 'Repeat password'],
                                            ]);
            }

            if ($this->isGranted('ROLE_ADMIN')) {
                yield FormField::addPanel('Admin Settings')
                ->setIcon('fas fa-users-cog')
                ->setCssClass('');
                yield ChoiceField::new('roles', 'Role')
                                            ->allowMultipleChoices()
                                            ->autocomplete()
                                            ->setChoices( $this->getUserRolesField());
                
                yield BooleanField::new('is_active', 'is active');
                yield BooleanField::new('is_verified', 'is verified')->setFormTypeOption('disabled', 'disabled');
                
                yield TextField::new('pid')->setFormTypeOption('disabled', 'disabled');

                yield DateTimeField::new('createdAt', 'created')->setColumns('col-4')->setFormTypeOption('disabled', 'disabled');
                yield DateTimeField::new('updatedAt', 'updated')->setColumns('col-4')->setFormTypeOption('disabled', 'disabled');
                yield DateTimeField::new('deletedAt', 'deleted')->setColumns('col-4');
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
            
            if (isset($passwords['first']) AND empty(trim($passwords['first'])) == False) {
                
                if (trim($passwords['first']) == trim($passwords['second'])) {
                    $plainPassword = trim($passwords['first']);
                }else{
                    $this->addFlash('warning', 'Passwords dont match');
                }
                if (!empty($plainPassword)) {
                    $encodedPassword = $this->passwordEncoder->encodePassword($this->getUser(), $plainPassword);
                    $entityInstance->setPassword($encodedPassword);
                }else{
                    $entityInstance->eraseCredentials();
                }
            }else{
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
            //->disable('edit')
            ->disable('delete')
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
        ->add('username', 'Username')
        ->add('email', 'email')
        ->add('roles', 'Userroles')
        ->add('isActive', 'is Active');
    }
    
    private function getUserRolesField(): Array{
        $return = [];
        $roles = $this->entityManager->getRepository(UserRole::class)->findAllActive();
        foreach ($roles as $r){
            $return[$r->getName()] = $r->getRole(); 
        }
        return $return;
    }
}
