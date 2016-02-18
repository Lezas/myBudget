<?php

namespace UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BudgetUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}