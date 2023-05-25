<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\LogMessage;
use App\Enums\LogResult;
use App\Enums\LogType;
use App\Facades\ActivityLogFacade;
use App\Http\Controllers\Controller;
use App\Models\RatesCategory;
use App\Models\RatesValues;
use App\Services\ActivityLogService;
use App\Services\RatesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RatesController extends Controller
{
    /** @var RatesService */
    protected $ratesService;

    public function __construct()
    {
        $this->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::ADD_AND_EDIT_CLIENT_RATES]), ['except' => ['index']]);
        $this->ratesService = new RatesService;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backoffice.rates-categories', [
            'categories' => RatesCategory::all()->sortByDesc('default_for_account_type'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $c = new RatesCategory;
        $values = $this->ratesService->getValueListFrom();
        $values['_action'] = route('rates.store', $c->id);
        $values['_method'] = 'POST';
        $values['_category'] = $c;
        return view('backoffice.rates', $values);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $r = $this->ratesService;

        $valuesIn = $r->getValueListFrom($request);
        unset($valuesIn['_types']); // @todo

        //!!! transaction

        try {
            $c = RatesCategory::create([
                'id' => Str::uuid(),
                'title' => $request->_title,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Can\'t create RatesCategory']);
        }

        $models = $r->mapNewValues($valuesIn);
        foreach ($models as $model) {
            $model->fill([
                'rates_category_id' => $c->id,
                'id' => Str::uuid(),
                ])->save();
        }


        ActivityLogFacade::saveLog(LogMessage::RATES_CREATE,['name' => $c->title],LogResult::RESULT_SUCCESS,LogType::TYPE_RATES_CREATE);

        return redirect()->route('rates.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // method uses as edit

        $r = $this->ratesService;
        $c = RatesCategory::find($id);
        $valuesDB = $r->getValueListFromDB($c->id);
        $values = $r->getValueListFrom($valuesDB);

        $values['_action'] = route('rates.update', $c->id);
        $values['_method'] = 'PATCH';
        $values['_title'] = $c->title;
        $values['_category'] = $c;
        return view('backoffice.rates', $values);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // show used
    }


    public function deactivate()
    {
        $id = request('RatesCategoryId');
        $c = RatesCategory::find($id);
        if ($c->default_for_account_type) {
            return false;
        }

        $c->update([
            'status' => RatesService::STATUS_INACTIVE
        ]);
        return true;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $r = $this->ratesService;

        $c = RatesCategory::find($id);

        $valuesIn = $r->getValueListFrom($request);
        $valuesPrev = (array)$r->getValueListFromDB($id);
        $diff = $r->getDiffOfValueList($valuesIn, $valuesPrev);
        unset($valuesIn['_types']); // @todo


        // @todo transaction

        $c->update([
            'title' => $request->_title,
            'updated_at' => now(),
        ]);

        $models = $r->mapNewValues($valuesIn);
        foreach ($models as $model) {
            $foundModel = RatesValues
                ::where('rates_category_id', $id)
                ->where('key', $model->key)
                ->where('level', $model->level)
                ->first();
            if (!$foundModel) {
                $foundModel = new RatesValues;
                $foundModel->fill([
                    'id' => Str::uuid(),
                    'rates_category_id' => $id,
                    'key' => $model->key,
                    'level' => $model->level,
                ]);
            }
            $foundModel->fill([
                'value' => $model->value,
            ])->save();
        }

        ActivityLogFacade::saveLog(LogMessage::RATES_UPDATE,
            ['name' => $c->title, 'values' => $diff],LogResult::RESULT_SUCCESS,LogType::TYPE_RATES_UPDATE);

        return redirect()->route('rates.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
