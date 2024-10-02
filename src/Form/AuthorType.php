<?php

namespace App\Form;

use App\Entity\Author;
use App\Entity\Blog;
use App\Repository\BlogRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('email')
            ->add('password')
            ->add('blogs', EntityType::class, [
                'class' => Blog::class,
                'choice_label' => 'title',
                'multiple' => true, // Если нужно выбрать несколько блогов
                'expanded' => true,  // Если хотите использовать радиокнопки или чекбоксы
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Author::class,
        ]);
    }
}
