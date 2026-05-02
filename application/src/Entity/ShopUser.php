<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ShopUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ShopUser as BaseShopUser;

#[ORM\Entity(repositoryClass: ShopUserRepository::class)]
#[ORM\Table(name: 'sylius_shop_user')]
class ShopUser extends BaseShopUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $username;

    #[ORM\Column(type: 'string', length: 255, nullable: true, unique: true)]
    protected $usernameCanonical;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $password;

    #[ORM\Column(type: 'boolean')]
    protected $enabled = true;

    #[ORM\Column(type: 'json')]
    protected $roles = ['ROLE_USER'];

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $updatedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $lastLogin;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $emailVerificationToken;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $passwordResetToken;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $passwordRequestedAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $verifiedAt;

    #[ORM\ManyToOne(targetEntity: Customer::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'customer_id', nullable: true, onDelete: 'SET NULL')]
    protected $customer;
    // Do NOT annotate: $email, $emailCanonical (delegated to Customer), $plainPassword, $oauthAccounts
}
