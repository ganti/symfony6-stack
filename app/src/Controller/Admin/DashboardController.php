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
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

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
            ->setTitle('App');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');

        if ($this->isGranted('ROLE_ADMIN')) {
            yield MenuItem::section('Administration');
            yield MenuItem::linkToCrud('Users', 'fas fa-user', User::class);
            yield MenuItem::linkToCrud('User Roles', 'fas fa-user-tag', UserRole::class);
            yield MenuItem::section('System');
            yield MenuItem::linkToCrud('Logs', 'fas fa-list', Log::class);
            if ($this->isGranted('ROLE_SUPER_ADMIN')) {
                yield MenuItem::linkToRoute('phpInfo', 'fa-brands fa-php', 'admin_phpinfo');
            }
        }
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)

            //Icon
            ->displayUserAvatar(true)
            ->setGravatarEmail($this->security->getUser()->getEmail())

            // you can use any type of menu item, except submenus
            ->addMenuItems([
                MenuItem::linkToCrud('My Profile', 'fa fa-id-card', User::class)
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
            ->setDateTimeFormat('yyyy-MM-dd HH:mm:ss')
            ->setDateFormat('yyyy-MM-dd')
            ->setTimeFormat('HH:mm:ss')
        ;
    }


    #[IsGranted('ROLE_SUPER_ADMIN')]
    #[Route('/admin/phpinfo', name: 'admin_phpinfo')]
    public function phpinfo(): Response
    {
        ob_start();
        phpinfo(INFO_GENERAL);
        phpinfo(INFO_CONFIGURATION);
        phpinfo(INFO_MODULES);

        $output = ob_get_contents();
        ob_get_clean();
        //$output = preg_replace('#<style type="text/css">.*?</style>#s', '', $output);

        $output = str_replace('body {background-color: #fff;', 'body {', $output);
        $output = str_replace('hr {width: 934px; background-color: #ccc; border: 0; height: 1px;}', '', $output);
        $output = str_replace('a:link {color: #009; text-decoration: none; background-color: #fff;}', '', $output);

        return $this->render('@EasyAdmin/pages/phpinfo.html.twig', ['phpinfo' => $output]);
    }
}
