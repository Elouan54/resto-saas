<?php

namespace App\Entity;

use App\Repository\OpeningHoursRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OpeningHoursRepository::class)]
class OpeningHours
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $dayOfWeek = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $openingTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $closingTime = null;

    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'openingHours')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Restaurant $restaurant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(int $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }

    public function getOpeningTime(): ?\DateTime
    {
        return $this->openingTime;
    }

    public function setOpeningTime(\DateTime $openingTime): static
    {
        $this->openingTime = $openingTime;
        return $this;
    }

    public function getClosingTime(): ?\DateTime
    {
        return $this->closingTime;
    }

    public function setClosingTime(\DateTime $closingTime): static
    {
        $this->closingTime = $closingTime;
        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;
        return $this;
    }
}