<?php

namespace App\Repositories;

use App\Models\BaseModel;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Container\Container as Application;
use DB;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

abstract class BaseRepository
{
    protected $app;
    protected $model;
    protected $filter;
    protected $dataArray = [];
    protected $builder;

    public function __construct()
    {
        $this->app = new Application;
        $this->initializeModel();
        $this->initializeFilter();
        $this->boot();
    }

    public function boot()
    {
        $boot_methods = array_filter(get_class_methods($this), function ($method) {
            return str_starts_with($method, 'boot') && $method != 'boot';
        });
        foreach ($boot_methods as $method) {
            $this->{$method}();
        }
    }

    abstract public function model();

    public function findOrFail($id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function list(): Collection
    {
        return $this->orderBy('id', 'desc')->get();
    }

    public function paginate()
    {
        $records = $this->applyFilter();
        $perPage = request()->has('per_page') ? intval(request()->input('per_page')) : 10;

        $records = $this->applySort($records);
        if (method_exists($this->model(), 'relationship')) {
            return $records->with(app($this->model())->relationship())->paginate($perPage);
        } else {
            return $records->paginate($perPage);
        }
    }

    public function applySort($records)
    {

        if (request()->has('sortBy')) {
            $sortBy = Str::lower(request()->input('sortBy'));
            $orderBy = $this->getOrderBy(request()->input('orderBy'));
            $sortableValue = ['name', 'description'];
            # Has translations table
            if (method_exists($this->model(), 'translations') && $sortBy == in_array($sortBy, $sortableValue)) {
                $language = getCurrentLocale();
                $current_model_table_name = app($this->model())->getTable();
                $translation_table_name = Str::singular($current_model_table_name) . '_translations';
                $translation_sortBy = 'translation_' . $sortBy;
                $translation_select_as = $translation_table_name . '.' . $sortBy . ' as ' . $translation_sortBy;

                return $records->join($translation_table_name, function (JoinClause $join) use ($current_model_table_name, $translation_table_name, $language) {
                    $join->on($current_model_table_name . '.id', '=', $translation_table_name . '.' . Str::singular($current_model_table_name) . '_id')
                        ->where($translation_table_name . '.language_id', $language->id);
                })->select($current_model_table_name . '.*', $translation_select_as)->orderBy($translation_sortBy, $orderBy);
            } else {
                return $records->orderBy($sortBy, $orderBy);
            }
        }

        return $records;
    }

    function getOrderBy($value)
    {
        $availableValue = ['asc', 'desc'];

        if ($value == '' || !in_array(Str::lower($value), $availableValue)) {
            $value = 'asc';
        }

        return $value;
    }

    public function store(Request|array $attributes): Model
    {
        if ($attributes instanceof Request) {
            $attributes = $this->getDataArrayFromRequest($attributes);
        }

        DB::beginTransaction();

        try {
            $model = $this->model->newInstance($attributes);
            $model->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $model->refresh();
    }

    public function update(Request|array $attributes, int $id): Model
    {
        if ($attributes instanceof Request) {
            $attributes = $this->getDataArrayFromRequest($attributes);
        }

        DB::beginTransaction();

        try {
            $model = $this->model->findOrFail($id);
            $model->fill($attributes);
            $model->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $model->refresh();
    }

    public function destroy(int $id): bool
    {
        $model = $this->model->findOrFail($id);
        return $model->delete();
    }

    public function toggle(int $id): Model
    {
        DB::beginTransaction();

        try {
            $model = $this->model->findOrFail($id);
            $model->is_active = !$model->is_active;
            $model->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $model;
    }

    public function getDataArrayFromRequest(Request $request)
    {
        return $request->only($this->dataArray);
    }

    public function initializeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance " . Model::class);
        }

        return $this->model = $model;
    }

    /**
     * Trigger static method calls to the model
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        return call_user_func_array([new static(), $method], $arguments);
    }

    /**
     * Trigger method calls to the model
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $this->applyFilter();

        $response = call_user_func_array([$this->model, $method], $arguments);

        $this->initializeModel();
        return $response;
    }
}
