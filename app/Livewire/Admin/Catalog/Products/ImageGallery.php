<?php

namespace App\Livewire\Admin\Catalog\Products;

use App\Actions\Catalog\DeleteProductImageAction;
use App\Actions\Catalog\StoreProductImagesAction;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\DeleteConfirmationAlert;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImageGallery extends Component
{
    use LivewireAlert;
    use WithFileUploads;

    /**
     * La confirmación usa un evento propio porque este componente convive con la
     * ficha del producto, que también confirma borrados: con el evento genérico
     * ambas reaccionarían a la misma respuesta.
     */
    private const CONFIRMED_EVENT = 'product-image-delete-confirmed';

    public ?int $deleting_image_id = null;

    public Product $product;

    /** @var array<int, mixed> */
    public array $new_images = [];

    public function mount(Product $product): void
    {
        $this->product = $product;
    }

    public function updatedNewImages(): void
    {
        abort_unless($this->product->isEditableBy(), 403);

        $this->validate([
            'new_images'   => ['array', 'max:10'],
            'new_images.*' => ['image', 'mimes:jpg,jpeg,jfif,png,webp', 'max:4096'],
        ], [
            'new_images.max'   => 'Puedes subir hasta 10 imágenes a la vez.',
            'new_images.*.image' => 'El archivo debe ser una imagen.',
            'new_images.*.mimes' => 'Formatos permitidos: JPG, PNG o WebP.',
            'new_images.*.max'   => 'Cada imagen no debe superar 4 MB.',
        ]);

        StoreProductImagesAction::run($this->product, $this->new_images);

        $this->new_images = [];
        $this->product->refresh();

        $this->dispatch('swal', ['title' => 'Imágenes agregadas', 'icon' => 'success']);
    }

    public function setPrimary(int $image_id): void
    {
        abort_unless($this->product->isEditableBy(), 403);

        $image = ProductImage::query()->where('product_id', $this->product->id)->findOrFail($image_id);

        $this->product->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        $this->product->refresh();
    }

    public function deleteImage(int $image_id): void
    {
        abort_unless($this->product->isEditableBy(), 403);

        abort_unless(
            ProductImage::query()->where('product_id', $this->product->id)->whereKey($image_id)->exists(),
            404
        );

        $this->deleting_image_id = $image_id;

        $this->confirm(
            '¿Eliminar esta imagen del producto?',
            DeleteConfirmationAlert::options(self::CONFIRMED_EVENT)
        );
    }

    #[On(self::CONFIRMED_EVENT)]
    public function deleteConfirmedImage(): void
    {
        abort_unless($this->product->isEditableBy(), 403);

        $image = ProductImage::query()
            ->where('product_id', $this->product->id)
            ->find($this->deleting_image_id);

        $this->deleting_image_id = null;

        if (! $image) {
            return;
        }

        try {
            DeleteProductImageAction::run($image);
            $this->product->refresh();
        } catch (\Throwable) {
            $this->alert('error', 'No se pudo eliminar la imagen.', [
                'position' => 'top-end',
                'timer'    => 4000,
                'toast'    => true,
            ]);

            return;
        }

        $this->alert('success', 'Imagen eliminada correctamente.', [
            'position' => 'top-end',
            'timer'    => 3000,
            'toast'    => true,
        ]);
    }

    public function moveImage(int $image_id, string $direction): void
    {
        abort_unless($this->product->isEditableBy(), 403);

        $images = $this->product->images()->orderBy('sort_order')->orderBy('id')->get();
        $index  = $images->search(fn (ProductImage $image) => $image->id === $image_id);

        if ($index === false) {
            return;
        }

        $swap_index = $direction === 'left' ? $index - 1 : $index + 1;

        if (! isset($images[$swap_index])) {
            return;
        }

        $current = $images[$index];
        $swap    = $images[$swap_index];

        [$current_order, $swap_order] = [$current->sort_order, $swap->sort_order];

        $current->update(['sort_order' => $swap_order]);
        $swap->update(['sort_order' => $current_order]);

        $this->product->refresh();
    }

    public function render()
    {
        return view('livewire.admin.catalog.products.image-gallery', [
            'images' => $this->product->images,
        ]);
    }
}
