<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Testimonial;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

final class TestimonialAdminService
{
    /**
     * @return LengthAwarePaginator<int, Testimonial>
     */
    public function list(int $page, int $perPage): LengthAwarePaginator
    {
        return Testimonial::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage, page: $page);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, ?UploadedFile $avatar): Testimonial
    {
        if ($avatar !== null) {
            $data['avatar_path'] = $avatar->store('testimonials', 'public');
        }

        return Testimonial::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Testimonial $testimonial, array $data, ?UploadedFile $avatar): Testimonial
    {
        if ($avatar !== null) {
            if ($testimonial->avatar_path !== null) {
                Storage::disk('public')->delete($testimonial->avatar_path);
            }
            $data['avatar_path'] = $avatar->store('testimonials', 'public');
        }

        $testimonial->update($data);

        return $testimonial;
    }

    public function delete(Testimonial $testimonial): void
    {
        if ($testimonial->avatar_path !== null) {
            Storage::disk('public')->delete($testimonial->avatar_path);
        }

        $testimonial->delete();
    }
}
