<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Page::with('author');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->notTrashed();
        }

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        return PageResource::collection($query->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $page = Page::with('author:id,name')->findOrFail($id);

            return $page->toResource();
        } catch (\Throwable $th) {
            return response()->json([
                'message' => "Page with id {$id} not found.",
                'error' => [
                    'message' => $th->getMessage(),
                    'code' => $th->getCode(),
                ]
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
