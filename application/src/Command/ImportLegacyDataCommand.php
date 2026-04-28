<?php

namespace App\Command;

use App\Entity\Album;
use App\Entity\Artist;
use App\Entity\Band;
use App\Entity\Media;
use App\Entity\Release;
use App\Entity\Track;
use App\Entity\Tracklist;
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
        $this->importMedia($io);
        $this->importReleases($io);
        $this->importTracks($io);
        $this->importTracklists($io);

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
            $io->writeln(\sprintf('Processing Artist: %s %s', $legacyArtist['prenom'], $legacyArtist['nom']));

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
            $io->writeln(\sprintf('Processing Band: %s', $legacyBand['nom']));

            $band = new Band();
            $band->setName($legacyBand['nom']);
            $band->setStatus($legacyBand['status']);
            $band->setWebsite($legacyBand['site_web']);
            $band->setEmail($legacyBand['email']);
            $band->setFacebook($legacyBand['facebook']);
            $band->setTwitter($legacyBand['twitter']);
            $band->setInstagram($legacyBand['instagram']);

            // Get translations
            $translations = $conn->executeQuery('SELECT lang, fiche FROM groupes_traductions WHERE groupe_id = :id', ['id' => $legacyBand['id']])->fetchAllAssociative();

            foreach ($translations as $translation) {
                $locale = $translation['lang'];
                if ('fr' === $locale) {
                    $locale = 'fr_FR';
                }
                if ('en' === $locale || 'uk' === $locale) {
                    $locale = 'en_GB';
                }

                $band->setTranslatableLocale($locale);
                $band->setDescription($translation['fiche']);
                $this->entityManager->persist($band);
            }

            // Temporarily flush to get ID if needed, or just persist
            $this->entityManager->persist($band);
            $this->entityManager->flush();

            // Store legacy ID to new object mapping if we need it for albums or members
            // But here we can just handle members immediately
            $memberIds = $conn->executeQuery('SELECT artiste_id FROM groupes_artistes WHERE groupe_id = :id', ['id' => $legacyBand['id']])->fetchFirstColumn();

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

    private function getLegacyArtistEmail(\Doctrine\DBAL\Connection $conn, int $id): ?string
    {
        return $conn->executeQuery('SELECT mail FROM artistes WHERE id = :id', ['id' => $id])->fetchOne();
    }

    private function importAlbums(SymfonyStyle $io): void
    {
        $io->section('Importing Albums and their translations');

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare('SELECT * FROM albums');
        $legacyAlbums = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($legacyAlbums as $legacyAlbum) {
            $io->writeln(\sprintf('Processing Album: %s', $legacyAlbum['titre']));

            // On vérifie si l'album existe déjà pour ne pas le recréer
            // (Utile si on relance la commande)
            $album = $this->entityManager->getRepository(Album::class)->findOneBy(['name' => $legacyAlbum['titre']]);
            if (!$album) {
                $album = new Album();
                $album->setName($legacyAlbum['titre']);
                $album->setCatalogNumber($legacyAlbum['catalogue']);
                $album->setStatus($legacyAlbum['status']);

                if ($legacyAlbum['date_sortie']) {
                    $album->setReleaseDate(new \DateTime($legacyAlbum['date_sortie']));
                }

                // Link to Band
                if ($legacyAlbum['groupe_id']) {
                    $bandName = $conn->executeQuery('SELECT nom FROM groupes WHERE id = :id', ['id' => $legacyAlbum['groupe_id']])->fetchOne();
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

            $albumTitle = $conn->executeQuery('SELECT titre FROM albums WHERE id = :id', ['id' => $legacyAlbumId])->fetchOne();
            $similarTitle = $conn->executeQuery('SELECT titre FROM albums WHERE id = :id', ['id' => $legacySimilarId])->fetchOne();

            $album = $this->entityManager->getRepository(Album::class)->findOneBy(['name' => $albumTitle]);
            $similarAlbum = $this->entityManager->getRepository(Album::class)->findOneBy(['name' => $similarTitle]);

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

            $albumTitle = $conn->executeQuery('SELECT titre FROM albums WHERE id = :id', ['id' => $legacyAlbumId])->fetchOne();
            $artistEmail = $this->getLegacyArtistEmail($conn, $legacyArtistId);

            $album = $this->entityManager->getRepository(Album::class)->findOneBy(['name' => $albumTitle]);
            $artist = $this->entityManager->getRepository(Artist::class)->findOneBy(['email' => $artistEmail]);

            if ($album && $artist) {
                $album->addArtist($artist);
            }
        }

        $this->entityManager->flush();
        $io->writeln('Album artists relations processed.');
    }

    private function importMedia(SymfonyStyle $io): void
    {
        $io->section('Importing Media Types');

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare('SELECT * FROM supports');
        $legacySupports = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($legacySupports as $legacySupport) {
            $io->writeln(\sprintf('Processing Media: %s', $legacySupport['libelle']));

            $media = $this->entityManager->getRepository(Media::class)->findOneBy(['name' => $legacySupport['libelle']]);
            if (!$media) {
                $media = new Media();
                $media->setName($legacySupport['libelle']);
                $media->setIsDigital(null !== $legacySupport['digital_id']);
                $this->entityManager->persist($media);
            }
        }

        $this->entityManager->flush();
        $io->writeln('Media types processed.');
    }

    private function importReleases(SymfonyStyle $io): void
    {
        $io->section('Importing Releases');

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare('SELECT * FROM formats');
        $legacyFormats = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($legacyFormats as $legacyFormat) {
            $io->writeln(\sprintf('Processing Release: %s', $legacyFormat['titre']));

            // Resolve Album
            $albumTitle = $conn->executeQuery('SELECT titre FROM albums WHERE id = :id', ['id' => $legacyFormat['album_id']])->fetchOne();
            $album = $this->entityManager->getRepository(Album::class)->findOneBy(['name' => $albumTitle]);

            // Resolve Media
            $supportName = $conn->executeQuery('SELECT libelle FROM supports WHERE id = :id', ['id' => $legacyFormat['support_id']])->fetchOne();
            $media = $this->entityManager->getRepository(Media::class)->findOneBy(['name' => $supportName]);

            if ($album && $media) {
                $release = new Release();
                $release->setAlbum($album);
                $release->setMedia($media);
                $release->setTitle($legacyFormat['titre']);
                $release->setPrice((int) $legacyFormat['prix']);
                $release->setStatus($legacyFormat['status']);

                $this->entityManager->persist($release);
            }
        }

        $this->entityManager->flush();
        $io->writeln('Releases processed.');
    }

    private function importTracks(SymfonyStyle $io): void
    {
        $io->section('Importing Tracks');

        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare('SELECT * FROM titres');
        $legacyTracks = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($legacyTracks as $legacyTrack) {
            $io->writeln(\sprintf('Processing Track: %s', $legacyTrack['titre1']));

            $track = $this->entityManager->getRepository(Track::class)->findOneBy(['name' => $legacyTrack['titre1']]);
            if (!$track) {
                $track = new Track();
                $track->setName($legacyTrack['titre1']);
                $track->setDuration($legacyTrack['duree']);
                $track->setIsrc($legacyTrack['isrc']);
                $track->setLyrics($legacyTrack['paroles']);
                $this->entityManager->persist($track);
            }
        }

        $this->entityManager->flush();
        $io->writeln('Tracks processed.');
    }

    private function importTracklists(SymfonyStyle $io): void
    {
        $io->section('Importing Tracklists (Relations)');

        $conn = $this->entityManager->getConnection();

        // 1. Import from titres_albums (Master tracklist)
        $io->writeln('Processing master tracklists from titres_albums...');
        $stmtMaster = $conn->prepare('SELECT * FROM titres_albums');
        $masterRelations = $stmtMaster->executeQuery()->fetchAllAssociative();

        foreach ($masterRelations as $relation) {
            $legacyAlbumId = $relation['album_id'];
            $legacyTrackId = $relation['titre_id'];

            $albumTitle = $conn->executeQuery('SELECT titre FROM albums WHERE id = :id', ['id' => $legacyAlbumId])->fetchOne();
            $trackTitle = $conn->executeQuery('SELECT titre1 FROM titres WHERE id = :id', ['id' => $legacyTrackId])->fetchOne();

            $album = $this->entityManager->getRepository(Album::class)->findOneBy(['name' => $albumTitle]);
            $track = $this->entityManager->getRepository(Track::class)->findOneBy(['name' => $trackTitle]);

            if ($album && $track) {
                $tracklist = $this->entityManager->getRepository(Tracklist::class)->findOneBy([
                    'album' => $album,
                    'track' => $track,
                ]);

                if (!$tracklist) {
                    $tracklist = new Tracklist();
                    $tracklist->setAlbum($album);
                    $tracklist->setTrack($track);
                    $tracklist->setPosition((int) $relation['position']);
                    $this->entityManager->persist($tracklist);
                }
            }
        }
        $this->entityManager->flush();

        // 2. Import from formats_titres (Release specific tracklists)
        $io->writeln('Processing release specific tracklists from formats_titres...');
        $stmtRelease = $conn->prepare('SELECT * FROM formats_titres');
        $releaseRelations = $stmtRelease->executeQuery()->fetchAllAssociative();

        foreach ($releaseRelations as $relation) {
            $legacyFormatId = $relation['format_id'];
            $legacyTrackId = $relation['titre_id'];

            // Get new Release and Track
            $formatData = $conn->executeQuery('SELECT titre, album_id FROM formats WHERE id = :id', ['id' => $legacyFormatId])->fetchAssociative();
            if (!$formatData) {
                continue;
            }

            $release = $this->entityManager->getRepository(Release::class)->findOneBy(['title' => $formatData['titre']]);

            $albumTitle = $conn->executeQuery('SELECT titre FROM albums WHERE id = :id', ['id' => $formatData['album_id']])->fetchOne();
            $trackTitle = $conn->executeQuery('SELECT titre1 FROM titres WHERE id = :id', ['id' => $legacyTrackId])->fetchOne();

            $album = $this->entityManager->getRepository(Album::class)->findOneBy(['name' => $albumTitle]);
            $track = $this->entityManager->getRepository(Track::class)->findOneBy(['name' => $trackTitle]);

            if ($release && $track) {
                // Find or create the tracklist entry for this album/track
                $album = $release->getAlbum();
                $tracklist = $this->entityManager->getRepository(Tracklist::class)->findOneBy([
                    'album' => $album,
                    'track' => $track,
                ]);

                if (!$tracklist) {
                    $tracklist = new Tracklist();
                    $tracklist->setAlbum($album);
                    $tracklist->setTrack($track);
                    $tracklist->setPosition((int) $relation['position']);
                    $this->entityManager->persist($tracklist);
                }

                $tracklist->addRelease($release);
            }
        }

        $this->entityManager->flush();
        $io->writeln('Tracklists processed.');
    }
}
