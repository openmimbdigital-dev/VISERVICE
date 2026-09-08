<?php

namespace App\Livewire\Ui;

use App\Support\SearchableSelectCreate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class SearchableSelect extends Component
{
    #[Modelable]
    public mixed $value = null;

    public string $search = '';

    #[Locked]
    public string $modelClass;

    /** @var list<string> */
    #[Locked]
    public array $searchBy = ['name'];

    #[Locked]
    public string $labelField = 'name';

    #[Locked]
    public string $valueField = 'id';

    #[Locked]
    public string $placeholder = 'Seleccionar';

    #[Locked]
    public string $searchPlaceholder = 'Buscar...';

    #[Locked]
    public string $emptyText = 'Sin coincidencias';

    #[Locked]
    public string $orderBy = 'name';

    /** @var array<string, mixed> */
    #[Locked]
    public array $filters = [];

    #[Locked]
    public int $limit = 25;

    #[Locked]
    public bool $disabled = false;

    #[Locked]
    public bool $allowCreate = true;

    public bool $showCreateModal = false;

    #[Reactive]
    public mixed $invalid = false;

    /**
     * @param  list<string>|string  $searchBy
     * @param  array<string, mixed>  $filters
     */
    public function mount(
        string $modelClass,
        array|string $searchBy = 'name',
        string $labelField = 'name',
        string $valueField = 'id',
        string $placeholder = 'Seleccionar',
        string $searchPlaceholder = 'Buscar...',
        string $emptyText = 'Sin coincidencias',
        string $orderBy = 'name',
        array $filters = [],
        int $limit = 25,
        bool $disabled = false,
        mixed $invalid = false,
        bool $allowCreate = true,
    ): void {
        abort_unless(
            is_subclass_of($modelClass, Model::class)
            && str_starts_with($modelClass, 'App\\Models\\'),
            403
        );

        $this->modelClass = $modelClass;
        $this->searchBy = $this->normalizeFields($searchBy);
        $this->labelField = $this->assertField($labelField);
        $this->valueField = $this->assertField($valueField);
        $this->placeholder = $placeholder;
        $this->searchPlaceholder = $searchPlaceholder;
        $this->emptyText = $emptyText;
        $this->orderBy = $this->assertField($orderBy);
        $this->filters = $filters;
        $this->limit = max(1, min(50, $limit));
        $this->disabled = $disabled;
        $this->invalid = (bool) $invalid;
        $this->allowCreate = $allowCreate;
    }

    public function updatedInvalid(mixed $value): void
    {
        $this->invalid = (bool) $value;
    }

    public function select(mixed $id): void
    {
        if ($this->disabled) {
            return;
        }

        if ($id === '' || $id === null) {
            $this->value = null;
        } else {
            $this->value = is_numeric($id) ? (int) $id : $id;
        }
        $this->search = '';
    }

    public function openCreateModal(): void
    {
        if ($this->disabled || ! $this->canCreate()) {
            return;
        }

        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    #[On('searchable-created')]
    public function onRecordCreated(int $id, ?string $modelClass = null): void
    {
        if ($modelClass !== null && $modelClass !== $this->modelClass) {
            return;
        }

        $this->select($id);
        $this->showCreateModal = false;
    }

    #[On('searchable-create-closed')]
    public function onCreateClosed(?string $modelClass = null): void
    {
        if ($modelClass !== null && $modelClass !== $this->modelClass) {
            return;
        }

        $this->showCreateModal = false;
    }

    public function render()
    {
        $create = $this->createConfig();

        return view('livewire.ui.searchable-select', [
            'options'          => $this->options(),
            'selected_label'   => $this->selectedLabel(),
            'can_create'       => $this->canCreate(),
            'create_component' => $create['component'] ?? null,
            'create_button'    => $create['button'] ?? 'Crear registro',
            'is_searching'     => trim($this->search) !== '',
            'invalid'          => (bool) $this->invalid,
        ]);
    }

    /** @return Collection<int, array{value: string, label: string, hint: string}> */
    protected function options(): Collection
    {
        $query = $this->baseQuery();
        $term = trim($this->search);

        if ($term !== '') {
            $query->where(function (Builder $inner) use ($term) {
                foreach ($this->searchBy as $field) {
                    $inner->orWhere($this->qualify($field), 'like', $term.'%');
                }
            });
            $query->orderBy($this->qualify($this->searchBy[0] ?? $this->orderBy));
        } else {
            $query->orderBy($this->qualify($this->orderBy));
        }

        return $query
            ->limit($this->limit)
            ->get()
            ->map(fn (Model $row) => $this->mapOption($row))
            ->values();
    }

    protected function canCreate(): bool
    {
        $create = $this->createConfig();

        if (! $this->allowCreate || $create === null) {
            return false;
        }

        return auth()->user()?->can($create['permission']) ?? false;
    }

    /** @return array{component: string, permission: string, button: string, title: string}|null */
    protected function createConfig(): ?array
    {
        return SearchableSelectCreate::for($this->modelClass);
    }

    protected function selectedLabel(): ?string
    {
        $selected = $this->selectedRecord();

        if (! $selected) {
            return null;
        }

        $label = data_get($selected, $this->labelField);

        return $label !== null && $label !== '' ? (string) $label : null;
    }

    protected function selectedRecord(): ?Model
    {
        if ($this->value === null || $this->value === '') {
            return null;
        }

        return $this->tenantQuery()->whereKey($this->value)->first();
    }

    protected function tenantQuery(): Builder
    {
        /** @var Model $model */
        $model = new $this->modelClass;
        $query = $this->modelClass::query();

        if (method_exists($model, 'scopeForAuthUser')) {
            $query->forAuthUser();
        } elseif (method_exists($model, 'scopeVisibleToUser')) {
            $query->visibleToUser();
        }

        return $query;
    }

    protected function baseQuery(): Builder
    {
        $query = $this->tenantQuery();

        foreach ($this->filters as $column => $value) {
            if ($column === 'exclude_ids') {
                $ids = array_values(array_filter(
                    array_map(fn ($id) => (int) $id, (array) $value),
                    fn (int $id) => $id > 0
                ));

                if ($ids !== []) {
                    $query->whereNotIn($this->qualify($this->valueField), $ids);
                }

                continue;
            }

            $query->where($this->qualify($this->assertField((string) $column)), $value);
        }

        return $query;
    }

    /** @return array{value: string, label: string, hint: string} */
    protected function mapOption(Model $row): array
    {
        $label = (string) data_get($row, $this->labelField);
        $hints = [];

        foreach ($this->searchBy as $field) {
            if ($field === $this->labelField) {
                continue;
            }

            $hint = data_get($row, $field);

            if ($hint !== null && $hint !== '' && ! str_contains($label, (string) $hint)) {
                $hints[] = (string) $hint;
            }
        }

        return [
            'value' => (string) data_get($row, $this->valueField),
            'label' => $label,
            'hint'  => implode(' · ', $hints),
        ];
    }

    protected function qualify(string $field): string
    {
        /** @var Model $model */
        $model = new $this->modelClass;

        return $model->getTable().'.'.$field;
    }

    /** @param  list<string>|string  $fields */
    protected function normalizeFields(array|string $fields): array
    {
        $list = is_array($fields) ? $fields : explode(',', $fields);

        $normalized = [];

        foreach ($list as $field) {
            $field = trim((string) $field);

            if ($field === '') {
                continue;
            }

            $normalized[] = $this->assertField($field);
        }

        return $normalized !== [] ? array_values(array_unique($normalized)) : ['name'];
    }

    protected function assertField(string $field): string
    {
        abort_unless((bool) preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $field), 400);

        return $field;
    }
}
