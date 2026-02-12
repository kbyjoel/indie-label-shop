<?php

namespace App\Command;

use App\Entity\Album;
use App\Entity\Artist;
use App\Entity\Band;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:import-legacy-data',
    description: 'Import data from legacy tables to Symfony entities with Gedmo Translatable support',
)]
class ImportLegacyDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Starting import from legacy tables');

        // Note: Since entities are already mapped to legacy tables,
        // we might need to use native queries to get the raw data
        // or just process existing entities to handle translations.
        // If we want to "import" into the same tables, we are basically
        // converting the legacy translation format to Gedmo format.

        $this->importArtists($io);
        $this->importBands($io);
        $this->importAlbums($io);
        $this->importAlbumSimilar($io);
        $this->importAlbumArtists($io);

        $io->success('Import completed!');

        return Command::SUCCESS;
    }

    private function importArtists(SymfonyStyle $io): void
    {
        $io->section('Importing Artists');

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare('SELECT * FROM artistes');
        $legacyArtists = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($legacyArtists as $legacyArtist) {
            $io->writeln(sprintf('Processing Artist: %s %s', $legacyArtist['prenom'], $legacyArtist['nom']));

            $artist = new Artist();
            $artist->setFirstName($legacyArtist['prenom']);
            $artist->setLastName($legacyArtist['nom']);
            $artist->setAddress($legacyArtist['adresse']);
            $artist->setZipCode($legacyArtist['codepostal']);
            $artist->setCity($legacyArtist['ville']);
            $artist->setPhone($legacyArtist['telephone']);
            $artist->setEmail($legacyArtist['mail']);

            $this->entityManager->persist($artist);
        }

        $this->entityManager->flush();
        $io->writeln('Artists processed.');
    }

    private function importBands(SymfonyStyle $io): void
    {
        $io->section('Importing Bands and their translations');

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare('SELECT * FROM groupes');
        $legacyBands = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($legacyBands as $legacyBand) {
            $io->writeln(sprintf('Processing Band: %s', $legacyBand['nom']));

            $band = new Band();
            $band->setName($legacyBand['nom']);
            $band->setStatus($legacyBand['status']);
            $band->setWebsite($legacyBand['site_web']);
            $band->setEmail($legacyBand['email']);
            $band->setFacebook($legacyBand['facebook']);
            $band->setTwitter($legacyBand['twitter']);
            $band->setInstagram($legacyBand['instagram']);

            // Get translations
            $stmtTrans = $conn->prepare('SELECT lang, fiche FROM groupes_traductions WHERE groupe_id = :id');
            $translations = $stmtTrans->executeQuery(['id' => $legacyBand['id']])->fetchAllAssociative();

            foreach ($translations as $translation) {
                $locale = $translation['lang'];
                if ($locale === 'fr') $locale = 'fr_FR';
                if ($locale === 'en' || $locale === 'uk') $locale = 'en_GB';

                $band->setTranslatableLocale($locale);
                $band->setDescription($translation['fiche']);
                $this->entityManager->persist($band);
            }

            // Temporarily flush to get ID if needed, or just persist
            $this->entityManager->persist($band);
            $this->entityManager->flush();

            // Store legacy ID to new object mapping if we need it for albums or members
            // But here we can just handle members immediately
            $stmtMembers = $conn->prepare('SELECT artiste_id FROM groupes_artistes WHERE groupe_id = :id');
            $memberIds = $stmtMembers->executeQuery(['id' => $legacyBand['id']])->fetchFirstColumn();

            foreach ($memberIds as $legacyArtistId) {
                // We need to find the new artist by some criteria or keep a map
                // For simplicity, let's search by email as it should be unique enough or name
                $artist = $this->entityManager->getRepository(Artist::class)->findOneBy(['email' => $this->getLegacyArtistEmail($conn, $legacyArtistId)]);
                if ($artist) {
                    $band->addMember($artist);
                }
            }
            $this->entityManager->flush();
        }

        $io->writeln('Bands processed.');
    }

    private function getLegacyArtistEmail($conn, $id)
    {
        return $conn->prepare('SELECT mail FROM artistes WHERE id = :id')->executeQuery(['id' => $id])->fetchOne();
    }

    private function importAlbums(SymfonyStyle $io): void
    {
        $io->section('Importing Albums and their translations');

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare('SELECT * FROM albums');
        $legacyAlbums = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($legacyAlbums as $legacyAlbum) {
            $io->writeln(sprintf('Processing Album: %s', $legacyAlbum['titre']));

            // On vérifie si l'album existe déjà pour ne pas le recréer
            // (Utile si on relance la commande)
            $album = $this->entityManager->getRepository(Album::class)->findOneBy(['title' => $legacyAlbum['titre']]);
            if (!$album) {
                $album = new Album();
                $album->setTitle($legacyAlbum['titre']);
                $album->setCatalogNumber($legacyAlbum['catalogue']);
                $album->setStatus($legacyAlbum['status']);

                if ($legacyAlbum['date_sortie']) {
                    $album->setReleaseDate(new \DateTime($legacyAlbum['date_sortie']));
                }

                // Link to Band
                if ($legacyAlbum['groupe_id']) {
                    $bandName = $conn->prepare('SELECT nom FROM groupes WHERE id = :id')->executeQuery(['id' => $legacyAlbum['groupe_id']])->fetchOne();
                    $band = $this->entityManager->getRepository(Band::class)->findOneBy(['name' => $bandName]);
                    if ($band) {
                        $album->setBand($band);
                    }
                }

                // FR Translation
                if ($legacyAlbum['description']) {
                    $album->setTranslatableLocale('fr_FR');
                    $album->setDescription($legacyAlbum['description']);
                    $this->entityManager->persist($album);
                }

                // EN Translation
                if ($legacyAlbum['description_uk']) {
                    $album->setTranslatableLocale('en_GB');
                    $album->setDescription($legacyAlbum['description_uk']);
                    $this->entityManager->persist($album);
                }

                $this->entityManager->persist($album);
                $this->entityManager->flush();
            }
        }

        $io->writeln('Albums processed.');
    }

    private function importAlbumSimilar(SymfonyStyle $io): void
    {
        $io->section('Importing Similar Albums relations');

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare('SELECT * FROM albums_albums');
        $relations = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($relations as $relation) {
            $legacyAlbumId = $relation['album_id'];
            $legacySimilarId = $relation['similar_id'];

            $albumTitle = $conn->prepare('SELECT titre FROM albums WHERE id = :id')->executeQuery(['id' => $legacyAlbumId])->fetchOne();
            $similarTitle = $conn->prepare('SELECT titre FROM albums WHERE id = :id')->executeQuery(['id' => $legacySimilarId])->fetchOne();

            $album = $this->entityManager->getRepository(Album::class)->findOneBy(['title' => $albumTitle]);
            $similarAlbum = $this->entityManager->getRepository(Album::class)->findOneBy(['title' => $similarTitle]);

            if ($album && $similarAlbum) {
                $album->addSimilarAlbum($similarAlbum);
            }
        }

        $this->entityManager->flush();
        $io->writeln('Similar albums relations processed.');
    }

    private function importAlbumArtists(SymfonyStyle $io): void
    {
        $io->section('Importing Album participating Artists relations');

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare('SELECT * FROM albums_artistes');
        $relations = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($relations as $relation) {
            $legacyAlbumId = $relation['album_id'];
            $legacyArtistId = $relation['artiste_id'];

            $albumTitle = $conn->prepare('SELECT titre FROM albums WHERE id = :id')->executeQuery(['id' => $legacyAlbumId])->fetchOne();
            $artistEmail = $this->getLegacyArtistEmail($conn, $legacyArtistId);

            $album = $this->entityManager->getRepository(Album::class)->findOneBy(['title' => $albumTitle]);
            $artist = $this->entityManager->getRepository(Artist::class)->findOneBy(['email' => $artistEmail]);

            if ($album && $artist) {
                $album->addArtist($artist);
            }
        }

        $this->entityManager->flush();
        $io->writeln('Album artists relations processed.');
    }
}
