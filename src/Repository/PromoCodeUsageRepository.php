<?php

namespace App\Repository;

use App\Entity\PromoCode;
use App\Entity\PromoCodeUsage;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PromoCodeUsageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PromoCodeUsage::class);
    }

    public function hasUserUsedPromo(User $user, PromoCode $promo): bool
    {
        return (bool) $this->findOneBy([
            'user' => $user,
            'promoCode' => $promo,
        ]);
    }
}