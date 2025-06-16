<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Resources\SubCategoryResource;
use App\Models\SubCategory;
use App\Traits\GeneralTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SubCategoryController extends Controller
{
    use GeneralTrait;
    public function index()
    {
        $subCategories = SubCategory::with('category')->withCount('products')->get();
        return $this->apiResponse(SubCategoryResource::collection($subCategories));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => "required|exists:categories,id",
        ]);
        $slug = Str::slug($request->name);

        // Step 3: Check for duplicate slug
        if (SubCategory::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'name' => ['This name creates a duplicate slug. Please choose a different name.']
            ]);
        }

        $sub_category = SubCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'category_id' => $request->category_id,
        ]);

        return $this->apiResponse(new SubCategoryResource($sub_category));
    }

    public function show(string $id)
    {
        try {
            $subCategory = SubCategory::with('products')->findOrFail($id);
            return $this->apiResponse(new SubCategoryResource($subCategory));
        } catch (ModelNotFoundException $e) {
            return $this->apiResponse(null, false, 'SubCategory not found', 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => "sometimes|exists:categories,id",
                'name' => "sometimes|required|string|max:255",
            ]);

            if ($validator->fails()) {
                return $this->apiResponse(null, false, $validator->errors(), 400);
            }

            //$sub_category = SubCategory::find($id);
            $sub_category = SubCategory::with('category')->find($id);

            if (!$sub_category) {
                return $this->apiResponse(null, false, 'This sub category does not exist', 404);
            }

            $updateData = $request->only([
                'category_id',
                'name',
            ]);

            if (isset($updateData['name'])) {
                $slug = Str::slug($updateData['name']);

                $exists = SubCategory::where('slug', $slug)
                    ->where('id', '!=', $sub_category->id)
                    ->exists();

                if ($exists) {
                    return $this->apiResponse(null, false, [
                        'name' => ['This name creates a duplicate slug. Please choose a different name.']
                    ], 422);
                }

                $updateData['slug'] = $slug;
            }
            $sub_category->update($updateData);
            $sub_category->refresh();

            DB::commit();
            return $this->apiResponse(new SubCategoryResource($sub_category));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $sub_category = SubCategory::findOrFail($id);
            $sub_category->delete();
            return response()->json(['message' => 'sub category deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
