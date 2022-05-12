<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = $manager->getRepository('App:User');
        $user->add('Ahmet', 'ahmet@example.com', 'ahmet');
        $user->add('Mehmet', 'mehmet@example.com', 'mehmet');
        $user->add('Selim', 'selim@example.com', 'selim');
    }

}
