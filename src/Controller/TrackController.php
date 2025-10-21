<?php

namespace App\Controller;

use App\Service\AuthSpotifyService;
use App\Service\SpotifyRequestService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/track')]
class TrackController extends AbstractController
{
    private string $token;

    public function __construct(
        private readonly AuthSpotifyService    $authSpotifyService,
        private readonly SpotifyRequestService $spotifyRequestService
    )
    {
        $this->token = $this->authSpotifyService->auth();
    }

    #[Route('/', name: 'app_track_index')]
    public function index(): Response
    {
        return $this->render('track/index.html.twig');
    }

    #[Route('/show', name: 'app_track_show')]
    public function show(Request $request): Response
    {
        $search = $request->query->get('search');
        $results = [];
        if ($search) {
            $results = $this->spotifyRequestService->searchTracks($search, $this->token);
        }

        return $this->render('track/show.html.twig', [
            'results' => $results,
            'search' => $search,
        ]);
    }
}