<?php

declare(strict_types=1);

namespace App\View\Web\SendMessage\Form;

use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

/**
 * Required for a form rendering a list of recipient choices.
 */
interface RecipientsChoiceLoader extends ChoiceLoaderInterface
{
}
