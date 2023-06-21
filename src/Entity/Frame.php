<?php

namespace App\Entity;

use App\Repository\FrameRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Frame
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: FrameRepository::class)]
class Frame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $url;

    #[ORM\ManyToOne(inversedBy: 'frames')]
    #[ORM\JoinColumn(nullable: false)]
    private Video $video;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Video
     */
    public function getVideo(): Video
    {
        return $this->video;
    }

    /**
     * @param Video|null $video
     * @return $this
     */
    public function setVideo(?Video $video): static
    {
        $this->video = $video;

        return $this;
    }
}
