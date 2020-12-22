<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\BaseController;
use App\Http\Resources\ShopResource;
use App\Http\Transformers\ShopTransformer;
use App\Models\Shop;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShopController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return ShopResource::collection(
            Shop::orderBy('name', 'asc')->simplePaginate()
        )
            ->additional([
                'meta' => [
                    'success' => true,
                    'message' => 'shops loaded'
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
            'name' => 'required|string|unique:shops|max:100',
            'domain' => 'sometimes|required|unique:shops|max:255',
            'active' => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();
        try {
            $shop = ShopTransformer::toInstance($request->all());
            $shop->save();
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollBack();
            return $this->sendError($ex->getMessage(), 409);
        }

        return $this->sendResponse(new ShopResource($shop), 'Shop created', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  Shop  $shop
     * @return JsonResponse
     */
    public function show(Shop $shop): JsonResponse
    {
        return $this->sendResponse(new ShopResource($shop), 'shop loaded');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Shop  $shop
     * @return Response
     */
    public function update(Request $request, Shop $shop)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|unique:shops|max:100',
            'domain' => 'sometimes|required|unique:shops|max:255',
            'active' => 'sometimes|required',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->toArray(), 422);
        }

        DB::beginTransaction();
        try {
            $updated_shop = ShopTransformer::toInstance($request->all(), $shop);
            $updated_shop->save();
            DB::commit();
        } catch (Exception $ex) {
            Log::info($ex->getMessage());
            DB::rollBack();
            return $this->sendError($ex->getMessage(), 409);
        }

        return $this->sendResponse(new ShopResource($updated_shop), 'Shop updated', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Shop  $shop
     * @return Response
     */
    public function destroy(Shop $shop)
    {
        //
    }
}
