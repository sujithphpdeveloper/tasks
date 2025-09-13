<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = Tag::all();
        return response()->json($tags);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request)
    {
        $validatedTag = $request->validated();
        $tag = Tag::create($validatedTag);

        return response()->json(['message' => 'Tag created successfully', 'tag' => $tag], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $validatedTag = $request->validated();
        $tag->update($validatedTag);

        return response()->json(['message' => 'Tag updated successfully', 'tag' => $tag]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        // Remove the deleted tag from the tasks
        $tag->tasks()->detach();

        // Delete the tag
        $tag->delete();
        return response()->json(['message' => 'Tag deleted successfully']);
    }
}
