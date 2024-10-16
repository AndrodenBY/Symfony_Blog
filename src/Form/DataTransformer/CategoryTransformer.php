<?php

namespace App\Form\DataTransformer;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\CallbackTransformer;

class CategoryTransformer implements DataTransformerInterface
{
    private EntityManagerInterface $entityManager;
    private CategoryRepository $categoryRepository;

    public function __construct(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository)
    {
        $this->entityManager = $entityManager;
        $this->categoryRepository = $categoryRepository;
    }

    public function transform($categories): string
    {
        if ($categories instanceof ArrayCollection) {
            $categories = $categories->toArray();
        }

        if (null === $categories || empty($categories)) {
            return '';
        }

        $ids = [];
        foreach ($categories as $category) {
            if ($category instanceof Category) {
                $ids[] = $category->getId();
            }
        }

        return implode(',', $ids);
    }



    public function reverseTransform($categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }

        $idsArray = explode(',', $categoryIds);
        $categories = $this->categoryRepository->findBy(['id' => $idsArray]);

        if (count($categories) !== count($idsArray)) {
            throw new TransformationFailedException('Some categories could not be found.');
        }

        return $categories;
    }
}
