<?php

declare(strict_types=1);

namespace App\Message;

class CommentMessage
{
    public function __construct(
        private readonly int $id,
        private readonly string $reviewUrl,
        private readonly array $context = [],
    ) {
    }

    public function getReviewUrl(): string
    {
        return $this->reviewUrl;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
