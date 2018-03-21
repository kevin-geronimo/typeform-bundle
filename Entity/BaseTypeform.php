<?php

namespace TypeformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BaseTypeform.
 *
 * @ORM\MappedSuperclass()
 */
abstract class BaseTypeform
{
    /**
     * @var string
     * @ORM\Column(name="form", type="string", length=255, nullable=false)
     */
    private $form;

    /**
     * @var string
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @var string
     * @ORM\Column(name="address_ip", type="string", nullable=false)
     */
    private $addressIp;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="submit_at", type="datetime", length=255, nullable=true)
     */
    private $submitAt;

    /**
     * @param string $form
     */
    public function setForm(string $form): void
    {
        $this->form = $form;
    }

    /**
     * @return string
     */
    public function getForm(): string
    {
        return $this->form;
    }

    /**
     * Set token.
     *
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set addressIp.
     *
     * @param string $addressIp
     *
     * @return $this
     */
    public function setAddressIp($addressIp)
    {
        $this->addressIp = $addressIp;

        return $this;
    }

    /**
     * Get addressIp.
     *
     * @return string
     */
    public function getAddressIp()
    {
        return $this->addressIp;
    }

    /**
     * Set submitAt.
     *
     * @param \DateTime $submitAt
     *
     * @return $this
     */
    public function setSubmitAt($submitAt)
    {
        $this->submitAt = $submitAt;

        return $this;
    }

    /**
     * Get submitAt.
     *
     * @return \DateTime
     */
    public function getSubmitAt()
    {
        return $this->submitAt;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->secret,
            $this->addressIp,
            $this->submitAt,
        ));
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        list(
            $this->id,
            $this->secret,
            $this->addressIp,
            $this->submitAt
            ) = $data;

        return $this;
    }
}
