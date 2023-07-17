<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Playlist;
use App\Entity\Video;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PlaylistType
 * @package App\Form
 */
final class PlaylistType extends AbstractType
{
    /**
     * @param Security $security
     * @param TranslatorInterface $translator
     */
    public function __construct(private readonly Security $security, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->security->getUser();

        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => $this->translator->trans('form.playlist.label.name'),
            ])
            ->add('private', CheckboxType::class, [
                'required' => false,
                'label' => $this->translator->trans('form.playlist.label.private'),
            ])
            ->add('videos', EntityType::class, [
                'label' => $this->translator->trans('form.playlist.label.videos'),
                'required' => false,
                'multiple' => true,
                'class' => Video::class,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('v')
                    ->where('v.user = :user')
                    ->setParameter('user', $user)
                    ->orderBy('v.createdAt', 'DESC'),
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Playlist::class,
        ]);
    }
}
