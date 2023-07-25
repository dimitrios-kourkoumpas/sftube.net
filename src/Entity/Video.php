<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\ApiResource\State\Provider\VideoWatchProvider;
use App\Repository\VideoRepository;
use App\Util\Slugger;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Class Video
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable()]
#[ApiResource(
    types: ['https://schema.org/Video'],
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => [
                    'videos:collection:get',
                ],
            ],
        ),
        new Get(
            normalizationContext: [
                'groups' => [
                    'videos:item:get',
                ],
            ],
            provider: VideoWatchProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/categories/{id}/videos',
            uriVariables: [
                'id' => new Link(
                    fromClass: Category::class,
                    fromProperty: 'videos'
                )
            ],
            normalizationContext: [
                'groups' => [
                    'videos:collection:get'
                ],
            ],
            order: [
                'createdAt' => 'DESC',
            ]
        ),
        new GetCollection(
            uriTemplate: '/tags/{id}/videos',
            uriVariables: [
                'id' => new Link(
                    fromClass: Tag::class,
                    toProperty: 'tags'
                )
            ],
            normalizationContext: [
                'groups' => [
                    'videos:collection:get'
                ],
            ],
            order: [
                'createdAt' => 'DESC',
            ]
        ),
        new GetCollection(
            uriTemplate: '/playlists/{id}/videos',
            uriVariables: [
                'id' => new Link(
                    fromClass: Playlist::class,
                    toProperty: 'playlists'
                )
            ],
            normalizationContext: [
                'groups' => [
                    'videos:collection:get'
                ],
            ],
            order: [
                'createdAt' => 'DESC',
            ]
        ),
    ]
)]
class Video
{
    public const SLIDESHOW_EXTRACTION = 'slideshow';

    public const PREVIEW_EXTRACTION = 'preview';

    public const PER_PAGE = 16;

    public const MAX_FRAMES = 10;

    public const NOT_AVAILABLE = 'not-available.jpeg';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['videos:collection:get', 'videos:item:get'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    #[Groups(['videos:collection:get', 'videos:item:get'])]
    private string $title;

    #[ORM\Column(length: 255)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['videos:item:get'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true, options: ['default' => self::NOT_AVAILABLE])]
    #[Groups(['videos:collection:get', 'videos:item:get'])]
    private string $thumbnail = self::NOT_AVAILABLE;

    #[Vich\UploadableField(mapping: 'videos', fileNameProperty: 'filename')]
    #[Assert\File(maxSize: '100m', mimeTypes: ['video/mp4'], extensions: ['mp4'])]
    private ?File $videoFile = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups(['videos:item:get'])]
    private ?string $filename = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    #[Groups(['videos:collection:get', 'videos:item:get'])]
    private int $views = 0;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $published = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $converted = false;


    #[ORM\Column(type: Types::STRING, options: ['default' => self::SLIDESHOW_EXTRACTION])]
    private string $extractionMethod = self::SLIDESHOW_EXTRACTION;

    #[ORM\Column(type: Types::JSON)]
    private array $metadata = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['videos:collection:get', 'videos:item:get'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'videos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'videos', fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $tags;

    #[ORM\OneToMany(mappedBy: 'video', targetEntity: Comment::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'video', targetEntity: Frame::class, orphanRemoval: true, fetch: 'EXTRA_LAZY', cascade: ['persist'])]
    private Collection $frames;


    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    #[Groups(['videos:item:get'])]
    private bool $allow_comments = true;

    #[ORM\OneToMany(mappedBy: 'video', targetEntity: Vote::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $votes;

    #[ORM\ManyToMany(targetEntity: Playlist::class, mappedBy: 'videos', fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $playlists;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->frames = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->playlists = new ArrayCollection();
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
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

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
        $this->slug = Slugger::slugify($this->title);
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    /**
     * @param string $thumbnail
     * @return $this
     */
    public function setThumbnail(string $thumbnail): static
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * @param string|null $filename
     * @return $this
     */
    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return int
     */
    public function getViews(): int
    {
        return $this->views;
    }

    /**
     * @param int $views
     * @return $this
     */
    public function setViews(int $views): static
    {
        $this->views = $views;

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return void
     */
    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable('now');
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
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function setCategory(Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    /**
     * @param Tag $tag
     * @return $this
     */
    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    /**
     * @param Tag $tag
     * @return $this
     */
    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @param Comment $comment
     * @return $this
     */
    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setVideo($this);
        }

        return $this;
    }

    /**
     * @param Comment $comment
     * @return $this
     */
    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getVideo() === $this) {
                $comment->setVideo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Frame>
     */
    public function getFrames(): Collection
    {
        return $this->frames;
    }

    public function addFrame(Frame $frame): static
    {
        if (!$this->frames->contains($frame)) {
            $this->frames->add($frame);
            $frame->setVideo($this);
        }

        return $this;
    }

    public function removeFrame(Frame $frame): static
    {
        if ($this->frames->removeElement($frame)) {
            // set the owning side to null (unless already changed)
            if ($frame->getVideo() === $this) {
                $frame->setVideo(null);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getExtractionMethod(): string
    {
        return $this->extractionMethod;
    }

    /**
     * @param string $extractionMethod
     * @return Video
     */
    public function setExtractionMethod(string $extractionMethod): static
    {
        $this->extractionMethod = $extractionMethod;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published;
    }

    /**
     * @param bool $published
     */
    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    /**
     * @return bool
     */
    public function isConverted(): bool
    {
        return $this->converted;
    }

    /**
     * @param bool $converted
     */
    public function setConverted(bool $converted): void
    {
        $this->converted = $converted;
    }

    /**
     * @param string|null $key
     * @return mixed
     */
    public function getMetadata(?string $key = null): mixed
    {
        if ($key !== null) {
            if (isset($this->metadata[$key])) {
                return $this->metadata[$key];
            }

            return null;
        }

        return $this->metadata;
    }

    /**
     * @param array $metadata
     */
    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    /**
     * @param User|null $user
     * @return bool
     */
    public function isOwner(?User $user = null): bool
    {
        return $user && $this->user->getId() === $user->getId();
    }

    public function getAllowComments(): ?bool
    {
        return $this->allow_comments;
    }

    public function setAllowComments(bool $allow_comments): static
    {
        $this->allow_comments = $allow_comments;

        return $this;
    }

    /**
     * @return File|null
     */
    public function getVideoFile(): ?File
    {
        return $this->videoFile;
    }

    /**
     * @param File|null $videoFile
     */
    public function setVideoFile(?File $videoFile): void
    {
        $this->videoFile = $videoFile;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->title;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setVideo($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getVideo() === $this) {
                $vote->setVideo(null);
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalVotes(): int
    {
        return $this->votes->count();
    }

    /**
     * @param string $type
     * @return int
     */
    public function getVotesByType(string $type): int
    {
        return $this->votes->filter(fn(Vote $vote) => $vote->getVote() === $type)->count();
    }

    /**
     * @return int
     */
    #[Groups(['videos:item:get'])]
    public function getVotesPercentage(): int
    {
        $total = $this->getTotalVotes();

        if ($total === 0) {
            return 0;
        }

        $upVotes = $this->getVotesByType(Vote::UP);

        return (int) (ceil($upVotes * 100) / $total);
    }

    /**
     * @param User|null $user
     * @return bool
     */
    public function hasVoted(?User $user = null): bool
    {
        return $user && $this->votes->exists(fn(int $key, Vote $vote) => $vote->getUser()->getId() === $user->getId());
    }

    /**
     * @return Collection<int, Playlist>
     */
    public function getPlaylists(): Collection
    {
        return $this->playlists;
    }

    public function addPlaylist(Playlist $playlist): static
    {
        if (!$this->playlists->contains($playlist)) {
            $this->playlists->add($playlist);
            $playlist->addVideo($this);
        }

        return $this;
    }

    public function removePlaylist(Playlist $playlist): static
    {
        if ($this->playlists->removeElement($playlist)) {
            $playlist->removeVideo($this);
        }

        return $this;
    }
}
