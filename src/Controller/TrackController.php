<?php

namespace App\Controller;

use App\Entity\Track;
use App\Service\AuthSpotifyService;
use App\Service\SpotifyRequestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tracks')]
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

    #[Route('/like', name: 'app_like', methods: ['POST'])]
    public function like(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $spotifyId = $data['trackId'] ?? null;
        $name = $data['trackName'] ?? null;
        $artist = $data['artistName'] ?? null;
        $album = $data['albumName'] ?? null;
        $imageUrl = $data['imageUrl'] ?? null;

        if (!$spotifyId) {
            return new JsonResponse(['error' => 'Missing trackId'], 400);
        }

        $track = $em->getRepository(Track::class)->findOneBy(['spotifyId' => $spotifyId]);

        if ($track) {
            $em->remove($track);
            $em->flush();
            return new JsonResponse(['status' => 'unliked']);
        }

        $track = new Track();
        $track->setSpotifyId($spotifyId);
        $track->setSpotifyUrl("https://open.spotify.com/track/" . $spotifyId);
        $track->setName($name);
        $track->setPictureLink($imageUrl);

        $em->persist($track);
        $em->flush();

        return new JsonResponse(['status' => 'liked']);
    }


    #[Route('/{search?}', name: 'app_track_index')]
    public function index(?string $search): Response
    {
        $tracks = [];
        if ($search && trim($search) !== '') {
            $tracks = $this->spotifyRequestService->searchTracks($search, $this->token);
        }

        return $this->render('track/index.html.twig', [
            'tracks' => $tracks,
            'search' => $search,
        ]);
    }

    #[Route('/show/{id}', name: 'app_track_show')]
    public function show(string $id): Response
    {
        return $this->render('track/show.html.twig', [
            'track' => $this->spotifyRequestService->getTrack($id, $this->token),
        ]);
    }
}