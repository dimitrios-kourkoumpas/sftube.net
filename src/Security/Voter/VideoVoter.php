<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Video;
use App\Service\Configurations;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class VideoVoter
 * @package App\Security\Voter
 */
final class VideoVoter extends Voter
{
    public const EDIT = 'edit';

    public const DELETE = 'delete';

    public const COMMENT = 'comment';

    public const UPLOAD = 'upload';

    /**
     * @param Configurations $configurations
     */
    public function __construct(private readonly Configurations $configurations)
    {
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::UPLOAD) {
            return true;
        }

        return in_array($attribute, [self::EDIT, self::DELETE, self::COMMENT]) && $subject instanceof Video;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::UPLOAD => $this->configurations->isSet('allow-video-uploads')
                && $this->configurations->get('allow-video-uploads')
                && $user->getCanUpload(),
            self::EDIT, self::DELETE => $subject->isOwner($user),
            self::COMMENT => $this->configurations->isSet('allow-video-comments')
                && $this->configurations->get('allow-video-comments')
                && $subject->getAllowComments()
                && $user->getCanComment(),
            default => false,
        };
    }
}
