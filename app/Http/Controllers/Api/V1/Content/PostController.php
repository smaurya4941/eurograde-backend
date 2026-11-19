<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Content;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Content\IndexPostRequest;
use App\Http\Resources\V1\PostDetailResource;
use App\Http\Resources\V1\PostResource;
use App\Http\Responses\ApiResponse;
use App\Services\Content\ContentService;
use Illuminate\Http\JsonResponse;

final class PostController extends Controller
{
    public function __construct(private readonly ContentService $content) {}

    /**
     * Published posts (card data, newest first), paginated.
     * Filters: ?category={slug}&tag={slug}&search={term}&page=&per_page=
     */
    public function index(IndexPostRequest $request): JsonResponse
    {
        $posts = $this->content->posts($request->toQuery())->withQueryString();

        return ApiResponse::success(PostResource::collection($posts));
    }

    /** A published article with its body, tags and author (404 otherwise). */
    public function show(string $slug): JsonResponse
    {
        return ApiResponse::success(new PostDetailResource($this->content->post($slug)));
    }
}
