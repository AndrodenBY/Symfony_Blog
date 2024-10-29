<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\Comment;
use App\Form\BlogType;
use App\Form\CommentType;
use App\Repository\CategoryRepository;
use App\Repository\UserRepository;
use App\Repository\BlogRepository;
use App\Repository\CommentRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use mysql_xdevapi\Result;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Doctrine\Common\Collections\ArrayCollection;

#[Route('/blog')]
final class BlogController extends AbstractController
{
    #[Route(name: 'app_blog_homepage', methods: ['GET'])]
    public function index(BlogRepository $blogRepository, CategoryRepository $categoryRepository, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($this->getUser());
        $blogs = $blogRepository->findAll();
        $categories = $categoryRepository->findAll();
        //dd($blogs);
        foreach ($blogs as $blog) {
            if (!($blog->getCategories() instanceof Collection)) {
                throw new \Exception('Categories is not a Collection');
            }
        }

        return $this->render('blog/homepage.html.twig', [
            'blogs' => $blogs,
            'categories' => $categories,
            'user' => $user,
        ]);
    }

    #[Route('/new', name: 'app_blog_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $blog = new Blog();
        $blog->setUser($user);
        $blog->setPublishedAt(new \DateTime());

        $categories = $categoryRepository->findAll();
        $categoryCollection = new ArrayCollection($categories);
        $blog->setCategories($categoryCollection);
        if (!$categories) {
            $categories = [];
        }
        $form = $this->createForm(BlogType::class, $blog, [
            'categories' => $categories,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($blog);
            $entityManager->flush();

            return $this->redirectToRoute('app_blog_my_blogs', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/new.html.twig', [
            'blog' => $blog,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/showId={id}', name: 'app_blog_show', methods: ['GET'])]
    public function show(BlogRepository $blogRepository, CommentRepository $commentRepository, int $id): Response
    {
        $blog = $blogRepository->find($id);
        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        $comments = $commentRepository->findBy(['blog' => $blog]);

        return $this->render('blog/show.html.twig', [
            'blog' => $blog,
            'comments' => $comments,
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_blog_page', methods: ['GET'])]
    public function page(BlogRepository $blogRepository, CommentRepository $commentRepository, CategoryRepository $categoryRepository, int $id): Response
    {
        $blog = $blogRepository->find($id);
        $categories = $categoryRepository->findAll();
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);

        if (!$blog) {
            throw $this->createNotFoundException('Blog not found');
        }

        $comments = $commentRepository->findBy(['blog' => $blog]);
        $blogCategories = $blog->getCategories();
        $allCategories = $categoryRepository->findAll();


        return $this->render('blog/page.html.twig', [
            'blog' => $blog,
            'comments' => $comments,
            'blogCategories' => $blogCategories,
            'allCategories' => $allCategories,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}/edit', name: 'app_blog_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BlogType::class, $blog);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_blog_my_blogs', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('blog/edit.html.twig', [
            'blog' => $blog,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'app_blog_delete', methods: ['POST'])]
    public function delete(Request $request, Blog $blog, EntityManagerInterface $entityManager): Response
    {
        //service.authorised
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        if($blog->getUser()->getId() !== $user->getId()) {
            return $this->redirectToRoute('app_blog_my_blogs', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$blog->getId(), $request->request->get('_token'))) {
            $entityManager->remove($blog);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_blog_my_blogs', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/homepage/about', name: 'app_blog_about', methods: ['GET'])]
    public function about(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('blog/about.html.twig',
        [
            'categories' => $categories,
        ]);
    }

    #[Route('/my-blogs', name: 'app_blog_my_blogs', methods: ['GET'])]
    public function myBlogs(BlogRepository $blogRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $blogs = $blogRepository->findBy(['user' => $user]);

        return $this->render('blog/my_blogs.html.twig', [
            'blogs' => $blogs,
        ]);
    }

    #[Route('/category/{category}', name: 'app_blog_category')]
    public function findByCategory(string $category, BlogRepository $blogRepository): Response
    {
        $blogs = $blogRepository->findByCategory($category);
        return $this->render('blog/category.html.twig', [
            'blogs' => $blogs,
            'category' => $category,
        ]);
    }


    #[Route('/search/q={$query}', name: 'app_blog_search', methods: ['GET'])]
    public function search(Request $request, BlogRepository $blogRepository): Response
    {
        $query = $request->query->get('q');
        $blogs = $blogRepository->searchByTitle($query);

        return $this->render('blog/search_query.html.twig', [
            'blogs' => $blogs
        ]);
    }

    #[Route('/author/{user}', name: 'app_blog_by_author', methods: ['GET'])]
    public function findByAuthor(string $user, BlogRepository $blogRepository): Response
    {
        $blogs = $blogRepository->findByAuthor($user);
        return $this->render('blog/author.html.twig', [
            'blogs' => $blogs,
            'user' => $user,
        ]);
    }
}
