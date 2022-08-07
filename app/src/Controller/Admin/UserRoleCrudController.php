<?php

namespace App\Controller\Admin;

use App\Entity\UserRole;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserRoleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserRole::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('User Roles')
            ->setPageTitle('index', '%entity_label_plural%')
            ->setPageTitle('new', 'New Role')

            ->setDateFormat('full')
            ->setDefaultSort(['id' => 'ASC'])
            ->setSearchFields(['role', 'description'])
            ->setEntityPermission('ROLE_ADMIN')
        ;
    }

    public function configureFields(string $pageName): iterable
    {

        if (Crud::PAGE_INDEX === $pageName) {

            yield TextField::new('role', 'Role');
            yield TextField::new('name', 'Name');
            yield TextField::new('description', 'Description');
            yield ArrayField::new('ParentRoleRecursive', 'Parent Roles');
            yield BooleanField::new('active', 'is active')->renderAsSwitch(false);
            yield DateTimeField::new('createdAt');  

        } else {
            yield FormField::addPanel('Role')->setIcon('fa fa-user-tag');
            if (Crud::PAGE_NEW === $pageName) {
                yield TextField::new('role', 'Role');
            } elseif (Crud::PAGE_EDIT === $pageName) {
                yield TextField::new('role', 'Role')->setFormTypeOption('disabled', 'disabled');
            }

            yield TextField::new('name', 'Name');
            yield TextField::new('description', 'Description');
            yield AssociationField::new('parentRole', 'Parent Role');
            yield BooleanField::new('active', 'is active');
            yield BooleanField::new('systemrole', 'is systemrole')->setFormTypeOption('disabled','disabled');
            yield FormField::addPanel('Timestamps')->setIcon('fa fa-clock');
            
            yield DateTimeField::new('createdAt', 'created')->setColumns('col-4')->setFormTypeOption('disabled', 'disabled');
            yield DateTimeField::new('updatedAt', 'updated')->setColumns('col-4')->setFormTypeOption('disabled', 'disabled');
            yield DateTimeField::new('deletedAt', 'deleted')->setColumns('col-4');

        }
        return $this;
    }
    
    public function configureActions(Actions $actions): Actions
    {

        // hide delete action if a UserRole is a Systemrole
        $delete_action = parent::configureActions($actions)->getAsDto(Crud::PAGE_INDEX)->getAction(Crud::PAGE_INDEX, Action::DELETE);
        if (!is_null($delete_action)) {
            $delete_action->setDisplayCallable(function (UserRole $userrole) {
                return $userrole->isSystemrole() === False;
            });   
        }

        return $actions
        ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
        ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
        ->Add(Crud::PAGE_NEW, Action::DELETE)
        ;
    }
}
