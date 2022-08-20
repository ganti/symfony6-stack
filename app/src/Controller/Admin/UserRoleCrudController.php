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
use function App\Controller\Admin\t;

class UserRoleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserRole::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural(t('admin.crud.user_roles.titles.index_page'))
            ->setPageTitle('index', '%entity_label_plural%')
            ->setPageTitle('new', t('admin.crud.user_roles.label.new_role'))

            ->setDateFormat('full')
            ->setDefaultSort(['id' => 'ASC'])
            ->setSearchFields(['role', 'description'])
            ->setEntityPermission('ROLE_ADMIN')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('role', t('admin.crud.user_roles.label.role'));
            yield TextField::new('name', t('admin.crud.user_roles.label.name'));
            yield TextField::new('description', t('admin.crud.user_roles.label.description'));
            yield ArrayField::new('ParentRoleRecursive', t('admin.crud.user_roles.label.parent_roles'));
            yield BooleanField::new('active', t('admin.crud.generic.is_active'))->renderAsSwitch(false);
            yield DateTimeField::new('createdAt', t('admin.crud.generic.created_at'));
        } else {
            yield FormField::addPanel(t('admin.crud.user_roles.label.role'))->setIcon('fa fa-user-tag');
            if (Crud::PAGE_NEW === $pageName) {
                yield TextField::new('role', t('admin.crud.user_roles.label.role'));
            } elseif (Crud::PAGE_EDIT === $pageName) {
                yield TextField::new('role', t('admin.crud.user_roles.label.role'))->setFormTypeOption('disabled', 'disabled');
            }

            yield TextField::new('name', t('admin.crud.user_roles.label.name'));
            yield TextField::new('description', t('admin.crud.user_roles.label.description'));
            yield AssociationField::new('parentRole', t('admin.crud.user_roles.label.parent_roles'));
            yield BooleanField::new('active', t('admin.crud.generic.is_active'));
            yield BooleanField::new('systemrole', 'is systemrole')->setFormTypeOption('disabled', 'disabled');
            yield FormField::addPanel('Timestamps')->setIcon('fa fa-clock');

            yield DateTimeField::new('createdAt', t('admin.crud.generic.created_at'))->setColumns('col-4')->setFormTypeOption('disabled', 'disabled');
            yield DateTimeField::new('updatedAt', t('admin.crud.generic.updated_at'))->setColumns('col-4')->setFormTypeOption('disabled', 'disabled');

        }
        return $this;
    }

    public function configureActions(Actions $actions): Actions
    {

        // hide delete action if a UserRole is a Systemrole
        $delete_action = parent::configureActions($actions)->getAsDto(Crud::PAGE_INDEX)->getAction(Crud::PAGE_INDEX, Action::DELETE);
        if (!is_null($delete_action)) {
            $delete_action->setDisplayCallable(function (UserRole $userrole) {
                return $userrole->isSystemrole() === false;
            });
        }

        return $actions
        ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
        ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
        ->Add(Crud::PAGE_NEW, Action::DELETE)
        ;
    }
}
