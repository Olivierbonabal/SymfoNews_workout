<?php

namespace App\Controller;

use App\Repository\NewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomePageController extends AbstractController
{
    #[Route('/', name: 'app_home_page')]
    public function index(NewsRepository $newsRepository): Response
    {
        return $this->render('home_page/index.html.twig', [
            'news' => $newsRepository->findAll()
        ]);
    }
}
