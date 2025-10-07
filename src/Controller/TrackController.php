<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Service\AuthSpotifyService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/track')]

class TrackController extends AbstractController
{
    private string $token;
    public function __construct(private readonly AuthSpotifyService $authSpotifyService,)
    {
        $this->token = $this->authSpotifyService->auth();
        dd($this->token);
    }

    #[Route('/', name: 'app_track_index')]


    public function number(int $max): Response
    {
        return $this->render('track/track.html.twig');
    }
}