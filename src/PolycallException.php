<?php

declare(strict_types=1);

namespace OBINexus\Polycall;

final class PolycallException extends \RuntimeException
{
    private int $status;

    public function __construct(
        string $message,
        int $status,
        ?\Throwable $previous = null
    ) {
        $this->status = $status;
        parent::__construct("{$message} (status={$status})", $status, $previous);
    }

    public function status(): int
    {
        return $this->status;
    }
}
