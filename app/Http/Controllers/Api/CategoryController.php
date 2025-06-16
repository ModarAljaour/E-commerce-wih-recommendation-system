<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class CategoryController extends Controller
{
    use GeneralTrait;
    public function index()
    {
        $category = CategoryResource::collection(Category::all());
        return $this->apiResponse($category);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $slug = Str::slug($request->name);

        // Check for duplicate slug :
        if (Category::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'name' => ['This name creates a duplicate slug. Please choose a different name.']
            ]);
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);
        return $this->apiResponse(new CategoryResource($category));
    }

    public function show(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return $this->apiResponse(new CategoryResource($category));
    }

    public function destroy(string $id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return response()->json(['message' => 'Category deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
