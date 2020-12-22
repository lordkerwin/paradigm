<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShopResource;
use App\Http\Transformers\ShopTransformer;
use App\Models\Shop;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShopController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
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
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function show(Shop $shop)
    {
        //
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
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
     * @param  \App\Models\Shop  $shop
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shop $shop)
    {
        //
    }
}
