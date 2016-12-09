<?php
namespace Tests\BudgetBundle\Repository;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;

class BudgetRepositoryTest extends WebTestCase
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

    public function testGetByDateRange()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $dateFrom = '2016-01-01';
        $dateTo = '2016-01-10';

        $expenses = $this->em
            ->getRepository('BudgetBundle:Expenses')
            ->getByDateRange($user, $dateFrom, $dateTo);
        ;

        $this->assertCount(2, $expenses);

        $dateFrom = new \DateTime('2016-01-01');
        $dateTo = new \DateTime('2016-01-10');

        $expenses = $this->em
            ->getRepository('BudgetBundle:Expenses')
            ->getByDateRange($user, $dateFrom, $dateTo);
        ;

        $this->assertCount(2, $expenses);
    }


}