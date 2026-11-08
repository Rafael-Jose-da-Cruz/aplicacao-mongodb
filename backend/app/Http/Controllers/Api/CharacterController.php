<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Services\RickAndMortyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class CharacterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:50'],
            'species' => ['nullable', 'string', 'max:100'],
            'gender' => ['nullable', 'string', 'max:50'],
            'origin' => ['nullable', 'string', 'max:150'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort' => ['nullable', Rule::in(['external_id', 'name', 'status', 'species', 'gender', 'origin', 'episode_count', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 20);
        $sort = $filters['sort'] ?? 'external_id';
        $direction = $filters['direction'] ?? 'asc';

        $query = Character::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('species', 'like', "%{$search}%")
                    ->orWhere('origin', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        foreach (['status', 'species', 'gender', 'origin'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }

        $total = $query->count();

        $characters = $query
            ->orderBy($sort, $direction)
            ->skip(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn (Character $character) => $character->makeHidden('episodes'));

        return response()->json([
            'data' => $characters,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $character = Character::findOrFail($id);

        return response()->json(['data' => $character]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate($this->rules());

        if (array_key_exists('episodes', $data) && ! array_key_exists('episode_count', $data)) {
            $data['episode_count'] = count($data['episodes'] ?? []);
        }

        $character = Character::create($data);

        return response()->json(['data' => $character], 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $character = Character::findOrFail($id);
        $data = $request->validate($this->rules($request->isMethod('patch')));

        if (array_key_exists('episodes', $data)) {
            $data['episode_count'] = count($data['episodes'] ?? []);
        }

        $character->fill($data);
        $character->save();

        return response()->json(['data' => $character->fresh()]);
    }

    public function destroy(string $id): JsonResponse
    {
        $character = Character::findOrFail($id);
        $character->delete();

        return response()->json(['message' => 'Personagem removido com sucesso.']);
    }

    public function sync(Request $request, RickAndMortyService $service): JsonResponse
    {
        $data = $request->validate([
            'count' => ['nullable', 'integer', 'min:1', 'max:826'],
        ]);

        $count = (int) ($data['count'] ?? 30);

        try {
            $characters = $service->fetchCharacters($count);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => $exception->getMessage(),
            ], 502);
        }

        $created = 0;
        $updated = 0;

        foreach ($characters as $character) {
            $model = Character::updateOrCreate(
                ['external_id' => (int) $character['id']],
                $this->fromApi($character),
            );

            $model->wasRecentlyCreated ? $created++ : $updated++;
        }

        return response()->json([
            'message' => 'Sincronização concluída com sucesso.',
            'requested' => $count,
            'imported' => count($characters),
            'created' => $created,
            'updated' => $updated,
            'total' => Character::count(),
        ]);
    }

    private function rules(bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return [
            'external_id' => ['nullable', 'integer', 'min:1'],
            'name' => [$required, 'string', 'max:150'],
            'status' => [$required, Rule::in(['Alive', 'Dead', 'unknown'])],
            'species' => [$required, 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:100'],
            'gender' => [$required, Rule::in(['Female', 'Male', 'Genderless', 'unknown'])],
            'origin' => ['nullable', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:150'],
            'image' => ['nullable', 'url', 'max:500'],
            'episode_count' => ['nullable', 'integer', 'min:0'],
            'episodes' => ['nullable', 'array'],
            'episodes.*' => ['string'],
            'api_url' => ['nullable', 'url', 'max:500'],
            'api_created_at' => ['nullable', 'string', 'max:100'],
        ];
    }

    private function fromApi(array $character): array
    {
        return [
            'external_id' => (int) $character['id'],
            'name' => $character['name'],
            'status' => $character['status'],
            'species' => $character['species'],
            'type' => filled($character['type'] ?? null) ? $character['type'] : null,
            'gender' => $character['gender'],
            'origin' => data_get($character, 'origin.name'),
            'location' => data_get($character, 'location.name'),
            'image' => $character['image'] ?? null,
            'episode_count' => count($character['episode'] ?? []),
            'episodes' => $character['episode'] ?? [],
            'api_url' => $character['url'] ?? null,
            'api_created_at' => $character['created'] ?? null,
            'synced_at' => now(),
        ];
    }
}
