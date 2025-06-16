<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Traits\GeneralTrait;
use App\Models\Product;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class ProductController extends Controller
{
    use UploadTrait, GeneralTrait;

    public function index()
    {
        $product = ProductResource::collection(Product::all());
        return $this->apiResponse($product);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'brand_id' => "required|exists:brands,id",
                'sub_category_id' => "required|exists:sub_categories,id",
                'name' => "required|string|max:255",
                'description' => "nullable|string",
                'discount_price' => "nullable|numeric|min:0",
                'price' => "required|numeric|min:0",
                'stock' => "required|integer|min:0",
                'images' => 'nullable|array',
                'images.*' => 'file|image|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->apiResponse(null, false, $validator->errors(), 400);
            }

            $ProductData = [
                'brand_id' => $request->brand_id,
                'sub_category_id' => $request->sub_category_id,
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'price' => $request->price,
                'stock' => $request->stock,
                'discount_price' => $request->discount_price,
            ];

            $product = Product::create($ProductData);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $this->verifyAndStoreImageForeach($image, 'Product', 'public', $product->id, Product::class);
                }
            }

            DB::commit();
            return $this->apiResponse(new ProductResource($product));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    public function show(string $id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'brand_id' => "sometimes|exists:brands,id",
                'sub_category_id' => "sometimes|exists:sub_categories,id",
                'name' => "sometimes|required|string|max:255",
                'description' => "sometimes|nullable|string",
                'price' => "sometimes|required|numeric|min:0",
                'discount_price' => "sometimes|required|numeric|min:0",
                'stock' => "sometimes|required|integer|min:0",
                'images' => 'sometimes|array',
                'images.*' => 'file|image|max:2048',
            ]);

            if ($validator->fails()) {
                return $this->apiResponse(null, false, $validator->errors(), 400);
            }

            $product = Product::find($id);
            if (!$product) {
                return $this->apiResponse(null, false, 'This product does not exist', 404);
            }

            $updateData = $request->only([
                'brand_id',
                'sub_category_id',
                'name',
                'description',
                'price',
                'discount_price',
                'stock'
            ]);

            if (isset($updateData['name'])) {
                $updateData['slug'] = Str::slug($updateData['name']);
            }
            $product->update($updateData);

            if ($request->hasFile('images')) {
                foreach ($product->images as $oldImage) {
                    \Storage::disk('public')->delete('Product/' . $oldImage->filename);
                    $oldImage->delete();
                }

                foreach ($request->images as $photo) {
                    $this->verifyAndStoreImageForeach($photo, 'Product', 'public', $id, Product::class);
                }
            }
            DB::commit();
            $updatedProduct = Product::with('images')->find($id);
            return $this->apiResponse(new ProductResource($updatedProduct));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);

            foreach ($product->images as $image) {
                Storage::disk('public')->delete('Product/' . $image->filename);
                $image->delete();
            }
            $product->delete();
            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
