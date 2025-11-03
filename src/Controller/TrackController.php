<?php

namespace App\Controller;

use App\Entity\Track;
use App\Service\AuthSpotifyService;
use App\Service\SpotifyRequestService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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
        $spotifyId = is_string($spotifyId) ? $this->normalizeSpotifyId(trim($spotifyId)) : $spotifyId;
        $track = $em->getRepository(Track::class)->findOneBy(['spotifyId' => $spotifyId]);

        if ($track) {
            $em->remove($track);
            $em->flush();
            return new JsonResponse(['status' => 'unliked']);
        }

        $track = new Track();
        $track->setSpotifyId($spotifyId);
        $track->setSpotifyUrl('https://open.spotify.com/track/' . $spotifyId);
        $track->setName($name);
        $track->setPictureLink($imageUrl);

        try {
            $em->persist($track);
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            return new JsonResponse(['status' => 'liked']);
        }

        return new JsonResponse(['status' => 'liked']);
    }

    #[Route('/dislike', name: 'app_dislike', methods: ['POST'])]
    public function dislike(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

            $spotifyId = $data['trackId'] ?? $request->request->get('trackId');
            $spotifyId = is_string($spotifyId) ? $this->normalizeSpotifyId(trim($spotifyId)) : $spotifyId;

        if (!$spotifyId) {
            return new JsonResponse(['error' => 'Missing trackId'], 400);
        }

        $track = $em->getRepository(Track::class)->findOneBy(['spotifyId' => $spotifyId]);

        if (!$track) {
            return new JsonResponse(['status' => 'not_found'], 404);
        }

        $em->remove($track);
        $em->flush();

        return new JsonResponse(['status' => 'disliked']);
    }

    #[Route('/favorites', name: 'app_track_favorites')]
    public function favorites(EntityManagerInterface $em): Response
    {
        $favorites = $em->getRepository(Track::class)->findBy([], ['id' => 'DESC']);

        return $this->render('track/favorites.html.twig', [
            'favorites' => $favorites,
        ]);
    }

    #[Route('/{search?}', name: 'app_track_index')]
    public function index(?string $search, EntityManagerInterface $em): Response
    {
        $tracks = [];
        $favoriteIds = [];

        if ($search && trim($search) !== '') {
            $tracks = $this->spotifyRequestService->searchTracks($search, $this->token);

            $spotifyIds = array_values(array_filter(array_map(fn($t) => $t['id'] ?? null, $tracks)));

            if (!empty($spotifyIds)) {
                $repo = $em->getRepository(Track::class);
                $matches = $repo->createQueryBuilder('t')
                    ->select('t.spotifyId')
                    ->where('t.spotifyId IN (:ids)')
                    ->setParameter('ids', $spotifyIds)
                    ->getQuery()
                    ->getArrayResult();


                $favoriteIds = array_map(fn($m) => $m['spotifyId'], $matches);
            }
        }

        return $this->render('track/index.html.twig', [
            'tracks' => $tracks,
            'search' => $search,
            'favoriteIds' => $favoriteIds,
        ]);
    }

    #[Route('/show/{id}', name: 'app_track_show')]
    public function show(string $id): Response
    {
        return $this->render('track/show.html.twig', [
            'track' => $this->spotifyRequestService->getTrack($id, $this->token),
        ]);
    }

    private function normalizeSpotifyId(string $maybeId): string
    {
        // If it's a full Spotify URL like https://open.spotify.com/track/{id}?si=..., extract the id
        if (str_contains($maybeId, 'open.spotify.com/track/')) {
            $parts = parse_url($maybeId);
            $path = $parts['path'] ?? '';
            $segments = explode('/', trim($path, '/'));
            return $segments[count($segments) - 1] ?? $maybeId;
        }
        // spotify URI format spotify:track:{id}
        if (str_starts_with($maybeId, 'spotify:track:')) {
            return substr($maybeId, strlen('spotify:track:'));
        }
        return $maybeId;
    }

}