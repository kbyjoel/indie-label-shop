<?php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\Zone;
use App\Entity\ZoneMember;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Intl\Countries;

class BaseFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['base'];
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Define default zones
        $zones = [
            'EU' => [
                'name' => 'Union Européenne',
                'type' => 'country',
                'members' => ['FR', 'DE', 'IT', 'ES', 'BE', 'NL', 'LU', 'IE', 'AT', 'PT', 'FI', 'SE', 'DK', 'GR'],
            ],
            'FR' => [
                'name' => 'France',
                'type' => 'country',
                'members' => ['FR'],
            ],
            'WORLD' => [
                'name' => 'Reste du monde',
                'type' => 'country',
                'members' => [], // Empty by default or all others
            ],
        ];

        // 2. Collect all member codes from zones to enable them
        $codesToEnable = [];
        foreach ($zones as $zoneData) {
            foreach ($zoneData['members'] as $memberCode) {
                $codesToEnable[$memberCode] = true;
            }
        }

        // 3. Import all countries from Symfony Intl
        $countries = Countries::getNames('fr');
        $countryEntities = [];

        foreach ($countries as $code => $name) {
            $country = new Country();
            $country->setCode($code);
            $country->setEnabled(isset($codesToEnable[$code])); // Enabled if part of a zone

            $manager->persist($country);
            $countryEntities[$code] = $country;
        }

        // 4. Create zones in DB
        foreach ($zones as $code => $data) {
            $zone = new Zone();
            $zone->setCode($code);
            $zone->setName($data['name']);
            $zone->setType($data['type']);
            $zone->setScope('all');
            $manager->persist($zone);
            $this->addReference('zone_' . $code, $zone);

            foreach ($data['members'] as $memberCode) {
                if (isset($countryEntities[$memberCode])) {
                    $member = new ZoneMember();
                    $member->setCode($memberCode);
                    $member->setBelongsTo($zone);
                    $manager->persist($member);
                }
            }
        }

        $manager->flush();
    }
}
