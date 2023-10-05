<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Repository\PlaylistRepository;
use App\Util\Slugger;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlaylistRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    types: ['https://schema.org/Playlist'],
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => [
                    'playlists:collection:get',
                ],
            ],
            order: [
                'name' => 'ASC',
            ]
        ),
        new GetCollection(
            uriTemplate: 'videos/{id}/playlists',
            uriVariables: [
                'id' => new Link(
                    fromClass: Playlist::class,
                    toProperty: 'videos'
                ),
            ],
            normalizationContext: [
                'groups' => [
                    'playlists:collection:get',
                ],
            ],
            order: [
                'name' => 'ASC',
            ]
        ),
        new Get(
            normalizationContext: [
                'groups' => [
                    'playlists:item:get',
                ],
            ]
        ),
    ]
)]
class Playlist
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['playlists:item:get', 'playlists:collection:get', 'playlists-without-user:collection:get'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[ApiFilter(OrderFilter::class, arguments: ['orderParameterName' => 'order'])]
    #[Groups(['playlists:item:get', 'playlists:collection:get', 'playlists-without-user:collection:get'])]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $slug;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    #[Groups(['playlists:item:get', 'playlists:collection:get'])]
    private bool $private = false;

    #[ORM\ManyToOne(inversedBy: 'playlists')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['playlists:collection:get'])]
    private User $user;

    #[ORM\ManyToMany(targetEntity: Video::class, inversedBy: 'playlists', fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $videos;

    public function __construct()
    {
        $this->videos = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return void
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setSlug(): void
    {
        $this->slug = Slugger::slugify($this->name);
    }

    /**
     * @return bool
     */
    public function isPrivate(): bool
    {
        return $this->private;
    }

    /**
     * @param bool $private
     * @return $this
     */
    public function setPrivate(bool $private): static
    {
        $this->private = $private;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param User|null $user
     * @return bool
     */
    public function isOwner(?User $user = null): bool
    {
        return $user && $this->getUser()->getId() === $user->getId();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    public function addVideo(Video $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
        }

        return $this;
    }

    public function removeVideo(Video $video): static
    {
        $this->videos->removeElement($video);

        return $this;
    }
}
