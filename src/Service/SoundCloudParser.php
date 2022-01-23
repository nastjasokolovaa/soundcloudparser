<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Artist;
use App\Entity\Track;
use App\Exception\SoundCloudParseException;
use Symfony\Contracts\HttpClient\Exception\{
    ClientExceptionInterface,
    RedirectionExceptionInterface,
    ServerExceptionInterface,
    TransportExceptionInterface
};
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SoundCloudParser
{
    private string $clientID;
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        // Some public API-key, can be moved to config.
        $this->clientID = 'RCfT93M4biAV6sjNiab6pMV1eYEgatjk';
        $this->client = $client;
    }

    /**
     * @param string $link
     * @return int
     * @throws SoundCloudParseException - parse error.
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function resolveUserID(string $link): int
    {
        $response = $this->client->request('GET', $link);

        if (200 !== $response->getStatusCode()) {
            throw new SoundCloudParseException("Wrong status code: {$response->getStatusCode()}");
        }
        $body = $response->getContent();

        $user = $this->parseUser($body);
        if (($user['id'] ?? 0) > 0) {
            return (int)$user['id'];
        }

        throw new SoundCloudParseException('User ID not found');
    }

    /**
     * @return array user object.
     * @throws SoundCloudParseException
     */
    private function parseUser(string $body): array
    {
        $userParams = [];
        if (!preg_match('#window.__sc_hydration = (.*?);</script>#US', $body, $userParams)) {
            throw new SoundCloudParseException('User Params not found');
        }

        $jsonParams = json_decode($userParams[1], true);
        $jsonParams = array_filter($jsonParams, function ($el) {
            return $el['hydratable'] === 'user';
        });
        if (empty($jsonParams)) {
            throw new SoundCloudParseException('Can not find user Params');
        }

        $user = $jsonParams[array_key_first($jsonParams)]['data'] ?? false;
        if (!$user || !is_array($user)) {
            throw new SoundCloudParseException('User not found');
        }

        return $user;
    }

    /**
     * @param int $userID
     * @param int $limit
     * @param int $offset
     * @return Track[]
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws SoundCloudParseException
     * @throws TransportExceptionInterface
     */
    public function getTracks(int $userID, int $limit = 20): array
    {
        $url = "https://api-v2.soundcloud.com/users/{$userID}/tracks?representation=" .
            "&client_id={$this->clientID}" .
            "&limit={$limit}" .
            '&linked_partitioning=1&app_version=1642757693&app_locale=en';

        $response = $this->client->request('GET', $url);
        if (200 !== $response->getStatusCode()) {
            throw new SoundCloudParseException("Wrong status code: {$response->getStatusCode()}");
        }
        $body = $response->getContent();
        $trackResponse = json_decode($body, true)['collection'] ?? [];

        $tracks = array_map(function ($trackObj) {
            $artist = (new Artist())
                ->setExtId($trackObj['user']['id'] ?? 0)
                ->setUsername($trackObj['user']['username'] ?? '')
                ->setFullName($trackObj['user']['full_name'] ?? '')
                ->setCity($trackObj['user']['city'] ?? '')
                ->setFollowersCount($trackObj['user']['followers_count'] ?? 0);

            return (new Track())
                ->setArtist($artist)
                ->setExtId($trackObj['id'] ?? 0)
                ->setTitle($trackObj['title'] ?? '')
                ->setDuration($trackObj['duration'] ?? 0)
                ->setCommentCount($trackObj['comment_count'] ?? 0)
                ->setPlaybackCount($trackObj['playback_count'] ?? 0);
        }, $trackResponse);

        return array_filter($tracks, function ($track) {
            return $track->getArtist()->getUsername() && $track->getTitle();
        });
    }
}
