<?php

namespace App\DataFixtures;

use App\Entity\TaxCategory;
use App\Entity\TaxRate;
use App\Entity\Zone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TaxFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['base'];
    }

    public function getDependencies(): array
    {
        return [
            BaseFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $taxCategories = [
            'standard' => [
                'name' => 'Taux Standard (20%)',
                'album' => true,
                'track' => false,
                'merch' => true,
                'rates' => [
                    'FR' => 0.20,
                    'EU' => 0.20,
                ],
            ],
            'reduced' => [
                'name' => 'Taux Réduit (5.5%)',
                'album' => false,
                'track' => false,
                'merch' => false,
                'rates' => [
                    'FR' => 0.055,
                ],
            ],
            'digital' => [
                'name' => 'Taux Numérique (20%)',
                'album' => false,
                'track' => true,
                'merch' => false,
                'rates' => [
                    'FR' => 0.20,
                    'EU' => 0.20,
                ],
            ],
        ];

        foreach ($taxCategories as $code => $data) {
            $category = $manager->getRepository(TaxCategory::class)->findOneBy(['code' => $code]);
            if (!$category) {
                $category = new TaxCategory();
                $category->setCode($code);
                $manager->persist($category);
            }
            $category->setName($data['name']);
            $category->setDefaultForAlbum($data['album']);
            $category->setDefaultForTrack($data['track']);
            $category->setDefaultForMerch($data['merch']);
            $this->addReference('tax_category_' . $code, $category);

            foreach ($data['rates'] as $zoneCode => $amount) {
                $rateCode = $code . '_' . $zoneCode;
                $rate = $manager->getRepository(TaxRate::class)->findOneBy(['code' => $rateCode]);
                if (!$rate) {
                    $rate = new TaxRate();
                    $rate->setCode($rateCode);
                    $manager->persist($rate);
                }
                $rate->setName($data['name'] . ' - ' . $zoneCode);
                $rate->setAmount((float) $amount);
                $rate->setIncludedInPrice(true);
                $rate->setCalculator('default');
                $rate->setCategory($category);
                $rate->setZone($this->getReference('zone_' . $zoneCode, Zone::class));
            }
        }

        $manager->flush();
    }
}
