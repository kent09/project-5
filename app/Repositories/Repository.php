<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\ParameterBag;

Class Repository {

    public $repo_type;

    public $model;

    /**
     * @todo : update as needed
     *
     */

    /**
     * Store item
     *
     * @param ParameterBag $parameter
     * @return $mixed
     */
    public function store(ParameterBag $parameter)
    {
        try {
            $model = $this->model;

            $item = $model->create($parameter->all());

            Log::info(trans('messages.success.store', ['type' => $this->repo_type, 'id' => $item->id]));

            return $item;
        } catch(\Exception $e) {
            Log::error(trans('messages.errors.store', ['type' => $this->repo_type, 'description' => $e->getMessage()]));

            return $e;
        }
    }

    /**
     * Insert items in bulk
     *
     * @param $items
     * @return @mixed
     */
    public function bulkInsert($items)
    {
        try {
            $result = $this->model->insert($items);

            $result
                ? Log::info(trans('messages.success.bulk-store', ['type' => $this->repo_type]))
                : Log::info(trans('messages.errors.bulk-store', ['type' => $this->repo_type]));

            return $result;
        } catch (\Exception $e) {
            Log::info(trans('messages.errors.bulk-store', ['type' => $this->repo_type, 'description' => $e->getMessage()]));

            return $e;
        }
    }

    /**
     * Update an item
     *
     * @param $id
     * @param $parameters
     * @return @return @mixed
     */
    public function update($id, $parameters) {
        try {
            $item = $this->find($id);

            $result = $item->update($parameters);

            $result
                ? Log::info(trans('messages.success.update', ['type' => $this->repo_type, 'id' => $item->id]))
                : Log::info(trans('messages.errors.update', ['type' => $this->repo_type, 'id' => $item->id]));

            return $result;
        } catch (\Exception $e) {
            Log::info(trans('messages.errors.update', ['type' => $this->repo_type, 'id' => $id, 'description' => $e->getMessage()]));

            return $e;
        }
    }

    /**
     * Update an item
     *
     * @param $id
     * @param $parameters
     * @return @return @mixed
     */
    public function updateOrCreate($filter, $parameters) {
        try {
            
            // updateOrCreate is not avaiable in laravel 5.2
            // $result = $this->updateOrCreate($filter, $parameters)
            $result = null;
            $item = $this->model
                 ->where($filter)
                 ->first();

            if ($item) {
                foreach($parameters as $key => $value) {
                    $result = $item->update($parameters);
                }
                $result = $item;
            } else {
                $result = $this->model->create($parameters);
            }

            $result
                ? Log::info(trans('messages.success.update', ['type' => $this->repo_type]))
                : Log::info(trans('messages.errors.update', ['type' => $this->repo_type]));

            return $result;

        } catch (\Exception $e) {
            Log::info(trans('messages.errors.update', ['type' => $this->repo_type]));

            return $e;
        }
    }

    /**
     * Delete an item
     *
     * @param $id
     * @return @return @mixed
     */
    public function destroy($id) {
        try {
            $item = $this->find($id);

            $result = $item->destroy();

            $result
                ? Log::info(trans('messages.success.destroy', ['type' => $this->repo_type, 'id' => $item->id]))
                : Log::info(trans('messages.errors.destroy', ['type' => $this->repo_type, 'id' => $item->id]));

            return $result;
        } catch (\Exception $e) {
            Log::info(trans('messages.errors.destroy', ['type' => $this->repo_type, 'id' => $id, 'description' => $e->getMessage()]));

            return $e;
        }
    }

    /**
     * Return all items
     *
     * @return mixed
     */
    public function all() {
        return $this->model->all();
    }

    /**
     * Find item
     *
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * When updating, get the result instead of boolean
     *
     * @param $id
     * @param $item
     * @return mixed
     */
    public function result($id, $result) {
        if ($result instanceof $this->model) {
            $item = $this->find($id);

            return $item;
        } else {
            return $result;
        }
    }

}
