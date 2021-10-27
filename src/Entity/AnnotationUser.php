<?php

namespace App\Entity;

use App\Repository\AnnotationUserRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

#[Entity(repositoryClass: AnnotationUserRepository::class)]
class AnnotationUser
{
    #[Id]
    #[GeneratedValue(strategy: 'UUID')]
    #[Column(type: 'guid')]
    private $id;

    #[ManyToOne(targetEntity: Annotation::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $annotation;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private $user;

    #[Column(type: 'boolean', nullable: true)]
    private $isRead;

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getAnnotations(): ?Annotation
    {
        return $this->annotations;
    }

    public function setAnnotations(?Annotation $annotations): self
    {
        $this->annotations = $annotations;

        return $this;
    }

    public function getUsers(): ?User
    {
        return $this->users;
    }

    public function setUsers(?User $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(?bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getAnnotation(): Annotation
    {
        return $this->annotation;
    }

    public function setAnnotation(Annotation $annotation): self
    {
        $this->annotation = $annotation;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
