<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Category;
use App\Entity\Playlist;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Video;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

/**
 * Class VideoType
 * @package App\Form
 */
final class VideoType extends AbstractType
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
        /** @var User $user */
        $user = $this->security->getUser();

        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'label' => $this->translator->trans('video.type.label.title'),
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => $this->translator->trans('video.type.label.description'),
            ])
            ->add('videoFile', VichFileType::class, [
                'required' => true,
                'allow_delete' => false,
                'download_uri' => false,
                'label' => $this->translator->trans('video.type.label.video'),
            ])
            ->add('extractionMethod', ChoiceType::class, [
                'choices' => [
                    $this->translator->trans('video.type.label.extraction-method.slideshow') => Video::SLIDESHOW_EXTRACTION,
                    $this->translator->trans('video.type.label.extraction-method.preview') => Video::PREVIEW_EXTRACTION,
                ],
                'label' => $this->translator->trans('video.type.label.extraction-method'),
            ])
            ->add('allow_comments', CheckboxType::class, [
                'data' => true,
                'label' => $this->translator->trans('video.type.label.allow-comments'),
            ])
            ->add('category', EntityType::class, [
                'label' => $this->translator->trans('video.type.label.category'),
                'required' => true,
                'class' => Category::class,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('c')->orderBy('c.name', 'ASC'),
            ])
            ->add('tags', EntityType::class, [
                'label' => $this->translator->trans('video.type.label.tags'),
                'required' => false,
                'multiple' => true,
                'class' => Tag::class,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('t')->orderBy('t.name', 'ASC'),
            ])
            ->add('playlists', EntityType::class, [
                'label' => $this->translator->trans('video.type.label.playlists'),
                'required' => false,
                'multiple' => true,
                'class' => Playlist::class,
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('p')
                    ->where('p.user = :user')
                    ->setParameter('user', $user)
                    ->orderBy('p.name', 'ASC'),
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
            'data_class' => Video::class,
        ]);
    }
}
