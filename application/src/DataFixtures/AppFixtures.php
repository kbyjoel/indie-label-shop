<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Album;
use App\Entity\Artist;
use App\Entity\Band;
use App\Entity\BandTranslation;
use App\Entity\Channel;
use App\Entity\Currency;
use App\Entity\Customer;
use App\Entity\Locale;
use App\Entity\Media;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\ProductOption;
use App\Entity\ProductOptionTranslation;
use App\Entity\ProductOptionValue;
use App\Entity\ProductOptionValueTranslation;
use App\Entity\ProductTranslation;
use App\Entity\ProductVariant;
use App\Entity\Release;
use App\Entity\Track;
use App\Entity\Tracklist;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['dev'];
    }
    private const INDIE_BANDS = [
        'The Psychotic Monks', 'Lysistrata', 'Slift', 'Frustration', 'La Femme',
        'Flavien Berger', 'Bryan\'s Magic Tears', 'Decibelles', 'Johnny Mafia', 'Pogo Car Crash Control',
        'Petit Fantôme', 'Odezenne', 'Grand Blanc', 'Bagarrre', 'Agar Agar',
        'MNNQNS', 'Structures', 'Working Men\'s Club', 'Dry Cleaning', 'Squid',
        'Black Country, New Road', 'Porridge Radio', 'Crack Cloud', 'Bodega', 'The Murder Capital',
        'Fontaines D.C.', 'Shame', 'IDLES', 'Viagra Boys', 'Preoccupations'
    ];

    private const MEDIA_NAMES = ['Vinyl 12"', 'CD', 'Cassette', 'Digital', 'Vinyl 7"'];

    private const MERCH_TYPES = [
        'T-shirt' => ['options' => ['size', 'color'], 'price' => [20, 30]],
        'Tote bag' => ['options' => ['color'], 'price' => [10, 15]],
        'Poster' => ['options' => ['size'], 'price' => [5, 15]],
        'Hoodie' => ['options' => ['size', 'color'], 'price' => [40, 55]],
        'Cap' => ['options' => ['color'], 'price' => [15, 25]],
    ];

    private const MERCH_OPTIONS = [
        'size' => [
            'name' => 'Taille',
            'values' => [
                'S' => 'S',
                'M' => 'M',
                'L' => 'L',
                'XL' => 'XL',
                'XXL' => 'XXL',
                'A3' => 'A3',
                'A2' => 'A2',
                '50x70' => '50x70',
            ]
        ],
        'color' => [
            'name' => 'Couleur',
            'values' => [
                'RED' => 'Rouge',
                'GREEN' => 'Vert',
                'BLUE' => 'Bleu',
                'BLACK' => 'Noir',
                'WHITE' => 'Blanc',
            ]
        ]
    ];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // 5. Create default Locale (French)
        $locale = new Locale();
        $locale->setCode("fr");
        $manager->persist($locale);
        $this->addReference("locale_fr", $locale);

        // 6. Create default Currency (Euro)
        $currency = new Currency();
        $currency->setCode("EUR");
        $manager->persist($currency);
        $this->addReference("currency_eur", $currency);

        // 1. Create Channel (Sylius needs it)
        $channel = new Channel();
        $channel->setCode('WEB');
        $channel->setName('Web Store');
        $channel->setHostname('localhost');
        $channel->setDefaultLocale($locale);
        $channel->setBaseCurrency($currency);
        $manager->persist($channel);

        // 1.5 Create Product Options
        $options = [];
        foreach (self::MERCH_OPTIONS as $code => $data) {
            $option = new ProductOption();
            $option->setCode($code);
            $option->setCurrentLocale('fr');
            $option->setFallbackLocale('fr');

            $optionTranslation = new ProductOptionTranslation();
            $optionTranslation->setLocale('fr');
            $optionTranslation->setName($data['name']);
            $option->addTranslation($optionTranslation);
            $manager->persist($optionTranslation);

            foreach ($data['values'] as $valCode => $valName) {
                $optionValue = new ProductOptionValue();
                $optionValue->setCode($valCode);
                $optionValue->setCurrentLocale('fr');
                $optionValue->setFallbackLocale('fr');

                $optionValueTranslation = new ProductOptionValueTranslation();
                $optionValueTranslation->setLocale('fr');
                $optionValueTranslation->setValue($valName);
                $optionValue->addTranslation($optionValueTranslation);
                $manager->persist($optionValueTranslation);

                $option->addValue($optionValue);
                $manager->persist($optionValue);
            }

            $manager->persist($option);
            $options[$code] = $option;
        }

        // 2. Create Medias
        $medias = [];
        foreach (self::MEDIA_NAMES as $name) {
            $media = new Media();
            $media->setName($name);
            $media->setIsDigital($name === 'Digital');
            $manager->persist($media);
            $medias[] = $media;
        }

        // 3. Create Artists
        $artists = [];
        for ($i = 0; $i < 20; $i++) {
            $artist = new Artist();
            $artist->setFirstName($faker->firstName);
            $artist->setLastName($faker->lastName);
            $artist->setEmail($faker->unique()->safeEmail);
            $artist->setAddress($faker->address);
            $artist->setZipCode($faker->postcode);
            $artist->setCity($faker->city);
            $artist->setPhone($faker->phoneNumber);
            $manager->persist($artist);
            $artists[] = $artist;
        }

        // 4. Create Bands
        $bands = [];
        foreach (self::INDIE_BANDS as $bandName) {
            $band = new Band();
            $band->setName($bandName);
            $band->setSlug($faker->slug);
            $band->setStatus('online');
            $band->setEmail($faker->safeEmail);
            $band->setWebsite($faker->url);

            // Add some random members
            $randomArtistsIndices = (array) array_rand($artists, rand(1, min(4, count($artists))));
            foreach ($randomArtistsIndices as $index) {
                $band->addMember($artists[$index]);
            }

            // Translations
            $translationEn = new BandTranslation('en', 'description', $faker->paragraph);
            $translationFr = new BandTranslation('fr', 'description', $faker->paragraph);
            $band->addTranslation($translationEn);
            $band->addTranslation($translationFr);

            $manager->persist($band);
            $bands[] = $band;
        }

        // 5. Create Albums & Releases
        $allReleases = [];
        $allTracks = [];
        foreach ($bands as $band) {
            $numAlbums = rand(1, 2);
            for ($i = 0; $i < $numAlbums; $i++) {
                $album = new Album();
                $album->setCurrentLocale('fr');
                $album->setFallbackLocale('fr');
                $albumTitle = implode(' ', (array) $faker->words(rand(1, 4), true));
                $album->setName(ucfirst($albumTitle));
                $album->setCode(strtoupper(str_replace(' ', '_', $albumTitle)) . '_' . $faker->unique()->numberBetween(100, 9999));
                $album->setBand($band);
                $album->addChannel($channel);
                $album->setStatus('online');
                $album->setReleaseDate($faker->dateTimeBetween('-10 years', 'now'));
                $album->setCatalogNumber('CAT-' . $faker->unique()->numberBetween(1000, 99999));

                // Add some artists to the album
                $albumArtistsIndices = (array) array_rand($artists, rand(1, min(3, count($artists))));
                foreach ($albumArtistsIndices as $index) {
                    $album->addArtist($artists[$index]);
                }

                $manager->persist($album);

                // Tracks for the album
                $numTracks = rand(8, 12);
                $tracks = [];
                for ($j = 1; $j <= $numTracks; $j++) {
                    $track = new Track();
                    $track->setCurrentLocale('fr');
                    $track->setFallbackLocale('fr');
                    $trackTitle = ucfirst(implode(' ', (array) $faker->words(rand(1, 4), true)));
                    $track->setTitle($trackTitle);
                    $track->setName($trackTitle);
                    $track->setCode($album->getCode() . '_TRK_' . $j);
                    $track->setPosition($j);
                    $track->setDuration(rand(2, 5) . ':' . sprintf('%02d', rand(0, 59)));
                    $track->setProduct($album);
                    $manager->persist($track);
                    $allTracks[] = $track;

                    $tracklist = new Tracklist();
                    $tracklist->setAlbum($album);
                    $tracklist->setTrack($track);
                    $tracklist->setPosition($j);
                    $manager->persist($tracklist);
                    $tracks[] = $tracklist;
                }

                // Releases (Variants)
                $numReleases = rand(1, 2);
                $shuffledMedias = $medias;
                shuffle($shuffledMedias);

                for ($k = 0; $k < $numReleases; $k++) {
                    $release = new Release();
                    $release->setCurrentLocale('fr');
                    $release->setFallbackLocale('fr');
                    $media = $shuffledMedias[$k];
                    $release->setAlbum($album);
                    $release->setMedia($media);
                    $release->setTitle($album->getName() . ' - ' . $media->getName());
                    $release->setName($release->getTitle());
                    $release->setCode($album->getCode() . '_' . strtoupper(str_replace(' ', '_', $media->getName() ?? '')));
                    $release->setPrice((int) round($faker->randomFloat(2, 10, 35) * 100));
                    $release->setStatus('online');
                    $release->setOnHand(rand(0, 100));

                    // Link tracklist to release
                    foreach ($tracks as $tracklistItem) {
                        $release->addTracklist($tracklistItem);
                    }

                    $manager->persist($release);
                    $allReleases[] = $release;
                }
            }
        }

        // 6. Create Merch Products
        $allMerchVariants = [];
        foreach ($bands as $band) {
            $numMerch = rand(1, 3);
            $merchTypes = array_keys(self::MERCH_TYPES);
            shuffle($merchTypes);

            for ($i = 0; $i < $numMerch; $i++) {
                $type = $merchTypes[$i];
                $config = self::MERCH_TYPES[$type];

                $product = new Product();
                $product->setCurrentLocale('fr');
                $product->setFallbackLocale('fr');
                $productName = $type . ' ' . $band->getName();
                $productCode = strtoupper(str_replace([' ', "'", '"'], '_', $productName)) . '_' . $faker->unique()->numberBetween(100, 9999);

                $product->setCode($productCode);
                $product->setBand($band);
                $product->addChannel($channel);
                $product->setEnabled(true);

                // Translations
                $translationEn = new ProductTranslation();
                $translationEn->setLocale('en');
                $translationEn->setName($productName);
                $translationEn->setSlug($faker->slug);
                $translationEn->setDescription($faker->paragraph);
                $product->addTranslation($translationEn);

                $translationFr = new ProductTranslation();
                $translationFr->setLocale('fr');
                $translationFr->setName($productName);
                $translationFr->setSlug($faker->slug);
                $translationFr->setDescription($faker->paragraph);
                $product->addTranslation($translationFr);

                $manager->persist($product);

                // Variants with Options
                $productOptions = [];
                foreach ($config['options'] as $optCode) {
                    $option = $options[$optCode];
                    $product->addOption($option);
                    $productOptions[] = $option;
                }

                // Generate combinations
                $variantsValues = [[]];
                foreach ($productOptions as $option) {
                    $newVariantsValues = [];
                    foreach ($variantsValues as $values) {
                        foreach ($option->getValues() as $value) {
                            // Filter sizes for T-shirts/Hoodies vs Posters
                            if ($option->getCode() === 'size') {
                                if (in_array($type, ['T-shirt', 'Hoodie']) && !in_array($value->getCode(), ['S', 'M', 'L', 'XL', 'XXL'])) continue;
                                if ($type === 'Poster' && !in_array($value->getCode(), ['A3', 'A2', '50x70'])) continue;
                            }
                            $newVariantsValues[] = array_merge($values, [$value]);
                        }
                    }
                    $variantsValues = $newVariantsValues;
                }

                foreach ($variantsValues as $values) {
                    $variant = new ProductVariant();
                    $variant->setCurrentLocale('fr');
                    $variant->setFallbackLocale('fr');
                    $variant->setProduct($product);

                    $valCodes = array_map(fn($v) => $v->getCode(), $values);
                    $valNames = array_map(fn($v) => $v->getValue(), $values);

                    $variant->setCode($productCode . '_' . implode('_', $valCodes));
                    $variant->setName(implode(' ', $valNames));
                    $variant->setOnHand(rand(0, 50));

                    foreach ($values as $value) {
                        $variant->addOptionValue($value);
                    }

                    $manager->persist($variant);
                    $allMerchVariants[] = $variant;
                }
            }
        }

        // 7. Create Customers & Orders
        for ($i = 0; $i < 15; $i++) {
            $customer = new Customer();
            $customer->setFirstName($faker->firstName);
            $customer->setLastName($faker->lastName);
            $customer->setEmail($faker->unique()->safeEmail);
            $manager->persist($customer);

            // Create 1-3 orders per customer
            $numOrders = rand(1, 3);
            for ($j = 0; $j < $numOrders; $j++) {
                $order = new Order();
                $order->setCustomer($customer);
                $order->setChannel($channel);
                $order->setNumber($faker->unique()->numerify('ORD-######'));
                $order->setState($faker->randomElement([Order::STATE_NEW, Order::STATE_FULFILLED, Order::STATE_CANCELLED]));
                $order->setCreatedAt($faker->dateTimeBetween('-1 year', 'now'));

                // Billing & Shipping Address
                $address = new Address();
                $address->setFirstName($customer->getFirstName());
                $address->setLastName($customer->getLastName());
                $address->setStreet($faker->streetAddress);
                $address->setCity($faker->city);
                $address->setPostcode($faker->postcode);
                $address->setCountryCode('FR');
                $manager->persist($address);

                $order->setBillingAddress($address);
                $order->setShippingAddress(clone $address);

                // Add random items (Merch, Releases, Tracks)
                $numItems = rand(1, 4);
                $totalOrder = 0;

                for ($k = 0; $k < $numItems; $k++) {
                    $allAvailableVariants = array_merge($allMerchVariants, $allReleases, $allTracks);
                    $item = new OrderItem();
                    $order->addItem($item);

                    // Pick a random variant
                    $variant = $allAvailableVariants[array_rand($allAvailableVariants)];

                    if ($variant instanceof Release && $variant->getPrice()) {
                        $price = (int) ($variant->getPrice() * 100);
                    } else {
                        $price = rand(10, 50) * 100;
                    }

                    $quantity = rand(1, 2);
                    $item->setVariant($variant);
                    $item->setUnitPrice($price);
                    $item->setQuantity($quantity);
                    $item->setTotal($price * $quantity);
                    $totalOrder += $item->getTotal();

                    $manager->persist($item);
                }

                $order->setTotal($totalOrder);
                $manager->persist($order);
            }
        }

        $manager->flush();
    }
}
