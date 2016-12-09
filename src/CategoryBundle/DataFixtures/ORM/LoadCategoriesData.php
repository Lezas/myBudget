<?php

namespace CategoryBundle\DataFixtures\ORM;

use CategoryBundle\Entity\Category;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MainBundle\Entity\User;

class LoadCategoriesData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //get user
        $user = $manager->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);

        $category = new Category();

        $category->setUser($user);
        $category->setType('expense');
        $category->setName('expense');
        $category->setValid('1');

        $manager->persist($category);
        $manager->flush();
    }
}