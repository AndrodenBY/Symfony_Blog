<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AuthorType;
use App\Repository\BlogRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/user')]
final class UserController extends AbstractController
{
    #[ISGranted('ROLE_ADMIN')]
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {

        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[ISGranted('ROLE_ADMIN')]
    #[Route(path: '/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(AuthorType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[ISGranted('ROLE_ADMIN')]
    #[Route(path: '/{id}/show', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/{id}', name: 'app_user_profile', methods: ['GET'])]
    public function profile(User $user, BlogRepository $blogRepository, CommentRepository $commentRepository): Response
    {
        $userBlogs = count($blogRepository->findByAuthor($user->getUsername()));
        $userComments = count($commentRepository->findByAuthor($user->getUsername()));
        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'blogs' => $userBlogs,
            'comments' => $userComments,
        ]);
    }

    //#[ISGranted('ROLE_ADMIN')]
    #[Route(path: '/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager,
                         KernelInterface $kernel): Response
    {
        if ($user->getId() == $this->getUser()->getId()) {
            $form = $this->createForm(AuthorType::class, $user);
            $form->handleRequest($request);
        }
        else{
            return $this->redirectToRoute('app_access_denied', [], Response::HTTP_SEE_OTHER);
        }
        if ($form->isSubmitted() && $form->isValid())
        {
            $uploadedFile = $form->get('icon')->getData();
            if($uploadedFile) {
                $file = $uploadedFile->move(
                    $kernel->getProjectDir() .'/public/uploads',
                    $uploadedFile->getClientOriginalName());
                $user->setIcon($file->getBasename());
            }
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_profile', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);

        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[ISGranted('ROLE_ADMIN')]
    #[Route(path: '/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path: '/access-denied', name: 'app_access_denied')] //FIX
    public function accessDenied(UserRepository $userRepository): Response
    {
        return $this->render('access_denied.html.twig');
    }
}