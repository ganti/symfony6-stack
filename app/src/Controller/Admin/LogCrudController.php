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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\Translation\TranslatableMessage;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class LogCrudController extends AbstractCrudController
{
    public function t(string $message, array $parameters = []): TranslatableMessage
    {
        return new TranslatableMessage($message, $parameters, 'admin');
    }

    public static function getEntityFqcn(): string
    {
        return Log::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            //->setEntityLabelInSingular('Log')
            ->setEntityLabelInPlural($this->t('admin.crud.logs.titles.index_page'))
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
        yield IntegerField::new('id')
            ->setCssClass('col-auto');
        yield ChoiceField::new('level', $this->t('admin.crud.logs.label.level'))
            ->renderAsBadges([
                'ERROR' => 'danger',
                'WARNING' => 'warning',
                'INFO' => 'success',
                'NOTICE' => 'info',
                'DEBUG' => 'info',
                'NO_LEVEL' => 'secondary'
            ])
            ->setChoices(['ERROR'=>'ERROR', 'WARNING'=>'WARNING', 'INFO'=>'INFO', 'NOTICE'=>'NOTICE', 'DEBUG'=>'DEBUG', 'NO_LEVEL'=>'NO_LEVEL']);
        yield TextField::new('context', $this->t('admin.crud.logs.label.context'));
        yield TextField::new('subcontext', $this->t('admin.crud.logs.label.subcontext'));
        yield TextareaField::new('message', $this->t('admin.crud.logs.label.message'));
        yield DateTimeField::new('createdAt', $this->t('admin.crud.generic.created_at'));

        yield FormField::addPanel($this->t('admin.crud.logs.titles.request_information'));
        yield TextField::new('requestMethod', $this->t('admin.crud.logs.label.request_method'))->hideOnIndex();
        yield TextField::new('requestPath', $this->t('admin.crud.logs.label.request_path'))->hideOnIndex();
        yield TextField::new('clientIP', $this->t('admin.crud.logs.label.client_ip'))->hideOnIndex();
        yield AssociationField::new('user', $this->t('admin.crud.user.label.user'))->hideOnIndex();
        yield TextField::new('clientLocale', $this->t('admin.crud.logs.label.client_locale'))->hideOnIndex();

        return $this;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('level', $this->t('admin.crud.logs.label.level'))
            ->add('context', $this->t('admin.crud.logs.label.context'))
            ->add('message', $this->t('admin.crud.logs.label.message'))
            ->add('user', $this->t('admin.crud.user.label.user'))
            ->add('createdAt', $this->t('admin.crud.generic.created_at'))
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
