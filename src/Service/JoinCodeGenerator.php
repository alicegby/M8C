<?php

namespace App\Service;

use App\Repository\GameSessionRepository;

class JoinCodeGenerator
{
    public function __construct(
        private GameSessionRepository $repository
    ) {}

    public function generate(): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        } while ($this->repository->findOneBy(['joinCode' => $code]));

        return $code;
    }
}