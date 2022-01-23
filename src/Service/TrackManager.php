<?php

namespace App\Service;

use App\Entity\Artist;
use App\Entity\Track;
use App\Repository\ArtistRepository;
use App\Repository\TrackRepository;
use Doctrine\ORM\EntityManagerInterface;

final class TrackManager
{
    private ArtistRepository $artistRepository;
    private TrackRepository $trackRepository;
    private EntityManagerInterface $em;

    public function __construct(
        ArtistRepository       $artistRepository,
        TrackRepository        $trackRepository,
        EntityManagerInterface $entityManager,
    )
    {
        $this->artistRepository = $artistRepository;
        $this->trackRepository = $trackRepository;
        $this->em = $entityManager;
    }

    /**
     * @param Track[] $tracks
     */
    public function upsert(array $tracks) {
        $this->em->beginTransaction();

        $artistsByExtID = $this->upsertArtists($tracks);
        // Link incomming tracks with upserted artists from db.
        foreach ($tracks as $track) {
            $track->setArtist($artistsByExtID[$track->getArtist()->getExtId()]);
        }
        $this->upsertTracks($tracks);
        $this->em->commit();
        $this->em->clear(); // Forget what we knew.
    }

    /**
     * @param Track[] $tracks
     * @return Artist[] artists by ext_id.
     */
    private function upsertArtists(array $tracks): array
    {
        // Make artists unique.
        $artists = [];
        foreach ($tracks as $track) {
            $artists[$track->getArtist()->getExtId()] = $track->getArtist();
        }

        $artistsByExtID = [];
        // Fetch known artists once.
        $artistsToUpdate = $this->artistRepository->findBy(['extId' => array_keys($artists)]);
        foreach ($artistsToUpdate as $artist) {
            $extID = $artist->getExtId();
            $artist->setFollowersCount($artists[$extID]->getFollowersCount())
                ->setUsername($artists[$extID]->getUsername())
                ->setCity($artists[$extID]->getCity())
                ->setFullName($artists[$extID]->getFullName());
            unset($artists[$extID]);

            $artistsByExtID[$extID] = $artist;
        }

        // Insert new.
        foreach ($artists as $artist) {
            $this->em->persist($artist);
            $artistsByExtID[$artist->getExtId()] = $artist;
        }

        $this->em->flush();

        return $artistsByExtID;
    }

    /**
     * @param Track[] $tracks
     */
    private function upsertTracks(array $tracks): void
    {
        $tracksByExtID = [];
        foreach ($tracks as $track) {
            $tracksByExtID[$track->getExtId()] = $track;
        }

        $tracksToUpdate = $this->trackRepository->findBy(['extId' => array_keys($tracksByExtID)]);
        foreach ($tracksToUpdate as $track) {
            $extID = $track->getExtId();
            $apiTrack = $tracksByExtID[$extID];
            $track->setTitle($apiTrack->getTitle())
                ->setPlaybackCount($apiTrack->getPlaybackCount())
                ->setCommentCount($apiTrack->getCommentCount())
                ->setDuration($apiTrack->getDuration())
            ;
            unset($tracksByExtID[$extID]);
        }
        foreach ($tracksByExtID as $track) {
            $this->em->persist($track);
        }
        $this->em->flush();
    }
}
