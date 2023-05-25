<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\CountryRequest;
use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::VIEW_COUNTRIES]), ['only' => ['index']]);
        $this->middleware('check.permissions:' . implode('-', [\App\Enums\BUserPermissions::ADD_AND_EDIT_COUNTRIES]), ['except' => ['index']]);
    }

    /**
     *Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, CountryService $countryService)
    {
        $countries = $countryService->getFilteredCountries($request->all());

        return view('backoffice.settings.countries.index', compact('countries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('backoffice.settings.countries.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CountryRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CountryRequest $request)
    {
        $country = new Country();

        if ($request->hasFile('countryFlag')) {
            $flagFile = $request->file('countryFlag');
            $fileName  = $request->code . '.png';
            $img = Image::make($flagFile->path());
            $img->resize(16, 16, function ($constraint) {
                $constraint->aspectRatio();
            })->save(public_path(Country::FLAG_PATH).'/'.$fileName);
        }

        $country->fill([
            'code' => $request->code,
            'name' => $request->name,
            'phone_code' => $request->phoneCode,
            'is_banned' => $request->isBanned,
            'is_alphanumeric_sender' => $request->isAlphanumericSender,
        ]);

        $country->save();

        session()->flash('success', t('country_successfully_created'));
        return redirect()->route('countries.index');
    }

    /**
     * Show the form for editing the specified resource
     *
     * @param Country $country
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Country $country)
    {
        return view('backoffice.settings.countries.edit', compact('country'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CountryRequest $request
     * @param int $country
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(CountryRequest $request, int $country)
    {
        $country = Country::query()->findOrFail($country);
        $destinationPath = public_path(Country::FLAG_PATH);

        if ($request->hasFile('countryFlag')) {
            $flagFile = $request->file('countryFlag');
            $fileName  = $request->code . '.png';

            $img = Image::make($flagFile->path());

            if (file_exists($destinationPath . '/' . $fileName)) {
                unlink($destinationPath . '/' . $fileName);
            }

            $img->resize(16, 16, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath.'/'.$fileName);
        }

        $country->update([
            'code' => $request->code,
            'name' => $request->name,
            'phone_code' => $request->phoneCode,
            'is_banned' => $request->isBanned,
            'is_alphanumeric_sender' => $request->isAlphanumericSender,
        ]);

        $country->refresh();

        session()->flash('success', t('country_successfully_updated'));
        return redirect()->route('countries.index');
    }
}
