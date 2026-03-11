<?php

namespace App\Service;

use App\Entity\PromoCode;
use Doctrine\ORM\EntityManagerInterface;

class PromoCodeService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function generateNewsletterCode(): PromoCode
    {
        $promo = new PromoCode();
        $promo->setCode('HELLO-' . strtoupper(bin2hex(random_bytes(3))));
        $promo->setDiscountType('percentage');
        $promo->setDiscountValue('10');
        $promo->setMaxUses(1);
        $promo->setIsActive(true);
        $promo->setApplicableTo('both');
        $promo->setValidFrom(new \DateTime());
        $promo->setValidUntil(new \DateTime('+30 days'));

        $this->em->persist($promo);
        $this->em->flush();

        return $promo;
    }

    public function generateBirthdayCode(): PromoCode
    {
        $promo = new PromoCode();
        $promo->setCode('ANNIV-' . strtoupper(bin2hex(random_bytes(3))));
        $promo->setDiscountType('percentage');
        $promo->setDiscountValue('10');
        $promo->setMaxUses(1);
        $promo->setIsActive(true);
        $promo->setApplicableTo('both');
        $promo->setValidFrom(new \DateTime());
        $promo->setValidUntil(new \DateTime('+30 days'));

        $this->em->persist($promo);
        $this->em->flush();

        return $promo;
    }
}