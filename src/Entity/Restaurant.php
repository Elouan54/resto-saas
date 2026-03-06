<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
class Restaurant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bannerImage = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(length: 20)]
    private ?string $primaryColor = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $fontFamily = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updateAt = null;

    // ===== Relations =====

    #[ORM\ManyToMany(targetEntity: RestaurantCategory::class, inversedBy: 'restaurants')]
    private Collection $restaurantCategories;

    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: Category::class, cascade: ['persist', 'remove'])]
    private Collection $categories;

    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: Dish::class, cascade: ['persist', 'remove'])]
    private Collection $dishes;

    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: OpeningHours::class, cascade: ['persist', 'remove'])]
    private Collection $openingHours;

    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: ExceptionalClosure::class, cascade: ['persist', 'remove'])]
    private Collection $exceptionalClosures;

    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: GalleryImage::class, cascade: ['persist', 'remove'])]
    private Collection $galleryImages;

    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: ContactMessage::class, cascade: ['persist', 'remove'])]
    private Collection $contactMessages;

    #[ORM\OneToMany(mappedBy: 'restaurant', targetEntity: User::class)]
    private Collection $users;

    public function __construct()
    {
        $this->restaurantCategories = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->dishes = new ArrayCollection();
        $this->openingHours = new ArrayCollection();
        $this->exceptionalClosures = new ArrayCollection();
        $this->galleryImages = new ArrayCollection();
        $this->contactMessages = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->isActive = true;
        $this->createdAt = new \DateTime();
        $this->updateAt = new \DateTime();
    }

    // ===== Getters & Setters =====

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getBannerImage(): ?string
    {
        return $this->bannerImage;
    }

    public function setBannerImage(?string $bannerImage): static
    {
        $this->bannerImage = $bannerImage;
        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;
        return $this;
    }

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function setPrimaryColor(string $primaryColor): static
    {
        $this->primaryColor = $primaryColor;
        return $this;
    }

    public function getFontFamily(): ?string
    {
        return $this->fontFamily;
    }

    public function setFontFamily(?string $fontFamily): static
    {
        $this->fontFamily = $fontFamily;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdateAt(): ?\DateTime
    {
        return $this->updateAt;
    }

    public function setUpdateAt(\DateTime $updateAt): static
    {
        $this->updateAt = $updateAt;
        return $this;
    }

    // ===== Relations Collections =====
    
    // RestaurantCategories
    public function getRestaurantCategories(): Collection
    {
        return $this->restaurantCategories;
    }

    public function addRestaurantCategory(RestaurantCategory $category): static
    {
        if (!$this->restaurantCategories->contains($category)) {
            $this->restaurantCategories->add($category);
        }
        return $this;
    }

    public function removeRestaurantCategory(RestaurantCategory $category): static
    {
        $this->restaurantCategories->removeElement($category);
        return $this;
    }

    // Categories
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setRestaurant($this);
        }
        return $this;
    }

    public function removeCategory(Category $category): static
    {
        if ($this->categories->removeElement($category)) {
            $category->setRestaurant(null);
        }
        return $this;
    }

    // Dishes
    public function getDishes(): Collection
    {
        return $this->dishes;
    }

    public function addDish(Dish $dish): static
    {
        if (!$this->dishes->contains($dish)) {
            $this->dishes->add($dish);
            $dish->setRestaurant($this);
        }
        return $this;
    }

    public function removeDish(Dish $dish): static
    {
        if ($this->dishes->removeElement($dish)) {
            $dish->setRestaurant(null);
        }
        return $this;
    }

    // OpeningHours
    public function getOpeningHours(): Collection
    {
        return $this->openingHours;
    }

    public function addOpeningHour(OpeningHours $hour): static
    {
        if (!$this->openingHours->contains($hour)) {
            $this->openingHours->add($hour);
            $hour->setRestaurant($this);
        }
        return $this;
    }

    public function removeOpeningHour(OpeningHours $hour): static
    {
        if ($this->openingHours->removeElement($hour)) {
            $hour->setRestaurant(null);
        }
        return $this;
    }

    // ExceptionalClosures
    public function getExceptionalClosures(): Collection
    {
        return $this->exceptionalClosures;
    }

    public function addExceptionalClosure(ExceptionalClosure $closure): static
    {
        if (!$this->exceptionalClosures->contains($closure)) {
            $this->exceptionalClosures->add($closure);
            $closure->setRestaurant($this);
        }
        return $this;
    }

    public function removeExceptionalClosure(ExceptionalClosure $closure): static
    {
        if ($this->exceptionalClosures->removeElement($closure)) {
            $closure->setRestaurant(null);
        }
        return $this;
    }

    // GalleryImages
    public function getGalleryImages(): Collection
    {
        return $this->galleryImages;
    }

    public function addGalleryImage(GalleryImage $image): static
    {
        if (!$this->galleryImages->contains($image)) {
            $this->galleryImages->add($image);
            $image->setRestaurant($this);
        }
        return $this;
    }

    public function removeGalleryImage(GalleryImage $image): static
    {
        if ($this->galleryImages->removeElement($image)) {
            $image->setRestaurant(null);
        }
        return $this;
    }

    // ContactMessages
    public function getContactMessages(): Collection
    {
        return $this->contactMessages;
    }

    public function addContactMessage(ContactMessage $message): static
    {
        if (!$this->contactMessages->contains($message)) {
            $this->contactMessages->add($message);
            $message->setRestaurant($this);
        }
        return $this;
    }

    public function removeContactMessage(ContactMessage $message): static
    {
        if ($this->contactMessages->removeElement($message)) {
            $message->setRestaurant(null);
        }
        return $this;
    }

    // Users
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setRestaurant($this);
        }
        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->setRestaurant(null);
        }
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;
        return $this;
    }
}