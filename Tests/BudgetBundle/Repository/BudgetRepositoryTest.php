<?php
namespace Tests\BudgetBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
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

        $this->assertCount(2, $expenses);

        $dateFrom = new \DateTime('2016-01-05 23:32');
        $dateTo = new \DateTime('2016-01-06 23:34');

        $expenses = $this->em
            ->getRepository('BudgetBundle:Expenses')
            ->getByDateRange($user, $dateFrom, $dateTo);

        $this->assertCount(2, $expenses);
    }

    public function testGetByDateRangeWithoutCategories()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);

        $dateFrom = new \DateTime('2016-01-05 23:32');
        $dateTo = new \DateTime('2016-01-06 23:34');

        $expenses = $this->em
            ->getRepository('BudgetBundle:Expenses')
            ->getByDateRangeWithoutCategories($user, $dateFrom, $dateTo);

        $this->assertCount(2, $expenses);
    }

    public function testGetByDateRangeWithCategories()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $category = $this->em->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'expense']);

        $dateFrom = new \DateTime('2016-01-10 23:32');
        $dateTo = new \DateTime('2016-01-20 23:34');

        $categoryIds = new ArrayCollection();
        $categoryIds->add($category->getId());

        $expenses = $this->em
            ->getRepository('BudgetBundle:Expenses')
            ->getByDateRangeAndCategories($user, $dateFrom, $dateTo, $categoryIds->toArray());

        $this->assertCount(2, $expenses);
    }

    public function testGetWithoutCategories()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);

        $expenses = $this->em
            ->getRepository('BudgetBundle:Expenses')
            ->getWithoutCategories($user);

        $this->assertCount(2, $expenses);
    }

    public function testGetWithCategories()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $expenses = $this->em
            ->getRepository('BudgetBundle:Expenses')
            ->getWithCategories($user);

        $this->assertCount(2, $expenses);
    }

    public function testGetFirst()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $expense = $this->em
            ->getRepository('BudgetBundle:Expenses')
            ->getFirst($user);

        $this->assertEquals('maistas', $expense[0]->getName());
    }

    public function testGetByCategories()
    {
        $user = $this->em->getRepository('MainBundle:User')->findOneBy(['username' => 'admin']);
        $category = $this->em->getRepository('CategoryBundle:Category')->findOneBy(['name' => 'expense']);

        $categoryIds = new ArrayCollection();
        $categoryIds->add($category->getId());

        $expenses = $this->em
            ->getRepository('BudgetBundle:Expenses')
            ->getByCategories($user, $categoryIds->toArray());

        $this->assertCount(2, $expenses);
    }


}