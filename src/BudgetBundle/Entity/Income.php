<?php

namespace BudgetBundle\Entity;

use MainBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="income")
 * @ORM\Entity(repositoryClass="BudgetBundle\Repository\IncomeRepository")
 */

class Income
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
     * @ORM\Column(type="decimal", precision=8, scale=2, name="earned_money")
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
     * @return Income
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;

        return $this;
    }

    /**
     * Get dateTime
     *
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * Set money
     *
     * @param string $money
     *
     * @return Income
     */
    public function setMoney($money)
    {
        $this->money = $money;

        return $this;
    }

    /**
     * Get money
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
     * @return Income
     */
    public function setUser(\MainBundle\Entity\User $user = null)
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
     * Set name
     *
     * @param string $name
     *
     * @return Income
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
