<?php

namespace App\Controller\Admin;

use App\Entity\Log;
use App\Entity\User;
use App\Entity\UserRole;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

use function App\Controller\Admin\t;

class DashboardController extends AbstractDashboardController
{
    public function __construct(Security $security)
    {
        $this->security = $security;
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
        yield MenuItem::linktoDashboard(t('admin.dashboard.menu.label.dashboard'), 'fa fa-home');

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section(t('admin.dashboard.menu.label.administration'));
            yield MenuItem::linkToCrud(t('admin.dashboard.menu.label.users'), 'fas fa-user', User::class);
            yield MenuItem::linkToCrud(t('admin.dashboard.menu.label.user_roles'), 'fas fa-user-tag', UserRole::class);
            yield MenuItem::section(t('admin.dashboard.menu.label.system'));
            yield MenuItem::linkToCrud(t('admin.dashboard.menu.label.logs'), 'fas fa-list', Log::class);
            if ($this->isGranted('ROLE_SUPER_ADMIN')) {
                yield MenuItem::linkToRoute(t('admin.dashboard.menu.label.phpinfo'), 'fa-brands fa-php', 'admin_phpinfo');
            }
        }
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)

            //Icon
            ->displayUserAvatar(true)
            ->setGravatarEmail($this->security->getUser()->getEmail())

            ->addMenuItems([
                MenuItem::linkToCrud(t('admin.dashboard.menu.label.my_profile'), 'fa fa-id-card', User::class)
                    ->setAction('edit')
                    ->setEntityId($this->security->getUser()->getId()),
                MenuItem::section(),
            ]);
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
}
