<?php

namespace Tests\BudgetBundle\Repository;

use BudgetBundle\Repository\BudgetRepositoryService;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class BudgetRepositoryServiceTest extends WebTestCase
{
    protected static $application;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    protected function setUp()
    {
        self::runCommand('doctrine:database:drop --force --env=test');
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:update --force');
        self::runCommand('doctrine:fixtures:load');

        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }

    protected static function runCommand($command)
    {
        $command = sprintf('%s --quiet', $command);

        return self::getApplication()->run(new StringInput($command));
    }

    protected static function getApplication()
    {
        if (null === self::$application) {
            $client = static::createClient();

            self::$application = new Application($client->getKernel());
            self::$application->setAutoExit(false);
        }

        return self::$application;
    }

    public function testGetMonthBudget()
    {
        $date = new \DateTime('2016-01-15');
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $service = new BudgetRepositoryService(static::$kernel->getContainer()
            ->get('doctrine'));

        $budget = $service->getMonthBudget($date,$user);

        $this->assertCount(2,$budget);
        $this->assertArrayHasKey('income', $budget);
        $this->assertArrayHasKey('expenses', $budget);
        $this->assertCount(4,$budget['income']);
        $this->assertCount(4,$budget['expenses']);

    }
}