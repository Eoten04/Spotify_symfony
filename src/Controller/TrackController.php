<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use App\Service\AuthSpotifyService;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/track')]

class TrackController extends AbstractController
{
    private string $token;
    private HttpClientInterface $httpClient;
    public function __construct(private readonly AuthSpotifyService $authSpotifyService,)
    {
        $this->token = $this->authSpotifyService->auth();
        $this->httpClient = HttpClient::create();
    }

    #[Route('/', name: 'app_track_index')]
    public function index(): Response
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?offset=0&limit=20&query=freeze%20corleone&type=track&locale=fr-FR',
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ]
        ])->toArray();

        return $this->render('track/index.html.twig', [
            'controller_name' => 'TrackController',
            'data' => $response['tracks']['items'],
        ]);
    }



}