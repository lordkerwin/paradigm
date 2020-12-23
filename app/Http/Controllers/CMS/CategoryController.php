<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\BaseController;
use App\Http\Resources\CategoryResource;
use App\Http\Transformers\CategoryTransformer;
use App\Models\Category;
use App\Models\Shop;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection(
            Category::orderBy('name', 'asc')->simplePaginate()
        )
            ->additional([
                'meta' => [
                    'success' => true,
                    'message' => 'categories loaded'
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
            'name' => 'required|string|unique:categories|max:100',
            'slug' => 'required|string|unique:categories|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();
        try {
            $category = CategoryTransformer::toInstance($request->all());
            $category->save();
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollBack();
            return $this->sendError($ex->getMessage(), 409);
        }

        return $this->sendResponse(new CategoryResource($category), 'Category created', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Category  $category
     * @return JsonResponse
     */
    public function show(Category $category): JsonResponse
    {
        return $this->sendResponse($category->load('shops'), 'category loaded');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Category  $category
     * @return JsonResponse
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|unique:categories|max:100',
            'slug' => 'sometimes|required|string|unique:categories|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();
        try {
            $updated_category = CategoryTransformer::toInstance($request->all(), $category);
            $updated_category->save();
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollBack();
            return $this->sendError($ex->getMessage(), 409);
        }

        return $this->sendResponse(new CategoryResource($updated_category), 'Category updated', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Category  $category
     * @return Response
     */
    public function destroy(Category $category)
    {
        //
    }

    /**
     * Attach the specified resource to a store.
     *
     * @param  Category  $category
     * @param  Request  $request
     * @return JsonResponse
     */
    public function attachToShop(Category $category, Request $request)
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
            $shop->categories()->attach($category->id);
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollBack();
            return $this->sendError($ex->getMessage(), 409);
        }

        return $this->sendResponse(new CategoryResource($category), 'Category has been attach to shop #' . $shop->id,
            200);
    }
}
