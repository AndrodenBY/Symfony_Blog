<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Form\BlogType;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Repository\BlogRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Result;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/blog')]
final class BlogController extends AbstractController
{
    #[Route(name: 'app_blog_homepage', methods: ['GET'])]
    public function index(BlogRepository $blogRepository): Response
    {
        return $this->render('blog/homepage.html.twig', [
            'blogs' => $blogRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,
                        CategoryRepository $categoryRepository): Response
    {
        $user = $this->getUser();
        if (!$user)
        {
            return $this->redirectToRoute('app_login');
        }

        $blog = new Blog();
        $blog->setUser($user);

        $categories = $categoryRepository->findAll() ?: [];

        $form = $this->createForm(BlogType::class, $blog, [
            'categories' => $categories,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            return $this->redirectToRoute('app_blog_homepage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/new.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[Route('/showId={id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(Blog $blog): Response
    {
        return $this->render('blog/show.html.twig', [
            'blog' => $blog,
        ]);
    }
    #[Route('/{id}', name: 'app_blog_page', methods: ['GET'])]
    public function page(BlogRepository $blogRepository, CommentRepository $commentRepository, string $id): Response
    {
        $id = (int) $id;
        if (!is_numeric($id))
        {
            throw new \InvalidArgumentException('ID must be an integer.');
        }

        $blog = $blogRepository->find($id);

        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        $comments = $commentRepository->findByBlogAndAuthor($id);

        return $this->render('blog/page.html.twig', [
            'blog' => $blog,
            'comments' => $comments,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_blog_homepage', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_blog_delete', methods: ['POST'])]
    public function delete(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blog->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($blog);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_blog_homepage', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/homepage/about', name: 'app_blog_about', methods: ['GET'])]
    public function about(BlogRepository $blogRepository): Response
    {
        return $this->render('blog/about.html.twig');
    }

    #[Route('/search/q={$query}', name: 'app_blog_search', methods: ['GET'])]
    public function search(Request $request, BlogRepository $blogRepository): Response
    {
        $query = $request->query->get('q');
        $blogs = $blogRepository->searchByQuery($query);

        return $this->render('blog/search_query.html.twig', [
            'posts' => $blogs
        ]);
    }
}
