<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\BlogRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/comment')]
final class CommentController extends AbstractController
{
    #[Route(name: 'app_comment_index', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

    /*#[Route('/new', name: 'app_comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,
                        CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $comment = new Comment();
        $user = $this->getUser();
        $blogId = $request->request->get('blog_id');
        if (!$blogId) {
            throw $this->createNotFoundException('Blog ID is missing.');
        }
        $blog = $entityManager->getRepository(Blog::class)->find($blogId);
        if (!$user || !$blog) {
            throw $this->createNotFoundException('User or Blog not found.');
        }

        $comment->setUser($user);
        $comment->setBlog($blog);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = new CsrfToken('comment', $request->request->get('_csrf_token'));
            if (!$csrfTokenManager->isTokenValid($token)) {
                throw new InvalidCsrfTokenException('Invalid CSRF token.');
            }

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_blog_page', ['id' => $blog->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
            'blog' => $blog,
        ]);
    }*/

    #[Route('/add', name: 'app_comment_add', methods: ['POST'])]
    public function addCommentAction(Request $request, EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $blogId = $request->request->get('blog_id');
        $blog = $entityManager->getRepository(Blog::class)->find($blogId);
        if (!$blog) {
            throw $this->createNotFoundException('Blog not found.');
        }

        $token = new CsrfToken('comment', $request->request->get('_csrf_token'));
        if (!$csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException('Invalid CSRF token.');
        }

        $comment = new Comment();
        $comment->setUser($user);
        $comment->setBlog($blog);
        $comment->setContent($request->request->get('content'));

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_blog_page', ['id' => $blog->getId()], Response::HTTP_SEE_OTHER);
    }


    #[Route('/{id}', name: 'app_comment_show', methods: ['GET'])]
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function delete(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
    }
}
