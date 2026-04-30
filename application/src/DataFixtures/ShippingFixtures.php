<?php

namespace App\DataFixtures;

use App\Entity\ShippingMethod;
use App\Entity\Zone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ShippingFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
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
        $shippingMethods = [
            'colissimo_fr' => [
                'name' => 'Colissimo (France)',
                'zone' => 'FR',
                'calculator' => 'flat_rate',
                'configuration' => ['amount' => 500],
            ],
            'colissimo_eu' => [
                'name' => 'Colissimo (Europe)',
                'zone' => 'EU',
                'calculator' => 'flat_rate',
                'configuration' => ['amount' => 1200],
            ],
            'world' => [
                'name' => 'International Shipping',
                'zone' => 'WORLD',
                'calculator' => 'flat_rate',
                'configuration' => ['amount' => 2500],
            ],
            'digital' => [
                'name' => 'Digital Delivery',
                'zone' => 'WORLD',
                'calculator' => 'flat_rate',
                'configuration' => ['amount' => 0],
            ],
        ];

        foreach ($shippingMethods as $code => $data) {
            $method = $manager->getRepository(ShippingMethod::class)->findOneBy(['code' => $code]);
            if (!$method) {
                $method = new ShippingMethod();
                $method->setCode($code);
                $manager->persist($method);
            }
            $method->setCurrentLocale('fr');
            $method->setFallbackLocale('fr');
            $method->setName($data['name']);
            $method->setZone($this->getReference('zone_' . $data['zone'], Zone::class));
            $method->setCalculator($data['calculator']);
            $method->setConfiguration($data['configuration']);
            $method->setEnabled(true);

            $this->addReference('shipping_method_' . $code, $method);
        }

        $manager->flush();
    }
}
