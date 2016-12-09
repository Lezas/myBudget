<?php

namespace BudgetBundle\DataFixtures\ORM;

use BudgetBundle\Entity\Expenses;
use CategoryBundle\Entity\Category;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use MainBundle\Entity\User;

class LoadExpenseData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        //get user
        $user = $manager->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);

        $expense = new Expenses();
        $expense->setUser($user);
        $expense->setDateTime(new \DateTime('2016-01-05 23:33'));
        $expense->setMoney(99.99);
        $expense->setName('maistas');

        $manager->persist($expense);

        $expense = new Expenses();
        $expense->setUser($user);
        $expense->setDateTime(new \DateTime('2016-01-06 23:33'));
        $expense->setMoney(89.99);
        $expense->setName('kitos islaidos');

        $manager->persist($expense);
        $manager->flush();
    }
}