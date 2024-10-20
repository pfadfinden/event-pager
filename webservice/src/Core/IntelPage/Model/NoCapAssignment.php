<?php

declare(strict_types=1);

namespace App\Core\IntelPage\Model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
readonly class NoCapAssignment extends AbstractCapAssignment
{
    public const string DISCRIMINATOR = 'no';
}
