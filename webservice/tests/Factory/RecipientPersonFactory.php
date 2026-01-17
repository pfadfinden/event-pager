<?php

declare(strict_types=1);

namespace App\Tests\Factory;

use App\Core\MessageRecipient\Model\Person;
use Override;
use Symfony\Component\Uid\Ulid;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Person>
 */
final class RecipientPersonFactory extends PersistentObjectFactory
{
    private const array GERMAN_FIRST_NAMES = [
        'Thomas', 'Michael', 'Andreas', 'Stefan', 'Christian', 'Markus', 'Daniel', 'Martin', 'Peter', 'Frank',
        'Klaus', 'Wolfgang', 'Jürgen', 'Dieter', 'Hans', 'Bernd', 'Matthias', 'Uwe', 'Ralf', 'Jens',
        'Tobias', 'Florian', 'Sebastian', 'Alexander', 'Patrick', 'Marco', 'Jan', 'Tim', 'Dennis', 'Nico',
        'Julia', 'Anna', 'Maria', 'Sandra', 'Sabine', 'Petra', 'Claudia', 'Nicole', 'Monika', 'Stefanie',
        'Kathrin', 'Andrea', 'Christine', 'Birgit', 'Kerstin', 'Susanne', 'Martina', 'Heike', 'Melanie', 'Tanja',
        'Lisa', 'Laura', 'Sarah', 'Lena', 'Katharina', 'Sophie', 'Hannah', 'Johanna', 'Marie', 'Emma',
        'Felix', 'Lukas', 'Leon', 'Paul', 'Jonas', 'Maximilian', 'Philipp', 'David', 'Erik', 'Julian',
        'Nadine', 'Jennifer', 'Jessica', 'Vanessa', 'Jasmin', 'Christina', 'Daniela', 'Anja', 'Simone', 'Silke',
        'Mareike', 'Theresa', 'Carolin', 'Miriam', 'Franziska', 'Helena', 'Isabell', 'Nina', 'Lea', 'Mona', 'Ronja',
        'Alina', 'Celine', 'Pauline', 'Marlene', 'Clara', 'Fiona', 'Elena', 'Amelie', 'Charlotte', 'Greta', 'Luisa',
        'Maja', 'Helene', 'Ella', 'Isabella', 'Mira', 'Lara', 'Pia', 'Eva', 'Antonia', 'Mina', 'Annika', 'Sina',
        'Mareen', 'Jule', 'Romy', 'Carla', 'Mona', 'Selina', 'Oliver', 'Niklas', 'Bastian', 'Kai', 'Sven', 'Holger',
        'Georg', 'Lennart', 'Hendrik', 'Till', 'Moritz', 'Fabian', 'Pascal', 'Kevin', 'Robin', 'Dominik', 'Ruben',
        'Timo', 'Benedikt', 'Hannes', 'Konstantin', 'Jannis', 'Malte', 'Kilian', 'Lennox', 'Noah', 'Elias', 'Samuel',
        'Adrian', 'Gabriel', 'Rico', 'Gerrit', 'Arne', 'Torben', 'Jörg', 'Heinrich', 'Friedrich', 'Otto', 'Karl',
        'Gustav', 'Bruno', 'Theodor', 'Albert', 'Ernst', 'Rudolf', 'Manfred', 'Günter', 'Horst', 'Volker', 'Reinhard',
        'Ingrid', 'Gisela', 'Waltraud', 'Renate', 'Ulrike', 'Ilona', 'Erika', 'Hildegard', 'Brigitte', 'Anneliese',
    ];

    private const array GERMAN_LAST_NAMES = [
        'Müller', 'Schmidt', 'Schneider', 'Fischer', 'Weber', 'Meyer', 'Wagner', 'Becker', 'Schulz', 'Hoffmann',
        'Schäfer', 'Koch', 'Bauer', 'Richter', 'Klein', 'Wolf', 'Schröder', 'Neumann', 'Schwarz', 'Zimmermann',
        'Braun', 'Krüger', 'Hofmann', 'Hartmann', 'Lange', 'Schmitt', 'Werner', 'Schmitz', 'Krause', 'Meier',
        'Lehmann', 'Schmid', 'Schulze', 'Maier', 'Köhler', 'Herrmann', 'König', 'Walter', 'Mayer', 'Huber',
        'Kaiser', 'Fuchs', 'Peters', 'Lang', 'Scholz', 'Möller', 'Weiß', 'Jung', 'Hahn', 'Schubert', 'Matthes',
        'Vogel', 'Friedrich', 'Keller', 'Günther', 'Frank', 'Berger', 'Winkler', 'Roth', 'Beck', 'Lorenz', 'Korte',
        'Graf', 'Böhm', 'Engel', 'Busch', 'Horn', 'Sauer', 'Arnold', 'Ott', 'Paul', 'Seidel', 'Hubmann', 'Albrecht',
        'Franke', 'Kraft', 'Reuter', 'Barth', 'Dietrich', 'Schuster', 'Kühn', 'Pohl', 'Heinrich', 'Voigt', 'Sommer',
        'Brandt', 'Seifert', 'Ludwig', 'Heinz', 'Haas', 'Bergmann', 'Schreiber', 'Jäger', 'Kuhn', 'Kramer', 'Böttcher',
        'Bender', 'Reinhardt', 'Ulrich', 'Adam', 'Nowak', 'Kern', 'Kaufmann', 'Schulte', 'Reich', 'Kunz', 'Wendt',
        'Krebs', 'Schilling', 'Binder', 'Kopp', 'Kuhnert', 'Rieger', 'Kroll', 'Wiese', 'Hein', 'Kunzmann', 'Bachmann',
        'Stahl', 'Kessler', 'Haase', 'Schramm', 'Heinemann', 'Bayer', 'Nickel', 'Köster', 'Reuter', 'Merkel', 'Stark',
        'Dietz', 'Eckert', 'Heuer', 'Ritter', 'Wolff', 'Bischof', 'Kremer', 'Schenk', 'Böttger', 'Heinrichs',
    ];

    public static function class(): string
    {
        return Person::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaults(): array
    {
        $firstName = self::faker()->firstName();
        $lastName = self::faker()->lastName();

        return [
            'name' => $firstName.' '.$lastName,
            'id' => new Ulid(),
        ];
    }

    public function withGermanName(): static
    {
        /** @var string $firstName */
        $firstName = self::faker()->randomElement(self::GERMAN_FIRST_NAMES);
        /** @var string $lastName */
        $lastName = self::faker()->randomElement(self::GERMAN_LAST_NAMES);

        return $this->with(['name' => $firstName.' '.$lastName]);
    }

    #[Override]
    protected function initialize(): static
    {
        return $this;
    }
}
