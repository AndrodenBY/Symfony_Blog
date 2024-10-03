<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Blog;
use App\Entity\Comment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('text')
            ->add('comments', EntityType::class, [
                'class' => Comment::class,
                'choice_label' => 'content',
                'multiple' => true, // Если нужно выбрать несколько комментариев
                'expanded' => true,  // Если хотите использовать радиокнопки или чекбоксы
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
