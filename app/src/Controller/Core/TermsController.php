<?php

namespace App\Controller\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TermsController extends AbstractController
{
    #[Route(path: '/terms', name: 'app_terms_base')]
    public function terms_base(): Response
    {
        return $this->redirectToRoute('app_terms', ['_locale' => $this->getParameter('app')['default_locale']]);
    }

    #[Route(path: '/{_locale}/terms', name: 'app_terms')]
    public function terms($_locale, Request $request): Response
    {
        if (in_array($request->getLocale(), $this->getParameter('app')['admin_locales'])) {
            return $this->render('view/core/terms/terms.html.twig');
        } else {
            return $this->redirectToRoute('app_terms_base');
        }
    }
}
