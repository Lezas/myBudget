<?php
namespace BudgetBundle\Entity;

use CategoryBundle\Entity\Category;
use MainBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/** @ORM\MappedSuperclass */
class Budget
{

    /**
     * @ORM\Column(type="integer", unique=true, name="id")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, name="name")
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="datetime", name="earn_date_time")
     */
    protected $dateTime;

    /**
     * @ORM\ManyToOne(targetEntity = "MainBundle\Entity\User", inversedBy = "incomesUser")
     * @ORM\JoinColumn(name = "user_id", referencedColumnName = "id")
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity = "CategoryBundle\Entity\Category", inversedBy = "ExpensesCategory")
     * @ORM\JoinColumn(name = "category_id", referencedColumnName = "id")
     * @var Category
     */
    protected $category;

    /**
     * @ORM\Column(type="decimal", precision=8, scale=2, name="money")
     * @var float
     */
    protected $money;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set earnDateTime
     *
     * @param \DateTime $dateTime
     *
     * @return Budget
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    /**
     * Get spendDateTime
     *
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Set spendMoney
     *
     * @param float $money
     *
     * @return Budget
     */
    public function setMoney($money)
    {
        $this->money = $money;

        return $this;
    }

    /**
     * Get spendMoney
     *
     * @return string
     */
    public function getMoney()
    {
        return $this->money;
    }

    /**
     * Set user
     *
     * @param \MainBundle\Entity\User $user
     *
     * @return Budget
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \MainBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set category
     *
     * @param \CategoryBundle\Entity\Category $category
     *
     * @return Budget
     */
    public function setCategory($category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return \CategoryBundle\Entity\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Budget
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}