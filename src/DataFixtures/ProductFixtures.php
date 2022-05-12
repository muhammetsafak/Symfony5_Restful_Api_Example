<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 0; $i < 50; ++$i) {
            $product = new Product();
            $product->setName($faker->word)
                ->setPrice($faker->randomFloat(2, 5))
                ->setStatus(1)
                ->setStock(rand(100, 500));
            $manager->persist($product);
        }
        $manager->flush();
    }
}
