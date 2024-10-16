<?php

namespace App\Entity;

use App\Repository\BlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DomCrawler\Image;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: BlogRepository::class)]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $title = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text = null;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'blogs',)]
    #[ORM\Column(nullable: true)]
    #[ORM\JoinTable(name: 'blog_categories')]
    private ?Collection $categories;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $publishedAt = null;

    #[ORM\Column(nullable: true)]
    private ?string $image = 'http://www.listercarterhomes.com/wp-content/uploads/2013/11/dummy-image-square.jpg';

    #[ORM\ManyToOne(inversedBy: 'blogs')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    /**
     * @var ?Collection<int, Comment>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'blog')]
    #[ORM\Column(nullable: true)]
    private ?Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }
    public function setPublishedAt(?\DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function setCategories(Collection $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return ?Collection<int, Comment>
     */
    public function getComments(): ?Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setBlog($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getBlog() === $this) {
                $comment->setBlog(null);
            }
        }

        return $this;
    }
}
