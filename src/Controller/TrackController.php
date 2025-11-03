<?php

namespace App\Controller;

use App\Entity\Track;
use App\Entity\User;
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
        $imageUrl = $data['imageUrl'] ?? null;

        if (!$spotifyId) {
            return new JsonResponse(['error' => 'Missing trackId'], 400);
        }

        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not logged in'], 401);
        }

        $spotifyId = is_string($spotifyId) ? $this->normalizeSpotifyId(trim($spotifyId)) : $spotifyId;
        $track = $em->getRepository(Track::class)->findOneBy(['spotifyId' => $spotifyId]);

        if (!$track) {
            $track = new Track();
            $track->setSpotifyId($spotifyId);
            $track->setSpotifyUrl('https://open.spotify.com/track/' . $spotifyId);
            $track->setName($name);
            $track->setPictureLink($imageUrl);
            $em->persist($track);
        }

        if ($track->getUsers()->contains($user)) {
            $track->removeUser($user);
            $status = 'unliked';
        } else {
            $track->addUser($user);
            $status = 'liked';
        }

        $em->flush();

        return new JsonResponse(['status' => $status]);
    }


    #[Route('/favorites', name: 'app_track_favorites')]
    public function favorites(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $tracks = $em->createQueryBuilder()
            ->select('t')
            ->from(Track::class, 't')
            ->join('t.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('track/favorites.html.twig', [
            'favorites' => $tracks,
        ]);
    }

    #[Route('/{search?}', name: 'app_track_index')]
    public function index(?string $search, EntityManagerInterface $em): Response
    {
        $tracks = [];
        $favoriteIds = [];

        if ($search && trim($search) !== '') {
            $tracks = $this->spotifyRequestService->searchTracks($search, $this->token);
            $spotifyIds = array_filter(array_map(fn($t) => $t['id'] ?? null, $tracks));

            $user = $this->getUser();
            if ($user && !empty($spotifyIds)) {
                $repo = $em->getRepository(Track::class);
                $matches = $repo->createQueryBuilder('t')
                    ->select('t.spotifyId')
                    ->join('t.users', 'u')
                    ->where('t.spotifyId IN (:ids)')
                    ->andWhere('u = :user')
                    ->setParameter('ids', $spotifyIds)
                    ->setParameter('user', $user)
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
        if (str_contains($maybeId, 'open.spotify.com/track/')) {
            $parts = parse_url($maybeId);
            $path = $parts['path'] ?? '';
            $segments = explode('/', trim($path, '/'));
            return end($segments) ?: $maybeId;
        }
        if (str_starts_with($maybeId, 'spotify:track:')) {
            return substr($maybeId, strlen('spotify:track:'));
        }
        return $maybeId;
    }

}