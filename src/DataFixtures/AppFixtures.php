<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Faker\Generator;
use App\Entity\Persona;
use App\Entity\Chimpokodex;
use App\Entity\Chimpokomon;
use App\Entity\Chimpokofood;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;
    private $userPasswordHasher;


    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {

        $chimpokoName = ["Chaussure", "SuperChimpokomon", "ChimpokoMegamon", "Jambon"];
        $chimpokodexEntries = [];
        $maxStat = 255;
        foreach ($chimpokoName as $key => $name) {
            $chimpokodexEntry = new Chimpokodex();

            $chimpokodexEntry->setName($name);
            $chimpokodexEntry->setStatus("on");
            $chimpokodexEntry->setIdDad($this->faker->numberBetween(1, 151));
            $chimpokodexEntry->setIdMom($this->faker->numberBetween(1, $maxStat));

            $pvMin = $this->faker->numberBetween(0, 151);
            $chimpokodexEntry->setPvMin($pvMin);
            $chimpokodexEntry->setPvMax($this->faker->numberBetween($pvMin, $maxStat));

            $manager->persist($chimpokodexEntry);
            $chimpokodexEntries[] = $chimpokodexEntry;
        }

        $manager->flush();

        for ($i = 0; $i < 100; $i++) {
            $chimpokodexEntry = $chimpokodexEntries[array_rand($chimpokodexEntries)];
            $chimpokomon = new Chimpokomon();
            $chimpokomon->setChimpokodex($chimpokodexEntry);

            $pvMax = $this->faker->numberBetween($chimpokodexEntry->getPvMin(), $chimpokodexEntry->getPvMax());
            $chimpokomon->setPvMax($pvMax);
            $chimpokomon->setPv($this->faker->numberBetween(0, $pvMax));

            $chimpokomon->setName($chimpokodexEntry->getName());

            $chimpokomon->setStatus("on");

            $manager->persist($chimpokomon);
        }


        for ($i = 0; $i < 10; $i++) {
            // Créer des utilisateurs
            $dateNow = new \DateTimeImmutable();
            $publicUser = new User();
            $publicUser->setUsername($this->faker->userName());
            $publicUser->setRoles(["ROLE_PUBLIC"]);
            $publicUser->setName($this->faker->name());
            $publicUser->setPhone($this->faker->phoneNumber());
            $publicUser->setCreatedAt($dateNow);
            $publicUser->setUpdatedAt($dateNow);
            $publicUser->setStatus("on");
            $publicUser->setPassword($this->userPasswordHasher->hashPassword($publicUser, "password"));

            $persona = new Persona();
            $persona->setHeight($this->faker->numberBetween(100, 200));
            $persona->setGender("Test");
            $persona->setStatus("on");
            $persona->setCreatedAt($dateNow);
            $persona->setUpdatedAt($dateNow);
            $persona->setBirthAt($this->faker->dateTimeBetween('-50 years', '-18 years'));
            $persona->setUser($publicUser);

            $manager->persist($persona);
            $manager->persist($publicUser);
        }


        $food = new Chimpokofood();
        $food->setName("Pomme");
        $food->setStatus("on");
        $food->setAmount(10);

        $food2 = new Chimpokofood();
        $food2->setName("Fraise");
        $food2->setStatus("on");
        $food2->setAmount(2);
        $food3 = new Chimpokofood();
        $food3->setName("Banane");
        $food3->setStatus("on");
        $food3->setAmount(5);

        $manager->persist($food);
        $manager->persist($food2);
        $manager->persist($food3);

        $manager->flush();
    }
}
