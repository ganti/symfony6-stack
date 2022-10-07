<?php

namespace App\Controller\Admin;

use App\Entity\Newsletter;
use Doctrine\DBAL\Types\TextType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class NewsletterCrudController extends AbstractCrudController
{
    public function t(string $message, array $parameters = []): TranslatableMessage
    {
        return new TranslatableMessage($message, $parameters, 'admin');
    }

    public function tm(string $message, array $parameters = []): TranslatableMessage
    {
        return new TranslatableMessage($message, $parameters, 'messages');
    }

    public static function getEntityFqcn(): string
    {
        return Newsletter::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable('show')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            if (Crud::PAGE_INDEX === $pageName) {
                if ($this->isGranted('ROLE_ADMIN')) {
                    yield IntegerField::new('id');
                    yield TextField::new('name', $this->tm('gate.newsletter.crud.basics.name.label'));
                    yield IntegerField::new('civicrmGroupId', $this->tm('gate.newsletter.crud.basics.crmgroup.label'));
                    yield LocaleField::new('locale', $this->tm('gate.newsletter.crud.basics.locale.label'));
                    yield BooleanField::new('isActive', $this->t('admin.crud.generic.is_active'))
                        ->renderAsSwitch(false);

                    yield DateTimeField::new('createdAt', $this->t('admin.crud.generic.created_at'));
                }
            } else {
                yield FormField::addTab($this->tm('gate.newsletter.crud.tabs.basics'), 'fa-solid fa-plug');
                yield TextField::new('name', $this->tm('gate.newsletter.crud.basics.name.label'))
                        ->setColumns(12);
                yield TextAreaField::new('description', $this->tm('gate.newsletter.crud.basics.description.label'))
                        ->setColumns(12);
                yield IntegerField::new('civicrmGroupId', $this->tm('gate.newsletter.crud.basics.crmgroup.label'))
                        ->setColumns(12);
                yield IntegerField::new('linkValidPeriod', $this->tm('gate.newsletter.crud.basics.linkValidPeriod.label'))
                        ->setColumns(12);
                yield LocaleField::new('locale', $this->tm('gate.newsletter.crud.basics.locale.label'))
                        ->includeOnly(['de', 'fr', 'en'])
                        ->setColumns(12);
                yield BooleanField::new('active', $this->t('admin.crud.generic.is_active'))
                        ->setColumns(12)
                        ->renderAsSwitch(false);
                yield DateTimeField::new('createdAt', $this->t('admin.crud.generic.created_at'))
                        ->setColumns(2)
                        ->setFormTypeOption('disabled', 'disabled');
                yield DateTimeField::new('updatedAt', $this->t('admin.crud.generic.updated_at'))
                        ->setColumns(2)
                        ->setFormTypeOption('disabled', 'disabled');



                yield FormField::addTab($this->tm('gate.newsletter.crud.tabs.email_templates'), 'fa-solid fa-envelope');

                yield FormField::addPanel($this->tm('gate.newsletter.crud.subscribe.title.label'), 'fa-solid fa-envelope');
                yield TextField::new('textSubscribeSubject', $this->tm('gate.newsletter.crud.subscribe.subject.label'));
                yield CodeEditorField::new('textSubscribe', $this->tm('gate.newsletter.crud.subscribe.text.label'))
                        ->setNumOfRows(10)
                        ->setHelp($this->tm('gate.newsletter.crud.subscribe.text.help'));

                yield FormField::addPanel($this->tm('gate.newsletter.crud.unsubscribe.title.label'), 'fa-solid fa-envelope');
                yield TextField::new('textUnsubscribeSubject', $this->tm('gate.newsletter.crud.unsubscribe.subject.label'));
                yield CodeEditorField::new('textunsubscribe', $this->tm('gate.newsletter.crud.unsubscribe.text.label'))
                        ->setHelp($this->tm('gate.newsletter.crud.unsubscribe.text.help'))
                        ->setNumOfRows(10);

                yield FormField::addTab($this->tm('gate.newsletter.crud.tabs.url_forwards'), 'fa-solid fa-forward');
                yield FormField::addPanel($this->tm('gate.newsletter.crud.url_forwards.title.label'))
                        ->setCssClass('col-9')
                        ->setIcon('fa-solid fa-forward')
                        ->setHelp($this->tm('gate.newsletter.crud.url_forwards.title.help'));
                yield TextField::new('urlSuccess', $this->tm('gate.newsletter.crud.url_forwards.url_success.label'))
                        ->setColumns(12)
                        ->setHelp($this->tm('gate.newsletter.crud.url_forwards.url_success.help'));
                yield TextField::new('urlError', $this->tm('gate.newsletter.crud.url_forwards.url_error.label'))
                        ->setColumns(12)
                        ->setHelp($this->tm('gate.newsletter.crud.url_forwards.url_error.help'));
            }
        }
    }
}
