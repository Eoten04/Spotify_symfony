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

    #[Route('/', name: 'app_track')]
    public function index(): Response
    {

        return $this->render('track/index.html.twig');
    }

    #[Route('/response', name: 'app_track_response', methods: ['GET', 'POST'])]
    public function response(Request $request): Response
    {
        $artist = $request->request->get('search');

        if (!$artist) {
            return $this->redirectToRoute('app_track');
        }

        $response = $this->httpClient->request(
            'GET',
            'https://api.spotify.com/v1/search?query=' . urlencode($artist) . '&type=track&locale=fr_FR',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]
        )->toArray();
        $data = $response['tracks']['items'] ?? [];
//dd($data);
        return $this->render('track/response.html.twig', [
            'data' => $data,
            'artist' => $artist,
        ]);
        }
}