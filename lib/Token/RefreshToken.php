<?php

namespace OAuth2\Token;

class RefreshToken extends Token implements RefreshTokenInterface
{
    /**
     * @var bool
     */
    protected $used;

    /**
     * @return bool
     */
    public function isUsed()
    {
        return $this->used;
    }

    /**
     * @param bool $used
     *
     * @return self
     */
    public function setUsed($used)
    {
        $this->used = $used;

        return $this;
    }
}
