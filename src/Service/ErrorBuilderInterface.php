<?php
declare(strict_types=1);

namespace Maba\Bundle\RestBundle\Service;

use Exception;
use Maba\Bundle\RestBundle\Entity\Error;

interface ErrorBuilderInterface
{
    public function createErrorFromException(Exception $exception): Error;
}
