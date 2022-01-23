<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TrackRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrackRepository::class)]
class Track
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    private $id;

    #[ORM\Column(type: 'string', length: 512)]
    private $title;

    #[ORM\ManyToOne(targetEntity: Artist::class, inversedBy: 'tracks')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $artist;

    #[ORM\Column(type: 'integer')]
    private $duration;

    #[ORM\Column(type: 'integer')]
    private $playbackCount;

    #[ORM\Column(type: 'integer')]
    private $commentCount;

    #[ORM\Column(type: 'integer', unique: true)]
    private $extId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artist): self
    {
        $this->artist = $artist;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getPlaybackCount(): ?int
    {
        return $this->playbackCount;
    }

    public function setPlaybackCount(int $playbackCount): self
    {
        $this->playbackCount = $playbackCount;

        return $this;
    }

    public function getCommentCount(): ?int
    {
        return $this->commentCount;
    }

    public function setCommentCount(int $commentCount): self
    {
        $this->commentCount = $commentCount;

        return $this;
    }

    public function getExtId(): ?int
    {
        return $this->extId;
    }

    public function setExtId(int $extId): self
    {
        $this->extId = $extId;

        return $this;
    }
}
