<?php

namespace App\DataFixtures;

use Aropixel\BlogBundle\Entity\Post;
use Aropixel\BlogBundle\Entity\PostTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class PostFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['dev'];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getPosts() as $data) {
            $post = new Post();

            // English values are the Gedmo fallback
            $post->setTitle($data['en']['title']);
            $post->setExcerpt($data['en']['excerpt']);
            $post->setDescription($data['en']['description']);
            $post->setStatus('online');
            $post->setPublishAt(new \DateTime('-' . $data['months_ago'] . ' months'));

            // French translations
            $post->addTranslation(new PostTranslation('fr', 'title', $data['fr']['title']));
            $post->addTranslation(new PostTranslation('fr', 'excerpt', $data['fr']['excerpt']));
            $post->addTranslation(new PostTranslation('fr', 'description', $data['fr']['description']));

            $manager->persist($post);
        }

        $manager->flush();
    }

    /**
     * @return array<int, array{en: array{title: string, excerpt: string, description: string}, fr: array{title: string, excerpt: string, description: string}, months_ago: int}>
     */
    private function getPosts(): array
    {
        return [
            [
                'en' => [
                    'title'       => 'Indie Label Signs Post-Punk Trio MNNQNS',
                    'excerpt'     => 'We are thrilled to welcome MNNQNS to our roster. The Rouen trio brings a raw, urgent sound that fits perfectly with our catalogue.',
                    'description' => '<p>After months of negotiations, we are proud to announce that <strong>MNNQNS</strong> have officially joined the Indie Label family. The Rouen-based trio — formed in 2013 — have spent the last decade refining a post-punk sound that draws as much from Wire as from early Pavement.</p><p>Their forthcoming debut on our label is expected for early next year. In the meantime, catch them on the road this autumn across France and Belgium.</p>',
                ],
                'fr' => [
                    'title'       => "Indie Label signe le trio post-punk MNNQNS",
                    'excerpt'     => "Nous sommes ravis d'accueillir MNNQNS dans notre catalogue. Le trio rouennais apporte un son brut et urgent qui s'intègre parfaitement à notre ligne éditoriale.",
                    'description' => "<p>Après plusieurs mois de discussions, nous sommes fiers d'annoncer que <strong>MNNQNS</strong> rejoint officiellement la famille Indie Label. Le trio originaire de Rouen — formé en 2013 — a passé une décennie à affiner un son post-punk qui puise autant chez Wire que chez les premiers Pavement.</p><p>Leur premier disque sur notre label est attendu pour le début de l'année prochaine. D'ici là, retrouvez-les sur les routes cet automne en France et en Belgique.</p>",
                ],
                'months_ago' => 11,
            ],
            [
                'en' => [
                    'title'       => 'Slift Announces European Tour',
                    'excerpt'     => 'Space rock titans Slift will be touring Europe this spring. Dates confirmed in Paris, Berlin, London, Amsterdam and Barcelona.',
                    'description' => '<p><strong>Slift</strong> are hitting the road again. After a sold-out run across France last year, the Toulouse three-piece are taking their colossal sound to the rest of Europe this spring.</p><p>Confirmed dates include shows at La Maroquinerie (Paris), Berghain Kantine (Berlin), Scala (London), Paradiso (Amsterdam) and Razzmatazz (Barcelona). Tickets go on sale this Friday at 10am.</p><p>Support on all dates will be provided by rising French outfit Structures, who recently released their debut EP on our label.</p>',
                ],
                'fr' => [
                    'title'       => "Slift annonce une tournée européenne",
                    'excerpt'     => "Les titans du space rock Slift partent en tournée en Europe ce printemps. Dates confirmées à Paris, Berlin, Londres, Amsterdam et Barcelone.",
                    'description' => "<p><strong>Slift</strong> reprend la route. Après une tournée française à guichets fermés l'an dernier, le trio toulousain emmène son son colossal dans le reste de l'Europe ce printemps.</p><p>Les dates confirmées incluent des concerts à La Maroquinerie (Paris), Berghain Kantine (Berlin), Scala (Londres), Paradiso (Amsterdam) et Razzmatazz (Barcelone). Les billets sont en vente ce vendredi à 10h.</p><p>L'assurance première partie sur toutes les dates sera assurée par Structures, qui a récemment sorti son premier EP sur notre label.</p>",
                ],
                'months_ago' => 9,
            ],
            [
                'en' => [
                    'title'       => 'New Album: Frustration — "Rien de Nouveau Sous le Soleil"',
                    'excerpt'     => 'The Paris legends return with their most abrasive record to date. Eight tracks of uncompromising industrial post-punk.',
                    'description' => '<p>It has been three years since their last album and the wait is finally over. <strong>Frustration</strong> announce <em>Rien de Nouveau Sous le Soleil</em>, an eight-track bruiser produced by Laurent Thinot at Studio Grosse Boîte in Paris.</p><p>First single "Territoire" is out now on all platforms. The full album lands on vinyl and digital on March 14th. Pre-orders are open in our shop — early birds get a signed insert.</p>',
                ],
                'fr' => [
                    'title'       => "Nouvel album : Frustration — « Rien de Nouveau Sous le Soleil »",
                    'excerpt'     => "Les légendes parisiennes reviennent avec leur disque le plus abrasif à ce jour. Huit titres de post-punk industriel sans compromis.",
                    'description' => "<p>Trois ans après leur dernier album, l'attente est terminée. <strong>Frustration</strong> annonce <em>Rien de Nouveau Sous le Soleil</em>, un massue de huit titres produit par Laurent Thinot au Studio Grosse Boîte à Paris.</p><p>Le premier single « Territoire » est disponible sur toutes les plateformes. L'album complet sort en vinyle et en numérique le 14 mars. Les pré-commandes sont ouvertes dans notre boutique — les premiers reçoivent un encart dédicacé.</p>",
                ],
                'months_ago' => 7,
            ],
            [
                'en' => [
                    'title'       => 'Record of the Month: La Femme — "Paradigmes"',
                    'excerpt'     => "Our editors unanimously pick La Femme's psychedelic masterpiece as their record of the month. A timeless piece of French pop weirdness.",
                    'description' => '<p>Every month our team picks one record that deserves your full, uninterrupted attention. This month, the choice was unanimous: <em>Paradigmes</em> by <strong>La Femme</strong>.</p><p>Released in 2021 but still sounding utterly alien, the album careens from motorik krautrock to lush bossa nova, from distorted synth-punk to torch-song balladry — sometimes within the same track. If you have not heard it yet, now is the time.</p><p>You can find both the LP and CD versions in our shop, alongside their entire back catalogue.</p>',
                ],
                'fr' => [
                    'title'       => "Disque du mois : La Femme — « Paradigmes »",
                    'excerpt'     => "Nos rédacteurs choisissent à l'unanimité le chef-d'œuvre psychédélique de La Femme comme disque du mois. Un moment intemporel de pop française décalée.",
                    'description' => "<p>Chaque mois, notre équipe choisit un disque qui mérite votre attention totale et sans interruption. Ce mois-ci, le choix a été unanime : <em>Paradigmes</em> de <strong>La Femme</strong>.</p><p>Sorti en 2021 mais sonnant toujours aussi extraterrestre, l'album passe du krautrock motorik à la bossa nova luxuriante, du synth-punk distordu à la ballade de cabaret — parfois dans le même titre. Si vous ne l'avez pas encore écouté, c'est le moment.</p><p>Vous trouverez le LP et le CD dans notre boutique, ainsi que l'intégralité de leur discographie.</p>",
                ],
                'months_ago' => 6,
            ],
            [
                'en' => [
                    'title'       => 'Behind the Scenes: Recording with Black Country, New Road',
                    'excerpt'     => 'A rare look inside the studio sessions for their forthcoming album. Layered orchestration, spoken word passages and a boldly experimental spirit.',
                    'description' => '<p>We were lucky enough to spend a week at RAK Studios in London while <strong>Black Country, New Road</strong> were putting the finishing touches to their third album. What we witnessed was unlike anything we had seen before.</p><p>The sessions began each day at noon and rarely ended before the early hours of the morning. Conductor Georgia Ellery led a small string quartet through seventeen takes of a single passage while the rest of the band improvised around her. By day three, something extraordinary had emerged.</p><p>The album is due later this year. We will be stocking exclusive limited pressings — sign up to our newsletter to be the first to know when pre-orders go live.</p>',
                ],
                'fr' => [
                    'title'       => "Les coulisses : en studio avec Black Country, New Road",
                    'excerpt'     => "Un rare coup d'œil dans les sessions d'enregistrement de leur prochain album. Orchestration en couches, passages en prose et un esprit résolument expérimental.",
                    'description' => "<p>Nous avons eu la chance de passer une semaine aux RAK Studios à Londres pendant que <strong>Black Country, New Road</strong> peaufinait leur troisième album. Ce que nous avons observé ne ressemblait à rien de ce que nous avions vu auparavant.</p><p>Les sessions commençaient chaque jour à midi et se terminaient rarement avant les premières heures du matin. La cheffe Georgia Ellery guidait un petit quatuor à cordes à travers dix-sept prises d'un seul passage pendant que le reste du groupe improvisait autour d'elle. Au troisième jour, quelque chose d'extraordinaire était apparu.</p><p>L'album est prévu pour plus tard cette année. Nous stockerons des pressages limités exclusifs — inscrivez-vous à notre newsletter pour être les premiers informés de l'ouverture des précommandes.</p>",
                ],
                'months_ago' => 5,
            ],
            [
                'en' => [
                    'title'       => 'Vinyl Reissue: Fontaines D.C. — "Dogrel" on Limited Orange Wax',
                    'excerpt'     => "The Dublin five-piece's explosive debut gets the reissue treatment. 500 copies pressed on orange vinyl, exclusive to our shop.",
                    'description' => '<p>Three years on from its release, <em>Dogrel</em> by <strong>Fontaines D.C.</strong> remains one of the most vital debut albums of the decade. Its collision of post-punk energy and poetic lyricism landed like a lightning bolt in 2019 — and it sounds just as urgent today.</p><p>We are delighted to offer an exclusive reissue on 180g orange wax, limited to 500 copies worldwide. Each copy is individually numbered. No repress will follow.</p><p>Available exclusively in our shop from next Monday. Limit of two copies per customer.</p>',
                ],
                'fr' => [
                    'title'       => "Réédition vinyle : Fontaines D.C. — « Dogrel » en édition limitée orange",
                    'excerpt'     => "Le premier album explosif du quintet dublinois a droit à une réédition. 500 exemplaires pressés sur vinyle orange, exclusivement disponibles dans notre boutique.",
                    'description' => "<p>Trois ans après sa sortie, <em>Dogrel</em> de <strong>Fontaines D.C.</strong> reste l'un des albums debuts les plus essentiels de la décennie. Sa collision d'énergie post-punk et de lyrisme poétique a eu l'effet d'un coup de foudre en 2019 — et sonne tout aussi urgent aujourd'hui.</p><p>Nous sommes ravis de proposer une réédition exclusive sur vinyle orange 180g, limitée à 500 exemplaires dans le monde entier. Chaque exemplaire est numéroté individuellement. Pas de réédition prévue.</p><p>Disponible exclusivement dans notre boutique à partir de lundi prochain. Limité à deux exemplaires par client.</p>",
                ],
                'months_ago' => 4,
            ],
            [
                'en' => [
                    'title'       => 'Interview: Shame on Vulnerability and Artistic Growth',
                    'excerpt'     => 'We sat down with frontman Charlie Steen ahead of their third album to talk about growing up in public, finding space to breathe and the pressure of expectations.',
                    'description' => '<p><strong>Shame</strong> frontman Charlie Steen meets us at a café in Brixton, arriving twenty minutes late and utterly unapologetic about it. Over two hours and several rounds of coffee, we discuss what it means to evolve as a band under relentless public scrutiny.</p><p><em>"The first album was anger. The second was confusion. I think this one is something closer to acceptance,"</em> he says, cradling his third espresso. <em>"Not resignation — acceptance. There is a difference."</em></p><p>The conversation turns to the making of the new record, the months spent in a converted farmhouse in rural Normandy, and why the band decided to strip back their sound rather than expand it.</p>',
                ],
                'fr' => [
                    'title'       => "Interview : Shame sur la vulnérabilité et la croissance artistique",
                    'excerpt'     => "Nous nous sommes assis avec le chanteur Charlie Steen avant leur troisième album pour parler de grandir en public, de trouver de l'espace pour respirer et de la pression des attentes.",
                    'description' => "<p>Le chanteur de <strong>Shame</strong>, Charlie Steen, nous retrouve dans un café de Brixton, arrivant vingt minutes en retard et totalement sans excuse à ce sujet. Au cours de deux heures et plusieurs cafés, nous discutons de ce que signifie évoluer en tant que groupe sous une surveillance publique incessante.</p><p><em>« Le premier album c'était la colère. Le deuxième, la confusion. Je pense que celui-ci est quelque chose qui ressemble davantage à l'acceptation »</em>, dit-il en tenant son troisième espresso. <em>« Pas la résignation — l'acceptation. Il y a une différence. »</em></p><p>La conversation se tourne vers la conception du nouvel album, les mois passés dans une ferme normande reconvertie, et pourquoi le groupe a décidé de dépouiller son son plutôt que de l'élargir.</p>",
                ],
                'months_ago' => 3,
            ],
            [
                'en' => [
                    'title'       => 'Festival Season: Our Picks for Summer 2025',
                    'excerpt'     => "From Primavera to Rock en Seine, here are the festivals where you'll find the best of our catalogue this summer — and a few hidden gems worth travelling for.",
                    'description' => '<p>Festival season is upon us and, as usual, our artists are scattered across the continent. Here is where we recommend spending your weekends this summer.</p><ul><li><strong>Primavera Sound (Barcelona, 30 May–1 June)</strong> — Slift headline the Pitchfork stage on Friday. Arrive early for Bodega.</li><li><strong>Rock en Seine (Paris, 21–24 August)</strong> — IDLES close the main stage on Saturday. The Murder Capital play the Grande Scène on Sunday afternoon.</li><li><strong>Les Eurockéennes (Belfort, 3–6 July)</strong> — Frustration open Thursday. Dry Cleaning close Sunday night.</li><li><strong>Sziget (Budapest, 6–11 August)</strong> — A rare Eastern European date for Black Country, New Road.</li></ul>',
                ],
                'fr' => [
                    'title'       => "Saison des festivals : nos coups de cœur pour l'été 2025",
                    'excerpt'     => "De Primavera à Rock en Seine, voici les festivals où vous retrouverez le meilleur de notre catalogue cet été — et quelques pépites cachées qui valent le déplacement.",
                    'description' => "<p>La saison des festivals arrive et, comme d'habitude, nos artistes sont disséminés sur le continent. Voici où nous vous recommandons de passer vos week-ends cet été.</p><ul><li><strong>Primavera Sound (Barcelone, 30 mai–1er juin)</strong> — Slift tête d'affiche de la scène Pitchfork le vendredi. Arrivez tôt pour Bodega.</li><li><strong>Rock en Seine (Paris, 21–24 août)</strong> — IDLES ferment la scène principale le samedi. The Murder Capital joue sur la Grande Scène le dimanche après-midi.</li><li><strong>Les Eurockéennes (Belfort, 3–6 juillet)</strong> — Frustration ouvre le jeudi. Dry Cleaning clôture le dimanche soir.</li><li><strong>Sziget (Budapest, 6–11 août)</strong> — Une rare date en Europe de l'Est pour Black Country, New Road.</li></ul>",
                ],
                'months_ago' => 2,
            ],
            [
                'en' => [
                    'title'       => 'New in the Shop: Crack Cloud "Tough Baby" on Cassette',
                    'excerpt'     => "The Vancouver collective's most recent album arrives on cassette in our shop for the first time. Limited to 200 copies, hand-numbered.",
                    'description' => '<p>We have been fans of <strong>Crack Cloud</strong> since their earliest EPs, and we are delighted to finally bring one of their records into our physical shop. <em>Tough Baby</em> — their second full-length — is available from today on cassette, limited to 200 hand-numbered copies.</p><p>The album is a dizzying mix of post-punk, art rock and agitprop, with production that feels simultaneously claustrophobic and enormous. It is one of the few records of recent years that genuinely surprised us on every listen.</p><p>The cassettes come with a two-sided A5 insert featuring photography from their 2023 world tour. Order yours before they sell out — previous runs went within hours.</p>',
                ],
                'fr' => [
                    'title'       => "Nouveauté en boutique : Crack Cloud « Tough Baby » en cassette",
                    'excerpt'     => "Le dernier album du collectif vancouvérois arrive pour la première fois en cassette dans notre boutique. Limité à 200 exemplaires, numérotés à la main.",
                    'description' => "<p>Nous sommes fans de <strong>Crack Cloud</strong> depuis leurs premiers EPs, et nous sommes ravis de pouvoir enfin faire entrer un de leurs disques dans notre boutique physique. <em>Tough Baby</em> — leur deuxième album — est disponible dès aujourd'hui en cassette, limité à 200 exemplaires numérotés à la main.</p><p>L'album est un mélange étourdissant de post-punk, de rock art et d'agitprop, avec une production qui semble à la fois claustrophobe et immense. C'est l'un des rares disques de ces dernières années qui nous a véritablement surpris à chaque écoute.</p><p>Les cassettes sont accompagnées d'un encart A5 recto-verso avec des photographies de leur tournée mondiale 2023. Commandez le vôtre avant qu'ils ne soient épuisés — les tirages précédents ont été vendus en quelques heures.</p>",
                ],
                'months_ago' => 1,
            ],
        ];
    }
}
