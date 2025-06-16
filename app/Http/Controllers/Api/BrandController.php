<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Traits\GeneralTrait;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    use GeneralTrait, UploadTrait;

    public function index()
    {
        try {
            $brands = BrandResource::collection(Brand::all());
            return $this->apiResponse($brands);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|unique:brands,name|max:255',
                'logo' => 'required|image|max:2048'
            ]);

            $brand = Brand::create([
                'name' => $validated['name'],
            ]);

            if ($request->hasFile('logo')) {
                $this->verifyAndStoreImage($request, 'logo', 'Brands', 'public', $brand->id, Brand::class);
            }

            return $this->apiResponse(new BrandResource($brand));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            return $this->apiResponse(new BrandResource($brand));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, false, 'Brand not found', 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|unique:brands,name,' . $id . '|max:255',
                'logo' => 'sometimes|file|'
            ]);

            $brand = Brand::findOrFail($id);
            if ($request->name) {
                $brand->update([
                    'name' => $validated['name'],
                ]);
            }

            // Update photo if provided
            if ($request->hasFile('logo')) {
                if ($brand->image) {
                    $old_img = $brand->image->filename;
                    $this->Delete_attachment('public', 'Brands/' . $old_img, $id);
                }
                // Upload new img
                $this->verifyAndStoreImage($request, 'logo', 'Brands', 'public', $id, Brand::class);
            }
            $updatedBrand = Brand::with('image')->find($id);
            return $this->apiResponse(new BrandResource($updatedBrand));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, false, 'Brand not found', 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            $this->Delete_attachment('public', 'Brands/' . $brand->image->filename, $id);
            $brand->delete();
            return $this->apiResponse(null, true, 'Brand deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->apiResponse(null, false, 'Brand not found', 404);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
