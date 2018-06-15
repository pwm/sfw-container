<?php
declare(strict_types=1);

namespace SFW\Container\Exception;

use RuntimeException;

class CycleDetected extends RuntimeException
{
}
