<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\BaseController;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Transformers\CategoryTransformer;
use App\Http\Transformers\ProductTransformer;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection(
            Product::orderBy('name', 'asc')->simplePaginate()
        )
            ->additional([
                'meta' => [
                    'success' => true,
                    'message' => 'products loaded'
                ]
            ]);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:products|max:100',
            'slug' => 'required|string|unique:products|max:100',
            'quantity' => 'required|numeric',
            'reserve_quantity' => 'required|numeric',
            'price' => 'required|numeric',
            'weight_grams' => 'required|numeric',
            'image' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();
        try {
            $product = ProductTransformer::toInstance($request->all());
            $product->save();
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollBack();
            return $this->sendError($ex->getMessage(), 409);
        }

        return $this->sendResponse(new ProductResource($product), 'Product created', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Product  $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        return $this->sendResponse($product->load('categories', 'shops'), 'product loaded');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Product  $product
     * @return JsonResponse
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|unique:products|max:100',
            'slug' => 'sometimes|required|string|unique:products|max:100',
            'quantity' => 'sometimes|required|numeric',
            'reserve_quantity' => 'sometimes|required|numeric',
            'price' => 'sometimes|required|numeric',
            'weight_grams' => 'sometimes|required|numeric',
            'image' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();
        try {
            $updated_product = ProductTransformer::toInstance($request->all(), $product);
            $updated_product->save();
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollBack();
            return $this->sendError($ex->getMessage(), 409);
        }

        return $this->sendResponse(new ProductResource($updated_product), 'Product updated', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Product  $product
     * @return Response
     */
    public function destroy(Product $product)
    {
        //
    }


    public function attachToShop(Product $product, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shop_id' => 'required|exists:shops,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();
        try {
            $shop = Shop::findOrFail($request->input('shop_id'));
            $product->shops()->attach($shop->id);
            $product->save();
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollBack();
            return $this->sendError($ex->getMessage(), 409);
        }

        return $this->sendResponse(new ProductResource($product), 'product has been attach to shop #' . $shop->id,
            200);
    }

    public function attachToCategory(Product $product, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();
        try {
            $category = Category::findOrFail($request->input('category_id'));
            $product->categories()->attach($category->id);
            $product->save();
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollBack();
            return $this->sendError($ex->getMessage(), 409);
        }

        return $this->sendResponse(new ProductResource($product), 'product has been attach to category #' . $category->id,
            200);
    }
}
