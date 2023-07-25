<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Class User
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email')]
#[Vich\Uploadable()]
#[ApiResource(
    types: ['https://schema.org/User'],
    operations: [
        new GetCollection(
            normalizationContext: [
                'groups' => [
                    'users:collection:get',
                ],
            ],
            order: [
                'lastname' => 'ASC',
                'firstname' => 'ASC',
            ]
        ),
        new Get(
            normalizationContext: [
                'groups' => [
                    'users:item:get',
                ],
            ]
        ),
        new Get(
            uriTemplate: '/videos/{id}/user',
            uriVariables: [
                'id' => new Link(
                    fromClass: Video::class,
                    fromProperty: 'user'
                ),
            ],
            normalizationContext: [
                'groups' => [
                    'users:item:get',
                ],
            ]
        ),
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Serializable
{
    public const ROLE_USER = 'ROLE_USER';

    public const ROLE_ADMIN = 'ROLE_ADMIN';

    public const GENERIC_AVATAR = 'generic-avatar.png';

    /**
     * @var int|null $id
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['users:item:get', 'users:collection:get', 'comments:collection:get'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Assert\Length(max: 180)]
    #[Groups(['users:item:get', 'users:collection:get', 'comments:collection:get'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roles = [];

    /**
     * @var string|null $password
     */
    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    private ?string $password = null;

    /**
     * @var string $firstname
     */
    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    private string $firstname;

    /**
     * @var string $lastname
     */
    #[ORM\Column(type: Types::STRING)]
    #[Assert\NotBlank]
    private string $lastname;

    /**
     * @var string $avatar
     */
    #[ORM\Column(type: Types::STRING, options: ['default' => self::GENERIC_AVATAR])]
    #[Groups(['users:item:get', 'users:collection:get', 'comments:collection:get'])]
    private string $avatar = self::GENERIC_AVATAR;

    #[Vich\UploadableField(mapping: 'avatars', fileNameProperty: 'avatar')]
    #[Assert\File(maxSize: '2m', mimeTypes: ['image/jpeg', 'image/png'], extensions: ['jpg', 'jpeg', 'png'])]
    private ?File $avatarFile = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Video::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $videos;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['createdAt' => 'DESC'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Playlist::class, orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $playlists;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $can_upload = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $can_comment = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $blocked = false;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Vote::class, orphanRemoval: true)]
    private Collection $votes;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'mySubscribers')]
    private Collection $mySubscriptions;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'mySubscriptions')]
    #[ORM\JoinTable(name: 'subscriptions')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'subscriber_user_id', referencedColumnName: 'id')]
    private Collection $mySubscribers;

    public function __construct()
    {
        $this->videos = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->playlists = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->mySubscriptions = new ArrayCollection();
        $this->mySubscribers = new ArrayCollection();

    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     *
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = self::ROLE_USER;

        return array_unique($roles);
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return string
     */
    public function getAvatar(): string
    {
        return $this->avatar;
    }

    /**
     * @param string $avatar
     * @return $this
     */
    public function setAvatar(string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $firstname
     * @return User
     */
    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $lastname
     * @return User
     */
    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    #[Groups(['users:item:get', 'users:collection:get', 'comments:collection:get'])]
    public function getFullname(): string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * @return string
     */
    public function getInitials(): string
    {
        return strtoupper($this->firstname[0] . $this->lastname[0]);
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }

    /**
     * @param Video $video
     * @return $this
     */
    public function addVideo(Video $video): static
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setUser($this);
        }

        return $this;
    }

    /**
     * @param Video $video
     * @return $this
     */
    public function removeVideo(Video $video): static
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getUser() === $this) {
                $video->setUser(null);
            }
        }

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
            $comment->setUser($this);
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
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @param bool|null $private
     * @return Collection<int, Playlist>
     */
    public function getPlaylists(?bool $private = false): Collection
    {
        if ($private === null) {
            return $this->playlists;
        }

        return $this->playlists->filter(fn(Playlist $playlist) => $playlist->isPrivate() === $private);
    }

    /**
     * @param Playlist $playlist
     * @return $this
     */
    public function addPlaylist(Playlist $playlist): static
    {
        if (!$this->playlists->contains($playlist)) {
            $this->playlists->add($playlist);
            $playlist->setUser($this);
        }

        return $this;
    }

    /**
     * @param Playlist $playlist
     * @return $this
     */
    public function removePlaylist(Playlist $playlist): static
    {
        if ($this->playlists->removeElement($playlist)) {
            // set the owning side to null (unless already changed)
            if ($playlist->getUser() === $this) {
                $playlist->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return File|null
     */
    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    /**
     * @param File|null $avatarFile
     */
    public function setAvatarFile(?File $avatarFile): void
    {
        $this->avatarFile = $avatarFile;
    }

    public function serialize()
    {
        // TODO: Implement serialize() method.
    }

    public function unserialize(string $data)
    {
        // TODO: Implement unserialize() method.
    }

    /**
     * @return array
     */
    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }

    /**
     * @param array $data
     * @return void
     */
    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->email = $data['email'];
        $this->password = $data['password'];
    }

    public function getCanUpload(): ?bool
    {
        return $this->can_upload;
    }

    public function setCanUpload(bool $can_upload): static
    {
        $this->can_upload = $can_upload;

        return $this;
    }

    public function getCanComment(): ?bool
    {
        return $this->can_comment;
    }

    public function setCanComment(bool $can_comment): static
    {
        $this->can_comment = $can_comment;

        return $this;
    }

    public function isBlocked(): ?bool
    {
        return $this->blocked;
    }

    public function setBlocked(bool $blocked): static
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getFullname();
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
            $vote->setUser($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getUser() === $this) {
                $vote->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function getSubscriptions(): Collection
    {
        return $this->mySubscriptions;
    }

    /**
     * @param User $user
     * @return void
     */
    public function addSubscription(User $user): void
    {
        if  (!$this->mySubscriptions->contains($user)) {
            $this->mySubscriptions->add($user);

            $user->addSubscriber($this);
        }
    }

    /**
     * @param User $user
     * @return void
     */
    public function removeSubscription(User $user): void
    {
        if ($this->mySubscriptions->contains($user)) {
            $this->mySubscriptions->removeElement($user);
        }

        $user->removeSubscriber($this);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function hasSubscription(User $user): bool
    {
        return $this->mySubscriptions->contains($user);
    }

    /**
     * @return Collection
     */
    public function getSubscribers(): Collection
    {
        return $this->mySubscribers;
    }

    /**
     * @param User $user
     * @return void
     */
    public function addSubscriber(User $user): void
    {
        if (!$this->mySubscribers->contains($user)) {
            $this->mySubscribers->add($user);

            $user->addSubscription($this);
        }
    }

    /**
     * @param User $user
     * @return void
     */
    public function removeSubscriber(User $user): void
    {
        if ($this->mySubscribers->contains($user)) {
            $this->mySubscribers->removeElement($user);
        }
    }

    /**
     * @param User $user
     * @return bool
     */
    public function hasSubscriber(User $user): bool
    {
        return $this->mySubscribers->contains($user);
    }
}
