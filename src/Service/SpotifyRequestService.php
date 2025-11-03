<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;


readonly class SpotifyRequestService
{

    public function __construct(private HttpClientInterface $httpClient)
    {}

    public function searchTracks(string $track, string $token): array
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/search?query=' . $track . '&type=track&locale=fr-FR', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        return $response->toArray()['tracks']['items'];
    }

    public function getTrack(string $spotifyId,string $token): array
    {
        $response = $this->httpClient->request('GET', 'https://api.spotify.com/v1/tracks/' . $spotifyId, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        return $response->toArray();
    }
}