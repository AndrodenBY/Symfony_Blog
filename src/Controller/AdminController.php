<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'); //ROLE_ADMIN

        /** @var User $user */
        $user = $this->getUser();
        return match ($user->isVerified()) {
            true => $this->render('admin/index.html.twig',),
            false => $this->render('admin/please-verify-email.html.twig',),
        };

        return $this->render('admin/index.html.twig');
    }
}
