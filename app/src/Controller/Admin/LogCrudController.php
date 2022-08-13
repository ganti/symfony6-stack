<?php

namespace App\Controller\Admin;

use App\Entity\Log;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class LogCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Log::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            //->setEntityLabelInSingular('Log')
            ->setEntityLabelInPlural('Logs')
            ->setPageTitle('index', '%entity_label_plural%')
            ->setDateFormat('full')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(100)
            ->setSearchFields(['type', 'context', 'action'])
            ->setEntityPermission('ROLE_ADMIN')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield IntegerField::new('id')->setCssClass('col-auto');
            yield TextField::new('level');
            yield TextField::new('context', 'Context');
            yield TextField::new('subcontext', 'Subcontext');
            yield TextareaField::new('message');
            yield DateTimeField::new('createdAt');
        } elseif (Crud::PAGE_DETAIL=== $pageName) {
            yield FormField::addPanel('General');
            //yield IdField::new('id');
            yield TextField::new('level');
            yield TextField::new('context', 'Context');
            yield TextareaField::new('message')->setCssClass('field-text-code');
            yield DateTimeField::new('createdAt')->setFormTypeOption('disabled', 'disabled');

            yield FormField::addPanel('Request Information');
            yield TextField::new('requestMethod', 'Request Method');
            yield TextField::new('requestPath', 'Request Path');
            yield TextField::new('clientIP', 'Client IP');
            yield AssociationField::new('user', 'User');
            yield TextField::new('clientLocale', 'Locale');
        }
        return $this;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('level', 'Level')
            ->add('context', 'Context')
            ->add('message', 'Message')
            ->add('user', 'User')
            ->add('createdAt', 'created at')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('new')
            ->disable('edit')
            ->disable('delete')
            ->add(Crud::PAGE_INDEX, Action::DETAIL)

            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
        ;
    }
}
