<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Service\NavCategory;
use App\Repository\NewsRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomePageController extends AbstractController
{
    private $navCategory;

    public function __construct(NavCategory $navCategory)
    {
        $this->navCategory = $navCategory;
    }

    #[Route('/', name: 'app_home_page')]
    public function index(NewsRepository $newsRepository): Response
    {
        return $this->render('home_page/index.html.twig', [
            'news' => $newsRepository->findAll(),
            'categoryList' => $this->navCategory->category()
        ]);
    }

    #[Route('/news/{id<[0-9]+>}', name: 'app_new_show')]
    public function newsShow(
        $id,
        NewsRepository $newsRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        CommentRepository $commentRepository
    ): Response {
        $newsId = $newsRepository->find($id);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $_user = $this->getUser();
            $comment->setAuthor($_user);
            $comment->setNews($newsId);
            $comment->setCreatedAt(new \DateTime);

            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('info', "Votre message a été envoyé !");
        }

        return $this->render('home_page/news_single.html.twig', [
            'single_news' => $newsRepository->find($newsId),
            'categoryList' => $this->navCategory->category(),
            'form' => $form->createView(),
            'comments' => $commentRepository->findBy(['news' => $newsId])
        ]);
    }

    #[Route('/news/{id<[0-9]+>}/category', name: 'app_new_by_category_show')]
    public function NewsByCategory(
        $id,
        NewsRepository $newsRepository,
        CategoryRepository $categoryRepository
    ): Response {
        $idCategory = $categoryRepository->find($id);
        $categoryName = $categoryRepository->findOneBy(['id' => $id], []);
        // dd($categoryName);
        $newsByCategory = $newsRepository->findBy(['category' => $idCategory], []);

        return $this->render('home_page/newsByCategory.html.twig', [
            'news' => $newsByCategory,
            'categoryList' => $this->navCategory->category(),
            'category' => $categoryName
        ]);
    }
}
