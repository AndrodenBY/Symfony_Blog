<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Blog;
use App\Entity\Comment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('likes', null, [
                'required' => false,
            ])
            ->add('content')
            ->add('blog', EntityType::class, [
                'class' => Blog::class,
                'choice_label' => 'title',
                'attr' => ['style' => 'display:none;'],
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'attr' => ['style' => 'display:none;'],
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
