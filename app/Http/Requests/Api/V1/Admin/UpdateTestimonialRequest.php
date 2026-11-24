<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use App\Enums\TestimonialCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $testimonial = $this->route('testimonial');
        $testimonialId = is_object($testimonial) ? $testimonial->id : $testimonial;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('testimonials', 'slug')->ignore($testimonialId)],
            'avatar' => ['nullable', 'image', 'max:5120'],
            'category' => ['required', Rule::enum(TestimonialCategory::class)],
            'program' => ['required', 'string', 'max:255'],
            'origin' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'achievement' => ['nullable', 'string', 'max:255'],
            'quote' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'highlights' => ['nullable', 'array'],
            'highlights.*' => ['string', 'max:120'],
            'is_featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array{name: string, slug: string, category: TestimonialCategory, program: string, origin: string|null, destination: string|null, achievement: string|null, quote: string, rating: int, highlights: list<string>|null, is_featured: bool, sort_order: int, is_active: bool}
     */
    public function payload(): array
    {
        /** @var array{name: string, slug: string, category: string, program: string, origin: string|null, destination: string|null, achievement: string|null, quote: string, rating: int, highlights: list<string>|null} $data */
        $data = $this->safe()->except(['avatar', 'is_featured', 'sort_order', 'is_active']);

        return [
            ...$data,
            'category' => TestimonialCategory::from($data['category']),
            'is_featured' => $this->boolean('is_featured'),
            'sort_order' => (int) $this->input('sort_order', 0),
            'is_active' => $this->boolean('is_active', true),
        ];
    }
}
