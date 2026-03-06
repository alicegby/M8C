<?php

namespace App\Service;

use MongoDB\Client;
use MongoDB\Collection;

class MongoService
{
    private ?Client $client = null;
    private bool $available = false;
    private bool $connectionAttempted = false;

    public function __construct(private string $uri, private string $database) {}

    private function connect(): void
    {
        if ($this->connectionAttempted) return;
        $this->connectionAttempted = true;

        try {
            $this->client = new Client($this->uri);
            // Pas de ping — connexion ouverte uniquement à la première vraie requête
            $this->available = true;
        } catch (\Throwable) {
            $this->available = false;
        }
    }

    public function isAvailable(): bool
    {
        $this->connect();
        return $this->available;
    }

    public function getCollection(string $name): ?Collection
    {
        $this->connect();
        if (!$this->available) return null;
        return $this->client->selectCollection($this->database, $name);
    }

    public function saveRegistration(string $userId, string $authProvider, \DateTimeInterface $registeredAt): void
    {
        $col = $this->getCollection('user_registration_stats');
        if (!$col) return;
        try {
            $col->insertOne([
                'userId'       => $userId,
                'authProvider' => $authProvider,
                'registeredAt' => new \MongoDB\BSON\UTCDateTime($registeredAt->getTimestamp() * 1000),
            ]);
        } catch (\Throwable) {}
    }

    public function getRegistrationsByPeriod(?\DateTime $start = null, ?\DateTime $end = null): array
    {
        $col = $this->getCollection('user_registration_stats');
        if (!$col) return [];
        try {
            $match = [];
            if ($start) $match['registeredAt']['$gte'] = new \MongoDB\BSON\UTCDateTime($start->getTimestamp() * 1000);
            if ($end)   $match['registeredAt']['$lte'] = new \MongoDB\BSON\UTCDateTime($end->getTimestamp() * 1000);

            $pipeline = [
                ['$match' => $match ?: (object)[]],
                ['$group' => [
                    '_id'   => ['$dateToString' => ['format' => '%Y-%m', 'date' => '$registeredAt']],
                    'count' => ['$sum' => 1],
                ]],
                ['$sort' => ['_id' => 1]],
            ];

            return array_map(fn($r) => [
                'month' => (string)$r['_id'],
                'count' => (int)$r['count'],
            ], $col->aggregate($pipeline)->toArray());

        } catch (\Throwable) {
            return [];
        }
    }

    public function savePurchase(array $data): void
    {
        $col = $this->getCollection('purchase_stats');
        if (!$col) return;
        try { $col->insertOne($data); } catch (\Throwable) {}
    }

    public function saveGame(array $data): void
    {
        $col = $this->getCollection('game_stats');
        if (!$col) return;
        try { $col->insertOne($data); } catch (\Throwable) {}
    }
}