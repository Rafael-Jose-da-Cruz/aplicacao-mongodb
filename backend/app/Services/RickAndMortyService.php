<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class RickAndMortyService
{
    private const BASE_URL = 'https://rickandmortyapi.com/api';

    public function fetchCharacters(int $count): array
    {
        $characters = [];
        $page = 1;

        while (count($characters) < $count) {
            $response = Http::timeout(20)
                ->retry(3, 300)
                ->get(self::BASE_URL.'/character', ['page' => $page]);

            if ($response->failed()) {
                throw new RuntimeException('Não foi possível consultar a Rick and Morty API.');
            }

            $results = $response->json('results', []);

            if (empty($results)) {
                break;
            }

            $characters = array_merge($characters, $results);
            $page++;

            if (! $response->json('info.next')) {
                break;
            }
        }

        return array_slice($characters, 0, $count);
    }
}
