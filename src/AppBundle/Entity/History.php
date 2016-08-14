<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * History
 *
 * @ORM\Table(name="history", options={"collate": "utf8_general_ci", "character": "utf8"})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HistoryRepository")
 */
class History
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="ip_id", type="integer", nullable=false, unique=false, options={"comment": "ip id"})
     */
    private $ipId;

    /**
     * @var integer
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false, unique=false, options={"comment": "用户id"})
     */
    private $userId;

    /**
     * @var integer
     *
     * @ORM\Column(name="db", type="integer", nullable=false, unique=false, options={"comment": "数据库"})
     */
    private $db;

    /**
     * @var integer
     *
     * @ORM\Column(name="command", type="string", nullable=false, unique=false, options={"comment": "命令"})
     */
    private $key;

    /**
     * @var integer
     *
     * @ORM\Column(name="params", type="string", length=500, nullable=false, unique=false, options={"comment": "值"})
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true, unique=false, options={"comment": "创建时间"})
     */
    private $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var Address
     *
     * @ORM\ManyToOne(targetEntity="Address")
     * @ORM\JoinColumn(name="ip_id", referencedColumnName="id")
     */
    private $address;

    function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ipId
     *
     * @param integer $ipId
     *
     * @return History
     */
    public function setIpId($ipId)
    {
        $this->ipId = $ipId;

        return $this;
    }

    /**
     * Get ipId
     *
     * @return integer
     */
    public function getIpId()
    {
        return $this->ipId;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     *
     * @return History
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set db
     *
     * @param integer $db
     *
     * @return History
     */
    public function setDb($db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Get db
     *
     * @return integer
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Set key
     *
     * @param string $key
     *
     * @return History
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return History
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return History
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return History
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set address
     *
     * @param \AppBundle\Entity\Address $address
     *
     * @return History
     */
    public function setAddress(\AppBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \AppBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }
}
