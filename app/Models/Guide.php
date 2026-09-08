<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Guía interna del sistema: documentación de un módulo o el paso a paso para
 * poner en marcha algo (onboarding).
 *
 * No pertenece a ningún negocio: es material de la plataforma y solo lo ve el
 * superAdmin.
 */
class Guide extends Model
{
    use SoftDeletes;

    public const TYPE_ONBOARDING = 'onboarding';

    public const TYPE_DOCUMENTATION = 'documentation';

    protected $fillable = [
        'title', 'slug', 'module', 'type', 'summary',
        'content', 'sort_order', 'published', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'published'  => 'boolean',
        ];
    }

    /** @return array<string, string> */
    public static function types(): array
    {
        return [
            self::TYPE_ONBOARDING    => 'Puesta en marcha',
            self::TYPE_DOCUMENTATION => 'Documentación',
        ];
    }

    public function typeLabel(): string
    {
        return self::types()[$this->type] ?? $this->type;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    /**
     * Slug único a partir del título. Se numera si ya existe, porque el título
     * es lo único que el autor escribe y no tiene por qué ser irrepetible.
     */
    public static function generateSlug(string $title, ?int $ignore_id = null): string
    {
        $base = Str::slug($title) ?: 'guia';
        $slug = $base;
        $suffix = 2;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($ignore_id, fn ($query) => $query->whereKeyNot($ignore_id))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * El contenido en Markdown, convertido a HTML.
     *
     * Se escapa el HTML de entrada: lo escribe el superAdmin, pero no hay razón
     * para permitir marcado arbitrario en una página que otros van a abrir.
     */
    public function renderedContent(): string
    {
        $html = Str::markdown($this->content ?? '', [
            'html_input'         => 'escape',
            'allow_unsafe_links' => false,
        ]);

        // Se le pone ancla a cada h2 para que el índice lateral pueda saltar ahí.
        return preg_replace_callback(
            '/<h2>(.*?)<\/h2>/s',
            fn (array $match) => '<h2 id="'.Str::slug(strip_tags($match[1])).'">'.$match[1].'</h2>',
            $html
        ) ?? $html;
    }

    /** Índice de los encabezados de nivel 2, para la tabla de contenido. */
    public function headings(): array
    {
        preg_match_all('/^##\s+(.+)$/m', (string) $this->content, $matches);

        return array_map(
            fn (string $heading) => ['title' => trim($heading), 'anchor' => Str::slug($heading)],
            $matches[1] ?? []
        );
    }
}
