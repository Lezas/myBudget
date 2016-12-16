<?php

namespace Tests\BudgetBundle\Repository;

use BudgetBundle\Repository\BudgetRepositoryService;
use Tests\TestCase\FunctionalTestCase;

/**
 * Class BudgetRepositoryServiceTest
 * @package Tests\BudgetBundle\Repository
 */
class BudgetRepositoryServiceTest extends FunctionalTestCase
{
    public function testGetMonthBudget()
    {
        $date = new \DateTime('2016-01-15');
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $service = new BudgetRepositoryService(
            static::$kernel->getContainer()->get('doctrine')
        );

        $budget = $service->getMonthBudget($date, $user);

        $this->assertCount(2, $budget);
        $this->assertArrayHasKey('income', $budget);
        $this->assertArrayHasKey('expenses', $budget);
        $this->assertCount(4, $budget['income']);
        $this->assertCount(4, $budget['expenses']);
    }

    public function testGetMonthBudget_dateIsNotDefined()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $service = new BudgetRepositoryService(
            static::$kernel->getContainer()->get('doctrine')
        );

        $budget = $service->getMonthBudget(null, $user);

        $this->assertCount(2, $budget);
        $this->assertArrayHasKey('income', $budget);
        $this->assertArrayHasKey('expenses', $budget);
        $this->assertCount(0, $budget['income']);
        $this->assertCount(0, $budget['expenses']);

    }
}