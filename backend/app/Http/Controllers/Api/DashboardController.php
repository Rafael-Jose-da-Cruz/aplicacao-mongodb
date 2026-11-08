<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $statistics = Character::raw(function ($collection) {
            $status = [];
            $statusGroups = $collection->aggregate([
                ['$group' => ['_id' => '$status', 'total' => ['$sum' => 1]]],
                ['$sort' => ['total' => -1]],
            ]);

            foreach ($statusGroups as $group) {
                $status[(string) ($group['_id'] ?? 'unknown')] = (int) $group['total'];
            }

            $speciesChart = [];
            $speciesGroups = $collection->aggregate([
                ['$group' => ['_id' => '$species', 'total' => ['$sum' => 1]]],
                ['$sort' => ['total' => -1]],
                ['$limit' => 8],
            ]);

            foreach ($speciesGroups as $group) {
                $speciesChart[] = [
                    'label' => (string) ($group['_id'] ?? 'Não informado'),
                    'value' => (int) $group['total'],
                ];
            }

            $originRanking = [];
            $originGroups = $collection->aggregate([
                ['$group' => ['_id' => '$origin', 'total' => ['$sum' => 1]]],
                ['$sort' => ['total' => -1]],
                ['$limit' => 5],
            ]);

            foreach ($originGroups as $group) {
                $originRanking[] = [
                    'name' => (string) ($group['_id'] ?? 'Não informado'),
                    'total' => (int) $group['total'],
                ];
            }

            $categoryRows = [];
            $categoryGroups = $collection->aggregate([
                ['$group' => [
                    '_id' => ['status' => '$status', 'species' => '$species'],
                    'total' => ['$sum' => 1],
                ]],
                ['$sort' => ['_id.status' => 1, 'total' => -1]],
            ]);

            foreach ($categoryGroups as $group) {
                $statusName = (string) ($group['_id']['status'] ?? 'unknown');
                $categoryRows[$statusName][] = [
                    'name' => (string) ($group['_id']['species'] ?? 'Não informado'),
                    'total' => (int) $group['total'],
                ];
            }

            $grouping = [];

            foreach ($categoryRows as $statusName => $species) {
                $grouping[] = [
                    'status' => $statusName,
                    'total' => array_sum(array_column($species, 'total')),
                    'species' => $species,
                ];
            }

            $average = $collection->aggregate([
                ['$group' => ['_id' => null, 'value' => ['$avg' => '$episode_count']]],
            ])->toArray();

            return [
                'total' => $collection->countDocuments(),
                'status' => $status,
                'species_chart' => $speciesChart,
                'origin_ranking' => $originRanking,
                'grouping' => $grouping,
                'average_episodes' => round((float) ($average[0]['value'] ?? 0), 2),
                'species_count' => count($collection->distinct('species')),
                'origin_count' => count($collection->distinct('origin')),
            ];
        });

        $statusChart = [];

        foreach ($statistics['status'] as $label => $value) {
            $statusChart[] = ['label' => $label, 'value' => $value];
        }

        $topCharacters = Character::query()
            ->orderByDesc('episode_count')
            ->limit(5)
            ->get()
            ->map(fn (Character $character) => [
                'id' => (string) $character->getKey(),
                'name' => $character->name,
                'image' => $character->image,
                'status' => $character->status,
                'episode_count' => $character->episode_count,
            ])
            ->values();

        return response()->json([
            'indicators' => [
                'total' => $statistics['total'],
                'alive' => $statistics['status']['Alive'] ?? 0,
                'dead' => $statistics['status']['Dead'] ?? 0,
                'unknown' => $statistics['status']['unknown'] ?? 0,
                'species' => $statistics['species_count'],
                'origins' => $statistics['origin_count'],
                'average_episodes' => $statistics['average_episodes'],
            ],
            'charts' => [
                'status' => $statusChart,
                'species' => $statistics['species_chart'],
            ],
            'ranking' => [
                'origins' => $statistics['origin_ranking'],
                'characters' => $topCharacters,
            ],
            'grouping' => [
                'by_status' => $statistics['grouping'],
            ],
        ]);
    }
}
