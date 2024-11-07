<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\User;
use App\Entity\Blog;
use App\Entity\Comment;
use App\Form\DataTransformer\CategoryTransformer;
use App\Repository\BlogRepository;
use App\Repository\CategoryRepository;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType as SymfonyTextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class BlogType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', SymfonyTextType::class, [
                'constraints' => [new NotBlank(['message' => 'Title is required'])],
            ])
            ->add('description', SymfonyTextType::class, [
                'constraints' => [new NotBlank(['message' => 'Description is required'])],
            ])
            ->add('text', SymfonyTextType::class, [
                'constraints' => [new NotBlank(['message' => 'Text is required'])],
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Category',
                'placeholder' => 'Select a category',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '2000k',
                         'mimeTypes' => [
                             'image/jpg',
                             'image/jpeg',
                             'image/png',
                             'image/webp '
                         ]
                    ])],
                'required' => false,
                'label' => 'Upload Image',
                'attr' => ['accept' => 'image/*'],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'attr' => ['style' => 'display:none;'],
                'mapped' => false,
            ]);
    }

public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
            'categories' => [],
        ]);
    }
}
