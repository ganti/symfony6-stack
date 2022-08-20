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
use function App\Controller\Admin\t;

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
            ->setEntityLabelInPlural(t('admin.crud.logs.titles.index_page'))
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
            yield TextField::new('level', t('admin.crud.logs.label.level'));
            yield TextField::new('context', t('admin.crud.logs.label.context'));
            yield TextField::new('subcontext', t('admin.crud.logs.label.subcontext'));
            yield TextareaField::new('message', t('admin.crud.logs.label.message'));
            yield DateTimeField::new('createdAt', t('admin.crud.generic.created_at'));
        } elseif (Crud::PAGE_DETAIL=== $pageName) {
            yield FormField::addPanel('General', t('admin.crud.logs.titles.general'));
            //yield IdField::new('id');
            yield TextField::new('level', t('admin.crud.logs.label.level'));
            yield TextField::new('context', t('admin.crud.logs.label.context'));
            yield TextareaField::new('message', t('admin.crud.logs.label.message'))->setCssClass('field-text-code');
            yield DateTimeField::new('createdAt', t('admin.crud.logs.titles.general'))->setFormTypeOption('disabled', 'disabled');

            yield FormField::addPanel(t('admin.crud.logs.titles.request_information'));
            yield TextField::new('requestMethod',  t('admin.crud.logs.label.request_method'));
            yield TextField::new('requestPath',  t('admin.crud.logs.label.request_path'));
            yield TextField::new('clientIP',  t('admin.crud.logs.label.client_ip'));
            yield AssociationField::new('user', t('admin.crud.logs.label.user'));
            yield TextField::new('clientLocale',  t('admin.crud.logs.label.client_locale'));
        }
        return $this;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('level', t('admin.crud.logs.label.level'))
            ->add('context', t('admin.crud.logs.label.context'))
            ->add('message', t('admin.crud.logs.label.message'))
            ->add('user', t('admin.crud.logs.label.user'))
            ->add('createdAt', t('admin.crud.generic.created_at'))
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
