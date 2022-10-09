<?php

namespace App\Controller\Admin;

use App\Entity\Log;
use App\Entity\User;
use App\Entity\UserRole;
use App\Entity\Newsletter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Translation\TranslatableMessage;

use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function t(string $message, array $parameters = []): TranslatableMessage
    {
        return new TranslatableMessage($message, $parameters, 'admin');
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('@EasyAdmin/pages/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
        ->setTranslationDomain('admin')
        ->setTitle($this->getParameter('app')['easyadmin']['dashboard_title']);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard($this->t('Dashboard'), 'fa-solid fa-house');

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section($this->t('admin.dashboard.menu.label.administration'));
            yield MenuItem::linkToCrud($this->t('admin.dashboard.menu.label.users'), 'fas fa-user', User::class);
            yield MenuItem::linkToCrud($this->t('admin.dashboard.menu.label.user_roles'), 'fas fa-user-tag', UserRole::class);
            yield MenuItem::section($this->t('admin.dashboard.menu.label.system'));
            yield MenuItem::linkToCrud($this->t('admin.dashboard.menu.label.logs'), 'fas fa-list', Log::class);
            if ($this->isGranted('ROLE_SUPER_ADMIN')) {
                yield MenuItem::linkToRoute($this->t('admin.dashboard.menu.label.phpinfo'), 'fa-brands fa-php', 'admin_phpinfo');
            }
        }
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $menuItems[] = MenuItem::linkToCrud($this->t('admin.dashboard.menu.label.my_profile'), 'fa fa-id-card', User::class)
                                ->setAction('edit')
                                ->setEntityId($this->security->getUser()->getId());
        $menuItems[] = MenuItem::section();
        $menuItems[] = MenuItem::linkToRoute('Activate 2FA', 'fa fa-lock', 'app_2fa_enable');

        return parent::configureUserMenu($user)
            ->displayUserAvatar(true)
            ->setGravatarEmail($this->security->getUser()->getEmail())
            ->addMenuItems($menuItems);
    }

    //Default Crud Settings
    public function configureCrud(): Crud
    {
        return Crud::new()
            ->setPaginatorPageSize(30)
            ->setTimezone($this->security->getUser()->getTimeZone())
            ->setDateTimeFormat($this->security->getUser()->getDateFormat().' '.$this->security->getUser()->getTimeFormat())
            ->setDateFormat($this->security->getUser()->getDateFormat())
            ->setTimeFormat($this->security->getUser()->getTimeFormat())
        ;
    }


    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('assets/css/easyadmin.css');
    }

}
